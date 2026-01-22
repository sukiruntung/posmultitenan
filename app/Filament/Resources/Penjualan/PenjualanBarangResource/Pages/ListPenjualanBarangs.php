<?php

namespace App\Filament\Resources\Penjualan\PenjualanBarangResource\Pages;

use App\Filament\Resources\Penjualan\PaymentPenjualanResource;
use App\Filament\Resources\Penjualan\PenjualanBarangResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Assets\Js;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentPenjualanResource\Widgets\PaymentTabs;
use App\Exports\LaporanPenjualanPivotExport;
use App\Exports\LaporanPenjualanPivotExport2Header;
use App\Exports\LaporanPenjualanPivotExportTemplate;
use Maatwebsite\Excel\Facades\Excel;

class ListPenjualanBarangs extends ListRecords
{
    protected static string $resource = PenjualanBarangResource::class;
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
            'title' => 'Surat Jalan / Faktur',
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
    public function getHeaderScripts(): array
    {
        return [
            Js::make('thermal-printer', asset('js/thermal-printer.js')),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Penjualan Barang')
                ->icon('heroicon-o-plus')
                ->url(fn() => static::getResource()::getUrl('create')),

            Actions\Action::make('connect_printer')
                ->label('Sambungkan Printer')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function ($livewire) {
                    $livewire->dispatch('printer-connect');
                }),

            Actions\Action::make('reset_printer')
                ->label('Reset Printer')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function ($livewire) {
                    $livewire->dispatch('printer-reset');
                }),
        ];
    }
    // public function getFooterScripts(): array
    // {
    //     return [
    //         Js::make('thermal-printer', asset('js/thermal-printer.js')),
    //     ];
    // }

    public function getFooter(): ?View
    {
        return view('filament.layouts.app');
    }

    public function connectPrinter()
    {
        $this->dispatch('printer-connect');
    }
    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         PaymentTabs::make(['active' => $this->activeTab, 'module' => 'penjualan'])
    //     ];
    // }

}
