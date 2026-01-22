<?php

namespace App\Filament\Resources\Pembelian;

use App\Filament\Resources\Pembelian\KasPengeluaranResource\Pages;
use App\Models\Accounting\KasPemasukkan;
use App\Models\Accounting\KasPengeluaran;
use App\Models\Accounting\KategoriPengeluaran;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KasPengeluaranResource extends Resource
{
    use CheckPermissionAccess;
    protected static ?string $model = KasPengeluaran::class;
    protected static int $menuId = 6;
    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';
    protected static ?string $navigationGroup = 'Pembelian';
    protected static ?string $navigationLabel = 'Pengeluaran Lain-Lain';
    protected static ?string $label = 'Pengeluaran Lain-Lain';
    protected static ?int $navigationSort = 9;

    public static function shouldRegisterNavigation(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function canAccess(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function canCreate(): bool
    {
        return static::checkMenuAccess('can_create', static::$menuId);
    }

    public static function canEdit(Model $record): bool
    {
        return static::checkMenuAccess('can_edit', static::$menuId);
    }
    public static function canDelete(Model $record): bool
    {
        return static::checkMenuAccess('can_delete', static::$menuId);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('kas_pengeluaran_tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),
                Forms\Components\Hidden::make('outlet_id')
                    ->default(Auth::user()->userOutlet->outlet_id),
                Forms\Components\Select::make('kategori_pengeluaran_id')
                    ->label('Kategori Pengeluaran')
                    ->options(KategoriPengeluaran::where('kategori_pengeluaran_name', '!=', 'Payment Supplier')
                        ->where('outlet_id', Auth::user()->userOutlet->outlet_id)
                        ->pluck('kategori_pengeluaran_name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('kas_pengeluaran_jumlah')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\Textarea::make('kas_pengeluaran_notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kas_pengeluaran_tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategoriPengeluaran.kategori_pengeluaran_name')
                    ->label('Kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kas_pengeluaran_jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kas_pengeluaran_notes')
                    ->label('Catatan')
                    ->limit(50),
            ])
            ->defaultSort('kas_pengeluaran_tanggal', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->closeModalByClickingAway(false)
                    ->modalHeading('Edit Kas Pengeluaran')
                    ->modalWidth('lg')
                    ->mutateFormDataUsing(function (array $data, $record) {
                        DB::transaction(function () use ($data, $record) {
                            $kategoriPengeluaran = KategoriPengeluaran::find($data['kategori_pengeluaran_id']);
                            KasPemasukkan::where('kas_pemasukkan_reference', $record->id)
                                ->where('kas_pemasukkan_sumber', 'KasPengeluaran')
                                ->update([
                                    'kas_pemasukkan_jumlah' => $data['kas_pengeluaran_jumlah'],
                                    'kas_pemasukkan_notransaksi' => $kategoriPengeluaran->kategori_pengeluaran_name,
                                    'kas_pemasukkan_notes' => $data['kas_pengeluaran_notes'] ?? null
                                ]);
                        });
                        $data['user_id'] = Auth::id();
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (KasPengeluaran $record) {
                        DB::transaction(function () use ($record) {

                            KasPemasukkan::where('kas_pemasukkan_reference', $record->id)
                                ->where('kas_pemasukkan_sumber', 'KasPengeluaran')
                                ->delete();
                            $record->user_id = Auth::id();
                            $record->save();

                            // hapus kas pengeluaran
                            $record->delete();
                        });
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKasPengeluarans::route('/'),
            // 'create' => Pages\CreateKasPengeluaran::route('/create'),
            // 'edit' => Pages\EditKasPengeluaran::route('/{record}/edit'),
        ];
    }
}
