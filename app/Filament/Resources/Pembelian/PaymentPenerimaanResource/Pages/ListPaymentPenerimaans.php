<?php

namespace App\Filament\Resources\Pembelian\PaymentPenerimaanResource\Pages;

use App\Filament\Resources\PaymentPenjualanResource\Widgets\PaymentTabs;
use App\Filament\Resources\Pembelian\PaymentPenerimaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ListPaymentPenerimaans extends ListRecords
{
    protected static string $resource = PaymentPenerimaanResource::class;

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
            'actions' => [],
            'title' => 'Payment Supplier',
            'active' => $this->activeTab,
            'module' => 'penerimaan',
        ]);
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if (!empty($this->tableFilters['from'])) {
            $query->whereDate('penerimaan_barang_tanggal', '>=', $this->tableFilters['from']);
        }

        if (!empty($this->tableFilters['until'])) {
            $query->whereDate('penerimaan_barang_tanggal', '<=', $this->tableFilters['until']);
        }

        return $query->orderBy('penerimaan_barang_invoicenumber', 'desc');
    }
    protected function isTableSearchable(): bool
    {
        return false; // ✅ sembunyikan search default bawaan Filament
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         PaymentTabs::make(['active' => $this->activeTab, 'module' => 'penerimaan']),

    //     ];
    // }

}
