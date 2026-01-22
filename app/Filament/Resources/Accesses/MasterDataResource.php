<?php

namespace App\Filament\Resources\Accesses;

use App\Filament\Resources\Accesses\MasterDataResource\Pages;
use App\Filament\Resources\Accesses\MasterDataResource\RelationManagers;
use App\Models\Accesses\MasterData;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MasterDataResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 8;
    protected static ?string $model = MasterData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'User';
    protected static ?int $navigationSort = 4;

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
                Forms\Components\Select::make('master_data_group_id')
                    ->relationship('masterDataGroup', 'master_data_groupname')
                    ->required()
                    ->label('Kelompok'),
                Forms\Components\TextInput::make('master_dataname')
                    ->required()
                    ->maxLength(100)
                    ->label('Nama Master Data'),
                Forms\Components\TextInput::make('master_datalink')
                    ->maxLength(100)
                    ->label('Url'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('masterDataGroup.master_data_groupname')
                    ->label('Kelompok')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('master_dataname')
                    ->label('Nama Master Data')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('master_datalink')
                    ->label('Url'),

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
                    ->action(function (MasterData $record) {
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
            'index' => Pages\ListMasterData::route('/'),
        ];
    }
}
