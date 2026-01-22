<?php

namespace App\Filament\Resources\Kasir\BukaKasirResource\Pages;

use App\Filament\Resources\Kasir\BukaKasirResource;
use App\Models\Accounting\KasHarian;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListBukaKasirs extends ListRecords
{
    protected static string $resource = BukaKasirResource::class;

    public function mount(): void
    {
        $this->tableFilters = [
            'from' => Carbon::now()->subMonth()->toDateString(), // 1 bulan lalu
            'until' => Carbon::now()->toDateString(),            // hari ini
        ];
    }
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if (!empty($this->tableFilters['from'])) {
            $query->whereDate('kas_harian_tanggalbuka', '>=', $this->tableFilters['from']);
        }

        if (!empty($this->tableFilters['until'])) {
            $query->whereDate('kas_harian_tanggalbuka', '<=', $this->tableFilters['until']);
        }

        return $query;
    }
    protected function isTableSearchable(): bool
    {
        return false; // ✅ sembunyikan search default bawaan Filament
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data) {
                    if (Auth::user()->is_kasir == false) {
                        Notification::make()
                            ->title('Gagal menyimpan')
                            ->body('Akun anda tidak berhak melakukan transaksi ini')
                            ->danger()
                            ->send();
                        throw new Halt();
                    }
                    $data['user_id'] = Auth::id();
                    $data['kasir_id'] = Auth::id();
                    $data['kas_harian_tanggalbuka'] = now();
                    return KasHarian::create($data);
                })
                ->visible(fn() => !$this->hasOpenKasToday()),
        ];
    }

    private function hasOpenKasToday(): bool
    {
        return KasHarian::whereDate('kas_harian_tanggalbuka', today())
            ->where('kas_harian_status', 'buka')
            ->where('kasir_id', Auth::id())
            ->exists();
    }
}
