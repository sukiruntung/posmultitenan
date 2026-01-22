<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasDateRangeFilter
{
    // public ?array $tableFilters = [];

    // public function mountDateFilter(): void
    // {
    //     $this->tableFilters = [
    //         'from' => Carbon::now()->subMonth()->toDateString(),
    //         'until' => Carbon::now()->toDateString(),
    //     ];
    // }

    public function applyDateFilter(Builder $query, array $tableFilters): Builder
    {
        DB::enableQueryLog();
        // dd($tableFilters);
        if (!empty($tableFilters['from'])) {
            info('FROM FILTER:', [$tableFilters['from']]);
            $query->whereDate('penjualan_barang_tanggal', '>=', $tableFilters['from']);
        }

        if (!empty($tableFilters['until'])) {
            info('UNTIL FILTER:', [$tableFilters['until']]);
            $query->whereDate('penjualan_barang_tanggal', '<=', $tableFilters['until']);
        }
        // dd(
        //     $query->toSql(),
        //     $query->getBindings()
        // );
        $queries = DB::getQueryLog();
        DB::listen(function ($query) {
            Log::info($query->sql, $query->bindings);
        });
        return $query;
    }
}
