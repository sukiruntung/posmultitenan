<?php

namespace App\Filament\Resources\Accesses;

use App\Filament\Resources\Accesses\MenuAccessResource\Pages;
use App\Filament\Resources\Accesses\MenuAccessResource\RelationManagers;
use App\Models\Accesses\Menu;
use App\Models\Accesses\MenuAccess;
use App\Models\Accesses\User;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MenuAccessResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 17;
    protected static ?string $model = MenuAccess::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
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
        return static::checkMasterDataAccess('can_create', static::$menuId);
    }

    public static function canEdit(Model $record): bool
    {
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
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('user_group_id')
                            ->options(function () {
                                return \App\Models\Accesses\UserGroup::whereHas('users.userOutlet', function ($query) {
                                    $query->where('outlet_id', Auth::user()->userOutlet->outlet_id);
                                })->pluck('user_groupname', 'id');
                            })
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('menu_id', null)) // reset kalau group berubah
                            ->required()
                            ->label('Group User'),

                        Forms\Components\Select::make('menu_id')
                            ->options(function (Forms\Get $get) {
                                $userGroupId = $get('user_group_id');
                                $currentValue = $get('menu_id'); // untuk mode edit

                                if (!$userGroupId) {
                                    // Belum pilih group → tampilkan semua
                                    return Menu::pluck('menu_name', 'id');
                                }

                                // Ambil yang sudah dipakai group
                                $usedIds =  MenuAccess::where('user_group_id', $userGroupId)
                                    ->where('outlet_id', Auth::user()->userOutlet->outlet_id)
                                    ->pluck('menu_id');

                                $query = Menu::query()
                                    ->whereNotIn('id', $usedIds);

                                // Kalau sedang edit, pastikan data lama tetap muncul
                                if ($currentValue) {
                                    $query->orWhere('id', $currentValue);
                                }

                                return $query->pluck('menu_name', 'id');
                            })
                            ->reactive()
                            ->searchable()
                            ->disabled(fn(Forms\Get $get): bool => !$get('user_group_id')) // nonaktif kalau group belum dipilih
                            ->required()
                            ->label('Nama Menu'),

                    ]),
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Toggle::make('can_view')
                            ->label('is view')
                            ->helperText('Enable or disable view access')
                            ->default(true),
                        Forms\Components\Toggle::make('can_create')
                            ->label('is create')
                            ->helperText('Enable or disable create access')
                            ->default(false),
                        Forms\Components\Toggle::make('can_edit')
                            ->label('Can Edit')
                            ->helperText('Enable or disable edit access')
                            ->default(false),
                        Forms\Components\Toggle::make('can_delete')
                            ->label('Can Delete')
                            ->helperText('Enable or disable delete access ')
                            ->default(false),
                        Forms\Components\Toggle::make('can_ppn')
                            ->label('Can PPN')
                            ->helperText('Enable or disable ppn ')
                            ->default(false)
                            ->live(),
                        Forms\Components\TextInput::make('ppn_rate')
                            ->label('Nilai PPN')
                            ->visible(fn(Forms\Get $get) => $get('can_ppn') == true)
                            ->default(0),

                        Forms\Components\Toggle::make('can_ongkir')
                            ->label('Can Ongkir')
                            ->helperText('Enable or disable ongkir ')
                            ->default(false),
                        Forms\Components\Toggle::make('can_hargapembelian')
                            ->label('Can Input Price Modal')
                            ->helperText('Enable or disable input price Modal')
                            ->default(false),
                        Forms\Components\Toggle::make('can_validate')
                            ->label('Can Validate')
                            ->helperText('Enable or disable validate access ')
                            ->default(false),
                        Forms\Components\Toggle::make('can_unvalidate')
                            ->label('Can Unvalidate')
                            ->helperText('Enable or disable unvalidate access ')
                            ->default(false),
                        Forms\Components\Toggle::make('can_print1')
                            ->label('Can Print 1')
                            ->helperText('Enable or disable print 1 access ')
                            ->default(false),
                        Forms\Components\Toggle::make('can_print2')
                            ->label('Can Print 2')
                            ->helperText('Enable or disable print 2 access ')
                            ->default(false),
                    ])->columns(6),
                Forms\Components\Hidden::make('outlet_id')
                    ->default(Auth::user()->userOutlet->outlet_id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('menu.menu_name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userGroup.user_groupname')
                    ->label('Group User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('can_view')
                    ->toggleable()
                    ->label('is view')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_create')
                    ->toggleable()
                    ->label('is create')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_edit')
                    ->toggleable()
                    ->label('Can Edit')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_delete')
                    ->toggleable()
                    ->label('Can Delete')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_validate')
                    ->toggleable()
                    ->label('Can Validate')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_unvalidate')
                    ->toggleable()
                    ->label('Can Unvalidate')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_print1')
                    ->toggleable()
                    ->label('Can Print 1')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_print2')
                    ->toggleable()
                    ->label('Can Print 2')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->closeModalByClickingAway(false)
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = Auth::id();
                        $data['outlet_id'] = Auth::user()->userOutlet->outlet_id;
                        return $data;
                    })
                    ->after(function (MenuAccess $record) {
                        $users = User::where('user_group_id', $record->user_group_id)->get();
                        foreach ($users as $user) {
                            $user->clearMenuAccessCache();
                            $user->cacheMenuAccess();
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (MenuAccess $record) {
                        $users = User::where('user_group_id', $record->user_group_id)->get();
                        $record->user_id = Auth::id();
                        $record->save();
                        $record->delete();
                        foreach ($users as $user) {
                            $user->clearMenuAccessCache();
                            $user->cacheMenuAccess();
                        }
                    })
                    ->after(function (MenuAccess $record) {
                        $users = User::where('user_group_id', $record->user_group_id)->get();
                        foreach ($users as $user) {
                            $user->clearMasterDataAccessCache();
                            $user->cacheMasterDataAccess();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListMenuAccesses::route('/'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['outlet_id'] = Auth::user()->userOutlet->outlet_id;
        return $data;
    }
}
