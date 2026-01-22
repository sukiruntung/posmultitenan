<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\MerkResource\Pages;
use App\Filament\Resources\Products\MerkResource\RelationManagers;
use App\Models\Products\Merk;
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

class MerkResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 10;
    protected static ?string $model = Merk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
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
                Forms\Components\TextInput::make('merk_name')
                    ->label('Nama Merk')
                    ->required()
                    ->maxLength(255)
                    ->unique(Merk::class, 'merk_name', ignoreRecord: true)
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('outlet_id')
                    ->default(Auth::user()->userOutlet->outlet_id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('merk_name')
                    ->label('Merk')
                    ->sortable()
                    ->searchable(),
                //
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
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (Merk $record) {
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
            'index' => Pages\ListMerks::route('/'),
        ];
    }
}
