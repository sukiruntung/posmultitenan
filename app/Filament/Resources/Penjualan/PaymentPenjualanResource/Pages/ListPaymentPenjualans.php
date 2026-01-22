<?php

namespace App\Filament\Resources\Penjualan\PaymentPenjualanResource\Pages;

use App\Filament\Resources\Penjualan\PaymentPenjualanResource;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class ListPaymentPenjualans extends ListRecords
{
    protected static string $resource = PaymentPenjualanResource::class;
    public ?array $tableFilters = [];
    public ?string $activeTab = 'payment'; // default tab
    public function mount(): void
    {
        $this->tableFilters = [
            'from' => Carbon::now()->subMonth()->toDateString(), // 1 bulan lalu
            'until' => Carbon::now()->toDateString(),            // hari ini
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.tabs.tab', [
            'actions' => $this->getHeaderActions(),
            'title' => 'Payment Penjualan',
            'active' => $this->activeTab,
            'module' => 'penjualan',
        ]);
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if (!empty($this->tableFilters['from'])) {
            $query->whereDate('penjualan_barang_tanggal', '>=', $this->tableFilters['from']);
        }

        if (!empty($this->tableFilters['until'])) {
            $query->whereDate('penjualan_barang_tanggal', '<=', $this->tableFilters['until']);
        }

        return $query->orderBy('penjualan_barang_no', 'desc');
    }
    protected function isTableSearchable(): bool
    {
        return false; // ✅ sembunyikan search default bawaan Filament
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         PaymentTabs::make(['active' => $this->activeTab, 'module' => 'penjualan']),

    //     ];
    // }
}
