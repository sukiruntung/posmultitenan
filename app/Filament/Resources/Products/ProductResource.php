<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\ProductResource\Pages;
use App\Filament\Resources\Products\ProductResource\RelationManagers;
use App\Models\Products\Merk;
use App\Models\Products\Product;
use App\Models\Products\Satuan;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
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
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class ProductResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 3;
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
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
                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Select::make('kelompok_product_id')
                            ->label('Kelompok Product')
                            ->relationship('kelompokProduct', 'kelompok_productname', fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
                            ->required(),
                        Forms\Components\TextInput::make('product_kode')
                            ->label('Product Code')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->rule('regex:/^\S+$/') // regex: tidak boleh ada spasi sama sekali
                            ->helperText('Tidak boleh mengandung spasi'),
                        Forms\Components\Checkbox::make('is_auto_catalog')
                            ->label('Generate Otomatis Product Catalog')
                            ->reactive()
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    // kalau dicentang → generate langsung
                                    $set('product_catalog', 'PRD-' . strtoupper(Str::random(4)));
                                } else {
                                    // kalau di-uncheck → kosongkan lagi (opsional)
                                    $set('product_catalog', null);
                                }
                            }),
                        Forms\Components\TextInput::make('product_catalog')
                            ->label('Product Catalog')
                            ->required()
                            ->maxLength(100)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: function (Unique $rule, Get $get) {
                                    return $rule->where('satuan_id', $get('satuan_id'))
                                        ->where('outlet_id', $get('outlet_id'));
                                },
                            )
                            ->rule('regex:/^\S+$/') // regex: tidak boleh ada spasi sama sekali
                            ->helperText('Tidak boleh mengandung spasi')
                            ->afterStateHydrated(function ($state, callable $set, Get $get) {
                                // waktu edit → kalau auto_catalog & kosong → generate otomatis
                                if ($get('is_auto_catalog') && empty($state)) {
                                    $set('product_catalog', 'PRD-' . strtoupper(Str::random(4)));
                                }
                            })
                            ->reactive(),

                        Forms\Components\TextInput::make('product_minstock')
                            ->label('Min Stock')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\TextInput::make('product_name')
                            ->label('Nama Product')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Select::make('satuan_id')
                            ->options(Satuan::where('outlet_id', Auth::user()->userOutlet->outlet_id)->pluck('satuan_name', 'id'))
                            ->searchable()
                            ->label('Satuan')
                            ->required(),
                        Forms\Components\Select::make('merk_id')
                            ->label('Merk')
                            ->relationship('merk', 'merk_name', fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
                            ->default(1)

                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Harga Jual')
                            ->schema([
                                Forms\Components\TextInput::make('hargajualgrosir')
                                    ->numeric()
                                    ->label('Harga Grosir')
                                    ->prefix('Rp')
                                    ->default(0),

                                Forms\Components\TextInput::make('hargajual1')
                                    ->numeric()
                                    ->label('Harga Jual 1')
                                    ->prefix('Rp')
                                    ->default(0),
                                Forms\Components\TextInput::make('hargajual2')
                                    ->numeric()
                                    ->label('Harga Jual 2')
                                    ->prefix('Rp')
                                    ->default(0),
                                Forms\Components\TextInput::make('hargajual3')
                                    ->numeric()
                                    ->label('Harga Jual 3')
                                    ->prefix('Rp')
                                    ->default(0),
                            ]),

                    ]),
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
                Tables\Columns\TextColumn::make('product_kode')
                    ->label('Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_catalog')
                    ->label('Catalog')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Name')
                    ->width('30%')
                    ->searchable(),
                Tables\Columns\TextColumn::make('satuan.satuan_name')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('kelompokProduct.kelompok_productname')
                    ->label('Kelompok Product'),
                Tables\Columns\TextColumn::make('product_minstock')
                    ->label('Minimum Stock')
                    ->numeric(),

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
                    ->action(function (Product $record) {
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
            'index' => Pages\ListProducts::route('/'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['merk_id'])) {
            $merk = Merk::find($data['merk_id']);
            if ($merk) {
                $data['product_catalog'] = $data['product_catalog'] . '_' . $merk->merk_name;
            }
        }
        $data['product_slug'] = Str::slug($data['product_name']);
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['merk_id'])) {
            $merk = Merk::find($data['merk_id']);
            if ($merk) {
                $data['product_catalog'] = $data['product_catalog'] . '_' . $merk->merk_name;
            }
        }
        $data['product_slug'] = Str::slug($data['product_name']);
        return $data;
    }
}
