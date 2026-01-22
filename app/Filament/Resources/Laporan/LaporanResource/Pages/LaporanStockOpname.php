<?php

namespace App\Filament\Resources\Laporan\LaporanResource\Pages;

use App\Filament\Resources\Laporan\LaporanResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Carbon\Carbon;

class LaporanStockOpname extends Page
{
    protected static string $resource = LaporanResource::class;
    protected static string $view = 'filament.resources.laporan.laporan-stock-opname';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tanggal' => Carbon::now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Stock Opname')
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('kategori')
                    ->label('Kategori Produk')
                    ->options([
                        'all' => 'Semua Kategori',
                        'obat' => 'Obat-obatan',
                        'alkes' => 'Alat Kesehatan',
                    ])
                    ->default('all'),
            ])
            ->statePath('data');
    }

    public function generateReport()
    {
        $data = $this->form->getState();
        // Logic untuk generate laporan stock opname
        session()->flash('success', 'Laporan stock opname berhasil digenerate');
    }

    public function getTitle(): string
    {
        return 'Laporan Stock Opname';
    }
}