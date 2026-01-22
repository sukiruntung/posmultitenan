<?php

namespace App\Providers;

use App\Services\ThermalPrinterService;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ThermalPrinterService::class, function ($app) {
            return new ThermalPrinterService();
        });

        // $this->app->singleton(QueueService::class, function ($app) {
        //     return new QueueService();
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // if (env('APP_ENV') === 'local' && request()->header('x-forwarded-proto') === 'https') {
        //     URL::forceScheme('https');
        // }
        // RateLimiter::for('filament-login', function ($request) {
        //     $email = strtolower((string) $request->email);
        //     $ip = $request->ip();
        //     $key = 'login:' . $email . '|' . $ip;

        //     // Jika sudah diblokir → tolak langsung
        //     if (Cache::has("blocked:$email")) {
        //         $remaining = now()->diffInMinutes(Cache::get("blocked:$email"));
        //         $minutes = $remaining > 0 ? $remaining : 15;
        //         abort(429, "Email ini diblokir. Coba lagi setelah $minutes menit.");
        //     }

        //     // Batas 3 kali percobaan dalam 1 menit
        //     return Limit::perMinute(3)->by($key)->response(function () use ($email) {
        //         // Saat gagal ke-3, tandai email ini diblokir selama 15 menit
        //         Cache::put("blocked:$email", now()->addMinutes(15), now()->addMinutes(15));
        //         abort(429, 'Terlalu banyak percobaan login gagal. Email ini diblokir selama 15 menit.');
        //     });
        // });
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        //contoh mendaftarkan js di seluruh aplikasi
        FilamentAsset::register([
            Js::make('thermal-printer', asset('js/thermal-printer.js')),
            Js::make('thermal-printer-listener', asset('js/thermal-printer-listener.js')),
            // Js::make('call-queue', asset('js/call-queue.js'))
        ]);
    }
}
