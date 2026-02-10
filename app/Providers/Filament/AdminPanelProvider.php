<?php

namespace App\Providers\Filament;

use App\Models\Accesses\Outlet;
use App\Services\TextQueueService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    private const DEFAULT_BRAND_NAME = 'CJNAAPP';

    private const DEFAULT_BRAND_LOGO_PATH = '';

    private bool $hasResolvedOutlet = false;

    private ?Outlet $resolvedOutlet = null;

    public function panel(Panel $panel): Panel
    {
        // URL::forceScheme(scheme: 'https');
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(fn() => $this->resolveBrandName())
            ->brandLogo(function () {
                $name = $this->resolveBrandName();
                $logo = Auth::check() ? $this->resolveBrandLogo() : null;

                return view('filament.brand', compact('name', 'logo'));
            })
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            // ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->assets([
                Css::make('custom', asset('css/filament-custom.css')),
            ])
            // ->homeUrl('admin/sales-overview')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // SalesOverview::class,
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // TransaksiChart::class,
                // OmsetWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::topbar.start',
            fn() => view('partials.running-text', [
                'text' => app(TextQueueService::class)->forUser(Auth::user())
            ])
        );
    }

    protected function resolveBrandName(): string
    {
        // echo $this->resolveOutletForAuthenticatedUser()?->outlet_name;
        return $this->resolveOutletForAuthenticatedUser()?->outlet_name ?? self::DEFAULT_BRAND_NAME;
    }

    protected function resolveBrandLogo(): string
    {
        return $this->resolveOutletLogoPath(
            $this->resolveOutletForAuthenticatedUser()?->outlet_logo
        ) ?? asset(self::DEFAULT_BRAND_LOGO_PATH);
    }

    protected function resolveOutletForAuthenticatedUser(): ?Outlet
    {
        if ($this->hasResolvedOutlet) {
            return $this->resolvedOutlet;
        }

        $this->hasResolvedOutlet = true;

        $user = Auth::user();

        if (!$user) {
            return null;
        }

        $user->loadMissing('userOutlet.outlet');

        $this->resolvedOutlet = $user->userOutlet?->outlet
            ?? Outlet::query()
            ->where('owner_user_id', $user->id)
            ->first();
        return $this->resolvedOutlet;
    }

    protected function resolveOutletLogoPath(?string $logoPath): ?string
    {
        if (!$logoPath) {
            return null;
        }

        if (filter_var($logoPath, FILTER_VALIDATE_URL)) {
            return $logoPath;
        }

        $normalizedPath = ltrim($logoPath, '/');

        if (Storage::disk('public')->exists($normalizedPath)) {
            return Storage::disk('public')->url($normalizedPath);
        }

        if (is_file(public_path($normalizedPath))) {
            return asset($normalizedPath);
        }

        $storageRelativePath = str_starts_with($normalizedPath, 'storage/')
            ? $normalizedPath
            : 'storage/' . $normalizedPath;

        if (is_file(public_path($storageRelativePath))) {
            return asset($storageRelativePath);
        }

        return null;
    }
}
