<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OmsetOutletChart extends ChartWidget
{
    protected static ?string $heading = 'Omset Semua Outlet';

    protected static ?int $sort = 3;

    protected static function user()
    {
        return auth()->user();
    }

    public static function canView(): bool
    {
        $user = static::user();

        if (! $user) {
            return false;
        }

        $access = $user->getCachedDashboardAccess(6);

        return $access ? (bool) $access->can_view : false;
    }

    public function mount(): void
    {
        $this->filter ??= (string) now()->year;

        parent::mount();
    }

    protected function getFilters(): ?array
    {
        $currentYear = now()->year;
        $filters = [];

        for ($year = $currentYear; $year >= $currentYear - 4; $year--) {
            $filters[(string) $year] = (string) $year;
        }

        return $filters;
    }

    protected function getData(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [
                'datasets' => [],
                'labels' => $this->monthLabels(),
            ];
        }

        $userId = $user->id;
        $year = (int) ($this->filter ?? now()->year);

        return Cache::remember("omset_outlet_chart_user_{$userId}_year_{$year}", now()->addHours(3), function () use ($userId, $year) {
            $results = DB::table('penjualan_barangs as pb')
                ->join('outlets as o', 'o.id', '=', 'pb.outlet_id')
                ->leftJoin('user_outlets as uo', function ($join) use ($userId) {
                    $join
                        ->on('uo.outlet_id', '=', 'o.id')
                        ->where('uo.user_id', '=', $userId)
                        ->whereNull('uo.deleted_at');
                })
                ->select(
                    'o.id',
                    'o.outlet_name',
                    DB::raw('MONTH(pb.penjualan_barang_tanggal) as bulan'),
                    DB::raw('SUM(pb.penjualan_barang_grandtotal) as total_omzet')
                )
                ->whereNull('pb.deleted_at')
                ->where('pb.penjualan_barang_status', 'validated')
                ->whereYear('pb.penjualan_barang_tanggal', $year)
                ->where(function ($query) use ($userId) {
                    $query->where('o.owner_user_id', $userId)
                        ->orWhereNotNull('uo.user_id');
                })
                ->groupBy('o.id', 'o.outlet_name', 'bulan')
                ->orderBy('o.outlet_name')
                ->get();

            $labels = $this->monthLabels();
            $grouped = $results->groupBy('outlet_name');

            $palette = [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(99, 102, 241, 0.8)',
                'rgba(234, 179, 8, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(13, 148, 136, 0.8)',
                'rgba(147, 51, 234, 0.8)',
            ];

            $datasets = [];
            $index = 0;

            foreach ($grouped as $outletName => $rows) {
                $monthlyTotals = array_fill(1, 12, 0);

                foreach ($rows as $row) {
                    $monthIndex = (int) $row->bulan;

                    if ($monthIndex >= 1 && $monthIndex <= 12) {
                        $monthlyTotals[$monthIndex] = (float) $row->total_omzet;
                    }
                }

                $color = $palette[$index % count($palette)];

                $datasets[] = [
                    'label' => $outletName,
                    'data' => array_values($monthlyTotals),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'borderWidth' => 1,
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

    /**
     * @return array<int, string>
     */
    private function monthLabels(): array
    {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    }
}
