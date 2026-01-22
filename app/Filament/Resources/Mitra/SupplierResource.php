<?php

namespace App\Filament\Resources\Mitra;

use App\Filament\Resources\Mitra\SupplierResource\Pages;
use App\Filament\Resources\Mitra\SupplierResource\RelationManagers;
use App\Models\Mitra\Supplier;
use App\Models\Outlet;
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

class SupplierResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 13;
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 9;
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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('supplier_name')
                            ->label('Nama Supplier')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('supplier_alamat')
                            ->autosize()
                            ->label('Alamat'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Email')
                            ->maxLength(70),
                        Forms\Components\TextInput::make('supplier_phone1')
                            ->tel()
                            ->maxLength(15)
                            ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                            ->placeholder('cth: 08123456789'),
                        Forms\Components\TextInput::make('supplier_phone2')
                            ->maxLength(15)
                            ->tel()
                            ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                            ->placeholder('cth: 08123456789'),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('supplier_picname1')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('supplier_picphone1')
                            ->maxLength(15)
                            ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                            ->placeholder('cth: 08123456789'),
                        Forms\Components\TextInput::make('supplier_picname2')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('supplier_picphone2')
                            ->maxLength(15)
                            ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                            ->placeholder('cth: 08123456789'),
                        //
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('phones')
                    ->label('Telp/HP')
                    ->getStateUsing(fn($record) => collect([
                        $record->supplier_phone1,
                        $record->supplier_phone2,
                    ])->filter()->implode(' / ')),
                Tables\Columns\TextColumn::make('pic')
                    ->label('PIC')
                    ->html()
                    ->getStateUsing(fn($record) => collect([
                        $record->supplier_picname1
                            ? $record->supplier_picname1 .
                            (!empty($record->supplier_picphone1)
                                ? ' (' . $record->supplier_picphone1 . ')'
                                : '')
                            : null,
                        $record->supplier_picname2
                            ? $record->supplier_picname2 .
                            (!empty($record->supplier_picphone2)
                                ? ' (' . $record->supplier_picphone2 . ')'
                                : '')
                            : null,
                    ])->filter()->implode('</br>'))
                    ->wrap(),

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
                    ->action(function (Supplier $record) {
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
            'index' => Pages\ListSupplier::route('/'),
        ];
    }
}
