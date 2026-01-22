<?php

namespace App\Filament\Resources\Accesses;

use App\Filament\Resources\Accesses\MasterDataGroupResource\Pages;
use App\Filament\Resources\Accesses\MasterDataGroupResource\RelationManagers;
use App\Models\Accesses\MasterDataGroup;
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

class MasterDataGroupResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 7;
    protected static ?string $model = MasterDataGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'User';
    protected static ?int $navigationSort = 3;
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
                Forms\Components\TextInput::make('master_data_groupname')
                    ->required()
                    ->maxLength(50)
                    ->label('Group Name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('master_data_groupname')
                    ->label('Group Name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->closeModalByClickingAway(false)
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = Auth::id();
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (MasterDataGroup $record) {

                        $record->user_id = Auth::id();
                        $record->save();
                        $record->delete();
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
            'index' => Pages\ListMasterDataGroups::route('/'),
        ];
    }
}
