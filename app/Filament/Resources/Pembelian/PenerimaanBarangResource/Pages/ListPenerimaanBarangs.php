<?php

namespace App\Filament\Resources\Pembelian\PenerimaanBarangResource\Pages;

use App\Filament\Resources\PaymentPenjualanResource\Widgets\PaymentTabs;
use App\Filament\Resources\Pembelian\PenerimaanBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ListPenerimaanBarangs extends ListRecords
{
    protected static string $resource = PenerimaanBarangResource::class;
    public ?array $tableFilters = [];
    public ?string $activeTab = 'barang';
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
            'title' => 'Penerimaan Barang',
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
                ->label('New Penerimaan Barang')
                ->icon('heroicon-o-plus')
                ->url(fn() => static::getResource()::getUrl('create'))
        ];
    }
    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         PaymentTabs::make(['active' => $this->activeTab, 'module' => 'penerimaan']),

    //     ];
    // }
}
