<?php

namespace App\Filament\Resources\Laporan;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\Page;
use App\Filament\Resources\Laporan\LaporanResource\Pages;
use App\Traits\CheckPermissionAccess;
use Illuminate\Database\Eloquent\Model;

class LaporanResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 8;
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan lain-lain';

    protected static ?string $navigationGroup = 'Admin';
    protected static ?int $navigationSort = 20;
    public static function shouldRegisterNavigation(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function canAccess(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporan::route('/'),
            'penjualan' => Pages\LaporanPenjualan::route('/penjualan'),
            'pembelian' => Pages\LaporanPembelian::route('/pembelian'),
            'stock-opname' => Pages\LaporanStockOpname::route('/stock-opname'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }
}
