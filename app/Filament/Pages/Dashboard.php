<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MinStockTable;
use App\Filament\Widgets\OmsetOutletChart;
use App\Filament\Widgets\OmsetWidget;
use App\Filament\Widgets\TransaksiChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Page;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.dashboard';
    public function getWidgets(): array
    {
        return [
            OmsetWidget::class,
            TransaksiChart::class,
            OmsetOutletChart::class,
            MinStockTable::class,
        ];
    }
}
