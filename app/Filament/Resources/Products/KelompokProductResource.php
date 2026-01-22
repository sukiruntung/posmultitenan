<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\KelompokProductResource\Pages;
use App\Filament\Resources\Products\KelompokProductResource\RelationManagers;
use App\Models\Products\KelompokProduct;
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
use Illuminate\Support\Facades\Log;

class KelompokProductResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 1;
    protected static ?string $model = KelompokProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;
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
                Forms\Components\TextInput::make('kelompok_productname')
                    ->label('Nama Kelompok')
                    ->required()
                    ->maxLength(80),
                Forms\Components\Hidden::make('outlet_id')
                    ->default(Auth::user()->userOutlet->outlet_id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->columns([
                Tables\Columns\TextColumn::make('kelompok_productname')
                    ->label('Nama Kelompok')
                    ->searchable(),
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
                    ->action(function (KelompokProduct $record) {
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
            'index' => Pages\ListKelompokProducts::route('/'),
        ];
    }
}
