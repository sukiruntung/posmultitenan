<?php

namespace App\Filament\Resources\Accesses;

use App\Filament\Resources\Accesses\DashboardAccessResource\Pages;
use App\Filament\Resources\Accesses\DashboardAccessResource\RelationManagers;
use App\Traits\CheckPermissionAccess;
use App\Models\Accesses\DashboardAccess;
use App\Models\Accesses\SystemDashboard;
use App\Models\Accesses\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DashboardAccessResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 19;
    protected static ?string $model = DashboardAccess::class;

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
                            ->relationship('userGroup', 'user_groupname')
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('system_dashboard_id', null)) // reset kalau group berubah
                            ->required()
                            ->label('Group User'),

                        Forms\Components\Select::make('system_dashboard_id')
                            ->options(function (Forms\Get $get) {
                                $userGroupId = $get('user_group_id');
                                $currentValue = $get('system_dashboard_id'); // untuk mode edit

                                if (!$userGroupId) {
                                    // Belum pilih group → tampilkan semua
                                    return SystemDashboard::pluck('system_dashboardname', 'id');
                                }

                                // Ambil yang sudah dipakai group
                                $usedIds =  DashboardAccess::where('user_group_id', $userGroupId)
                                    ->where('outlet_id', Auth::user()->userOutlet->outlet_id)
                                    ->pluck('system_dashboard_id');

                                $query = SystemDashboard::query()
                                    ->whereNotIn('id', $usedIds);

                                // Kalau sedang edit, pastikan data lama tetap muncul
                                if ($currentValue) {
                                    $query->orWhere('id', $currentValue);
                                }

                                return $query->pluck('system_dashboardname', 'id');
                            })
                            ->reactive()
                            ->searchable()
                            ->disabled(fn(Forms\Get $get): bool => !$get('user_group_id')) // nonaktif kalau group belum dipilih
                            ->required()
                            ->label('Nama Dashboard'),
                        Forms\Components\Toggle::make('can_view')
                            ->label('is View')
                            ->default(false),
                        Forms\Components\Hidden::make('outlet_id')
                            ->default(Auth::user()->userOutlet->outlet_id),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->columns([
                Tables\Columns\TextColumn::make('systemDashboard.system_dashboardname')
                    ->label('Nama Dashboard')
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
                    ->after(function (DashboardAccess $record) {
                        $users = User::where('user_group_id', $record->user_group_id)->get();
                        foreach ($users as $user) {
                            $user->clearDashboardAccessCache();
                            $user->cacheDashboardAccess();
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (DashboardAccess $record) {
                        $record->user_id = Auth::id();
                        $record->save();
                        $record->delete();
                    })
                    ->after(function (DashboardAccess $record) {
                        $users = User::where('user_group_id', $record->user_group_id)->get();
                        foreach ($users as $user) {
                            $user->clearDashboardAccessCache();
                            $user->cacheDashboardAccess();
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
            'index' => Pages\ListDashboardAccesses::route('/'),
        ];
    }
}
