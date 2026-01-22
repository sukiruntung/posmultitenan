<?php

namespace App\Filament\Widgets;

use App\Models\Penjualan\PenjualanBarang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OmsetWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static function user()
    {
        return auth()->user();
    }
    public static function canView(): bool
    {
        $user = static::user();
        $access = $user->getCachedDashboardAccess(1);
        return $access ? $access->can_view : false;
    }
    protected function getStats(): array
    {
        $outletId = Auth::user()->userOutlet->outlet_id;

        $today = Cache::remember('omset_today_' . today()->format('Y-m-d') . '_outlet_' . $outletId, now()->addHours(3), function () use ($outletId) {
            return PenjualanBarang::where('outlet_id', $outletId)->whereDate('penjualan_barang_tanggal', today())->sum('penjualan_barang_grandtotal');
        });

        $month = Cache::remember('omset_month_' . now()->format('Y-m') . '_outlet_' . $outletId, now()->addHours(3), function () use ($outletId) {
            return PenjualanBarang::where('outlet_id', $outletId)->whereMonth('penjualan_barang_tanggal', now()->month)->sum('penjualan_barang_grandtotal');
        });

        $year = Cache::remember('omset_year_' . now()->year . '_outlet_' . $outletId, now()->addHours(3), function () use ($outletId) {
            return PenjualanBarang::where('outlet_id', $outletId)->whereYear('penjualan_barang_tanggal', now()->year)->sum('penjualan_barang_grandtotal');
        });

        return [
            Stat::make('Omset Hari Ini', 'Rp ' . number_format($today, 0, ',', '.'))
                ->description('Total penjualan hari ini')
                ->color('success'),

            Stat::make('Omset Bulan Ini', 'Rp ' . number_format($month, 0, ',', '.'))
                ->description('Total penjualan bulan ini')
                ->color('primary'),

            Stat::make('Omset Tahun Ini', 'Rp ' . number_format($year, 0, ',', '.'))
                ->description('Total penjualan tahun ini')
                ->color('info'),
        ];
    }
}
