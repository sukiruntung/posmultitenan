<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OmsetMarketing extends ChartWidget
{
    protected static ?string $heading = 'Laporan Perbandingan Omset Marketing Per Bulan';
    protected static ?int $sort = 3;
    protected static function user()
    {
        return auth()->user();
    }
    public static function canView(): bool
    {
        $user = static::user();
        $access = $user->getCachedDashboardAccess(3);
        return $access ? $access->can_view : false;
    }

    protected function getData(): array
    {
        $outletId = auth()->user()->userOutlet->outlet_id;

        return Cache::remember('omset_marketing_' . now()->year . '_outlet_' . $outletId, now()->addHours(3), function () use ($outletId) {
            $data = DB::table('penjualan_barangs as pb')
                ->join('customer_marketings as cm', 'pb.customer_id', '=', 'cm.customer_id')
                ->join('marketings as m', 'cm.marketing_id', '=', 'm.id')
                ->select(
                    'm.marketing_name',
                    DB::raw('MONTH(pb.penjualan_barang_tanggal) as bulan'),
                    DB::raw('SUM(pb.penjualan_barang_grandtotal) as total_omset')
                )
                ->where('pb.outlet_id', $outletId)
                ->whereYear('pb.penjualan_barang_tanggal', now()->year)
                ->where('pb.deleted_at', null)
                ->groupBy('m.id', 'bulan')
                ->orderBy('m.id')
                ->orderBy('bulan')
                ->get();

            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $grouped = $data->groupBy('marketing_name');

            $colors = [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(0, 204, 153, 0.8)',
                'rgba(255, 102, 178, 0.8)',
                'rgba(102, 178, 255, 0.8)',
            ];

            $datasets = [];
            $index = 0;

            foreach ($grouped as $marketing => $rows) {
                $bulanValues = array_fill(1, 12, 0);

                foreach ($rows as $row) {
                    $bulanValues[(int) $row->bulan] = (float) $row->total_omset;
                }

                $datasets[] = [
                    'label' => $marketing,
                    'data' => array_values($bulanValues),
                    'backgroundColor' => $colors[$index % count($colors)],
                    'borderColor' => $colors[$index % count($colors)],
                    'fill' => false,
                    'tension' => 0.3,
                ];

                $index++;
            }

            return [
                'datasets' => $datasets,
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
