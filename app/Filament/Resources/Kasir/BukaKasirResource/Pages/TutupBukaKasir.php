<?php

namespace App\Filament\Resources\Kasir\BukaKasirResource\Pages;

use App\Filament\Resources\Kasir\BukaKasirResource;
use App\Models\Accounting\KasPemasukkan;
use App\Models\Accounting\KasPengeluaran;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TutupBukaKasir extends EditRecord implements HasTable
{
    use InteractsWithTable;
    public int $selisih = 0;
    protected static string $resource = BukaKasirResource::class;

    protected static string $view = 'filament.pages.tutup-buka-kasir';
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
                    ->date()
                    ->label('Tanggal')
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->readOnly()
                    ->afterStateHydrated(function ($component, $state) {
                        if (blank($state)) {
                            $component->state(now());
                        }
                    }),
                Forms\Components\TextInput::make('kas_harian_saldoakhir')
                    ->label('Saldo Akhir')
                    ->numeric()
                    ->prefix('Rp')
                    ->afterStateHydrated(function (Forms\Components\TextInput $component) {
                        $component->state($this->calculateSaldoSeharusnya());
                    })
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $saldoSeharusnya = (float) $get('kas_harian_saldoseharusnya');
                        $saldoAkhir = (float) $state;

                        $selisih =   $saldoAkhir - $saldoSeharusnya;
                        $this->selisih = $selisih;

                        $set('kas_harian_selisih', $selisih);
                    }),
                Forms\Components\TextInput::make('kas_harian_saldoseharusnya')
                    ->label('Saldo Seharusnya')
                    ->numeric()
                    ->prefix('Rp')
                    ->afterStateHydrated(function (Forms\Components\TextInput $component) {
                        $component->state($this->calculateSaldoSeharusnya());
                    })
                    ->readOnly(),

                Forms\Components\TextInput::make('kas_harian_selisih')
                    ->label('Selisih')
                    ->readOnly()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\Textarea::make('kas_harian_notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('kas_harian_status')
                    ->default('tutup'),
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Tutup Kasir')
                ->action('save')
                ->color('danger'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (Auth::user()->is_kasir == false) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Akun anda tidak berhak melakukan transaksi ini')
                ->danger()
                ->send();
            throw new Halt();
        }
        $data['kas_harian_tanggaltutup'] = now();
        $data['kas_harian_status'] = 'tutup';
        return $data;
    }

    private function calculateSaldoSeharusnya(): float
    {
        $saldoAwal = $this->record->kas_harian_saldoawal;

        $pemasukkan = KasPemasukkan::where('kas_harian_id', $this->record->id)
            ->where('kas_pemasukkan_jenis', 'masuk')
            ->sum('kas_pemasukkan_jumlah');

        $pengeluaran = KasPemasukkan::where('kas_harian_id', $this->record->id)
            ->where('kas_pemasukkan_jenis', 'keluar')
            ->sum('kas_pemasukkan_jumlah');

        return $saldoAwal + $pemasukkan - $pengeluaran;
    }

    public function getTotalPemasukkan(): float
    {
        return KasPemasukkan::where('kas_harian_id', $this->record->id)
            ->where('kas_pemasukkan_jenis', 'masuk')
            ->sum('kas_pemasukkan_jumlah');
    }

    public function getTotalPengeluaran(): float
    {
        return KasPemasukkan::where('kas_harian_id', $this->record->id)
            ->where('kas_pemasukkan_jenis', 'keluar')
            ->sum('kas_pemasukkan_jumlah');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
