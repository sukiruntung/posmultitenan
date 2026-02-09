<?php

namespace App\Filament\Resources\Accesses;

use App\Filament\Resources\Accesses\UserResource\Pages;
use App\Filament\Resources\Accesses\UserResource\RelationManagers;
use App\Models\Accesses\DashboardAccess;
use App\Models\Accesses\MasterDataAccess;
use App\Models\Accesses\MenuAccess;
use App\Models\Accesses\User;
use App\Models\Accesses\UserOutlet;
use App\Scopes\ForAuthUserGroupScope;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 6;
    protected static ?string $model = user::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'User';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return static::checkMasterDataAccess('can_view', static::$menuId);
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        $userOutlet = $user->userOutlet ?? null;

        // Staff tidak bisa create
        if ($userOutlet && $userOutlet->role === 'staff') {
            return false;
        }

        return static::checkMasterDataAccess('can_create', static::$menuId);
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        $userOutlet = $user->userOutlet ?? null;

        // Staff tidak bisa edit
        if ($userOutlet && $userOutlet->role === 'staff') {
            return false;
        }

        return static::checkMasterDataAccess('can_edit', static::$menuId);
    }
    public static function canDelete(Model $record): bool
    {
        return static::checkMasterDataAccess('can_delete', static::$menuId);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi User')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(100)
                                    ->label('Nama User'),
                                Forms\Components\TextInput::make('email')
                                    ->required()
                                    ->email()
                                    ->maxLength(100)
                                    ->label('Email'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                                    ->dehydrated(fn($state) => filled($state))
                                    ->label('Password')
                                    ->required(fn(Get $get) => is_null($get('id'))),
                                Forms\Components\Select::make('user_group_id')
                                    ->relationship('userGroup', 'user_groupname')
                                    ->required()
                                    ->label('Group User'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('role')
                                    ->label('Role')
                                    ->options(function () {
                                        $user = Auth::user();
                                        $userOutlet = $user->userOutlet ?? null;

                                        if (!$userOutlet) {
                                            return [];
                                        }

                                        // Admin: bisa pilih semua role
                                        if ($userOutlet->role === 'admin') {
                                            return [
                                                'admin' => 'Admin',
                                                'owner' => 'Owner',
                                                'staff' => 'Staff',
                                            ];
                                        }

                                        // Owner: hanya bisa pilih owner dan staff
                                        if ($userOutlet->role === 'owner') {
                                            return [
                                                'owner' => 'Owner',
                                                'staff' => 'Staff',
                                            ];
                                        }

                                        return [];
                                    })
                                    ->required()
                                    ->live()
                                    ->native(false),
                            ]),
                        Forms\Components\Toggle::make('is_kasir')
                            ->label('is Kasir')
                            ->default(false)
                            ->inline(false),
                    ]),

                Forms\Components\Section::make('Informasi Outlet')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('outlet_name')
                                    ->label('Nama Outlet')
                                    ->required(fn(Get $get) => $get('role') === 'owner')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('outlet_address')
                                    ->label('Alamat Outlet')
                                    ->autosize()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->visible(fn(Get $get) => $get('role') === 'owner'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                $userOutlet = $user->userOutlet;

                if (!$userOutlet) {
                    return $query->whereRaw('1 = 0');
                }

                // Admin: tampilkan semua user
                if ($userOutlet->role === 'admin') {
                    return $query;
                }

                // Owner: tampilkan user di outlet miliknya
                if ($userOutlet->role === 'owner') {
                    return $query->whereHas('userOutlet', fn($q) => $q->where('outlet_id', $userOutlet->outlet_id));
                }

                // Staff: hanya tampilkan dirinya sendiri
                return $query->where('id', $user->id);
            })
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userGroup.user_groupname')
                    ->label('Group')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userOutlet.outlet.outlet_name')
                    ->label('Outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('userOutlet.role')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'success',
                        'owner' => 'warning',
                        'staff' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_kasir')
                    ->toggleable()
                    ->label('is Kasir')
                    ->boolean(),
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->closeModalByClickingAway(false)
                    ->fillForm(function ($record) {
                        $data = $record->toArray();
                        $data['role'] = $record->userOutlet->role ?? 'staff';

                        // Jika owner, load data outlet
                        if ($data['role'] === 'owner' && $record->userOutlet) {
                            $outlet = $record->userOutlet->outlet;
                            $data['outlet_name'] = $outlet->outlet_name ?? '';
                            $data['outlet_address'] = $outlet->outlet_address ?? '';
                        }

                        return $data;
                    })
                    ->using(function ($record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $role = $data['role'] ?? 'staff';
                            $outletName = $data['outlet_name'] ?? null;
                            $outletAddress = $data['outlet_address'] ?? null;

                            unset($data['role'], $data['outlet_name'], $data['outlet_address']);

                            $data['user_id'] = Auth::id();
                            $record->update($data);

                            if ($record->userOutlet) {
                                $record->userOutlet->update(['role' => $role]);

                                // Update outlet jika role owner
                                if ($role === 'owner' && $outletName) {
                                    $outlet = $record->userOutlet->outlet;
                                    if ($outlet) {
                                        $outlet->update([
                                            'outlet_name' => $outletName,
                                            'outlet_address' => $outletAddress,
                                            'user_id' => Auth::id(),
                                        ]);
                                    }
                                }
                            }
                        });
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (User $record) {
                        DB::transaction(function () use ($record) {
                            $record->loadMissing('userOutlet');

                            $outletId = $record->userOutlet->outlet_id ?? null;
                            $role = $record->userOutlet->role ?? null;

                            static::deleteUserWithDependencies($record, $outletId);

                            if ($role === 'owner' && $outletId) {
                                $staffUsers = User::with('userOutlet')
                                    ->whereHas('userOutlet', function ($query) use ($outletId) {
                                        $query->where('outlet_id', $outletId)
                                            ->where('role', 'staff');
                                    })
                                    ->get();

                                foreach ($staffUsers as $staffUser) {
                                    static::deleteUserWithDependencies($staffUser, $outletId);
                                }
                            }
                        });
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function deleteUserWithDependencies(User $user, ?int $outletId = null): void
    {
        $user->user_id = $user->id;
        $user->save();

        $user->loadMissing('userOutlet');

        $outletId ??= $user->userOutlet->outlet_id ?? null;
        $userGroupId = $user->user_group_id;

        UserOutlet::where('user_id', $user->id)->delete();
        if ($outletId && $userGroupId) {
            MenuAccess::where('outlet_id', $outletId)
                ->where('user_group_id', $userGroupId)
                ->delete();
            MasterDataAccess::withoutGlobalScope(ForAuthUserGroupScope::class)
                ->where('outlet_id', $outletId)
                ->where('user_group_id', $userGroupId)
                ->delete();
            DashboardAccess::where('outlet_id', $outletId)
                ->where('user_group_id', $userGroupId)
                ->delete();
        }

        $user->delete();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
