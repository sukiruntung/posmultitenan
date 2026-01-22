<?php

namespace App\Filament\Widgets;

use App\Models\Penjualan\PenjualanBarang;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TransaksiChart extends ChartWidget
{
    protected static ?string $heading = 'Laporan Omset Penjualan per Bulan';

    protected static ?int $sort = 2;
    protected static function user()
    {
        return auth()->user();
    }
    public static function canView(): bool
    {
        $user = static::user();
        $access = $user->getCachedDashboardAccess(2);
        return $access ? $access->can_view : false;
    }
    // protected int|string|array $columnSpan = 'full'; perintah menjadikan full width
    protected function getData(): array
    {
        $outletId = Auth::user()->userOutlet->outlet_id;

        return Cache::remember('transaksi_chart_' . now()->year . '_outlet_' . $outletId, now()->addHours(3), function () use ($outletId) {
            $labels = [];
            $data = [];

            for ($i = 1; $i <= 12; $i++) {
                $labels[] = now()->startOfYear()->addMonths($i - 1)->format('M');
                $data[] = PenjualanBarang::where('outlet_id', $outletId)
                    ->whereMonth('penjualan_barang_tanggal', $i)
                    ->whereYear('penjualan_barang_tanggal', now()->year)
                    ->sum('penjualan_barang_grandtotal');
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Omset',
                        'data' => $data,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
