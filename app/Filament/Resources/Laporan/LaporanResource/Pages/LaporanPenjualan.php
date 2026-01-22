<?php

namespace App\Filament\Resources\Laporan\LaporanResource\Pages;

use App\Filament\Resources\Laporan\LaporanResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Carbon\Carbon;

class LaporanPenjualan extends Page
{
    protected static string $resource = LaporanResource::class;
    protected static string $view = 'filament.resources.laporan.laporan-penjualan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_awal' => Carbon::now()->startOfMonth()->toDateString(),
            'tanggal_akhir' => Carbon::now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal_awal')
                    ->label('Tanggal Awal')
                    ->required()
                    ->native(false),
                Forms\Components\DatePicker::make('tanggal_akhir')
                    ->label('Tanggal Akhir')
                    ->required()
                    ->native(false),
            ])
            ->statePath('data');
    }

    public function generateReport()
    {
        $data = $this->form->getState();
        // Logic untuk generate laporan penjualan
        session()->flash('success', 'Laporan penjualan berhasil digenerate');
    }

    public function getTitle(): string
    {
        return 'Laporan Penjualan Periode';
    }
}