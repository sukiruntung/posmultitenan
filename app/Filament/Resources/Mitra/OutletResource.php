<?php

namespace App\Filament\Resources\Mitra;

use App\Filament\Resources\Mitra\OutletResource\Pages;
use App\Models\Accesses\Outlet;
use App\Models\Accesses\User;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OutletResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 20;
    protected static ?string $model = Outlet::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
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
                Forms\Components\TextInput::make('outlet_name')
                    ->label('Nama Outlet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('outlet_address')
                    ->label('Alamat Outlet')
                    ->autosize(),
                Forms\Components\FileUpload::make('outlet_logo')
                    ->label('Logo Outlet')
                    ->image()
                    ->directory('outlet-logos'),
                Forms\Components\TextInput::make('outlet_hp')
                    ->label('No. HP Outlet')
                    ->tel()
                    ->maxLength(15)
                    ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                    ->placeholder('cth: 08123456789'),
                Forms\Components\Select::make('owner_user_id')
                    ->label('Owner')
                    ->options(User::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(fn(Builder $query) => $query->where('owner_user_id', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('outlet_name')
                    ->label('Nama Outlet')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('outlet_address')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\ImageColumn::make('outlet_logo')
                    ->label('Logo')
                    ->circular(),
                Tables\Columns\TextColumn::make('outlet_hp')
                    ->label('No. HP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
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
                    ->action(function (Outlet $record) {
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
            'index' => Pages\ListOutlets::route('/'),
        ];
    }
}
