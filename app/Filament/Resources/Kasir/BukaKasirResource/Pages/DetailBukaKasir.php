<?php

namespace App\Filament\Resources\Kasir\BukaKasirResource\Pages;

use App\Filament\Resources\Kasir\BukaKasirResource;
use App\Models\Accounting\KasPemasukkan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DetailBukaKasir extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = BukaKasirResource::class;

    protected static string $view = 'filament.pages.detail-buka-kasir';



    public function getHeader(): ?View
    {
        return null;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return '';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('kas_harian_tanggaltutup')
                    ->label('Tanggal Tutup')
                    ->readOnly(),
                Forms\Components\TextInput::make('kas_harian_saldoakhir')
                    ->label('Saldo Akhir')
                    ->readOnly()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('kas_harian_saldoseharusnya')
                    ->label('Saldo Seharusnya')
                    ->readOnly()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('kas_harian_selisih')
                    ->label('Selisih')
                    ->readOnly()
                    ->prefix('Rp'),
                Forms\Components\Textarea::make('kas_harian_notes')
                    ->label('Catatan')
                    ->readOnly()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KasPemasukkan::query()
                    ->where('kas_harian_id', $this->record->id)
                    ->with('kasPemasukkanSumber')
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date(),
                Tables\Columns\TextColumn::make('kas_pemasukkan_notransaksi')
                    ->label('Sumber')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kas_pemasukkan_jenis')
                    ->label('Jenis Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kas_pemasukkan_jumlah')
                    ->label('Jumlah')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('kas_pemasukkan_notes')
                    ->label('Catatan')
                    ->limit(50),
            ])
            ->paginated(false);
    }

    public function getTotalPemasukkan(): float
    {
        return Cache::remember("tutup_kasir_pemasukkan:{$this->record->id}", now()->addHours(4), function () {
            return KasPemasukkan::where('kas_harian_id', $this->record->id)
                ->where('kas_pemasukkan_jenis', 'masuk')
                ->sum('kas_pemasukkan_jumlah');
        });
    }

    public function getTotalPengeluaran(): float
    {
        return Cache::remember("tutup_kasir_pengeluaran:{$this->record->id}", now()->addHours(4), function () {
            return KasPemasukkan::where('kas_harian_id', $this->record->id)
                ->where('kas_pemasukkan_jenis', 'keluar')
                ->sum('kas_pemasukkan_jumlah');
        });
    }
}
