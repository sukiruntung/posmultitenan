<?php

namespace App\Filament\Resources\PaymentPenjualanResource\Widgets;

use App\Filament\Resources\Pembelian\PaymentPenerimaanResource;
use App\Filament\Resources\Pembelian\PenerimaanBarangResource;
use Filament\Widgets\Widget;
use App\Filament\Resources\Penjualan\PenjualanBarangResource;
use App\Filament\Resources\Penjualan\PaymentPenjualanResource;

class PaymentTabs extends Widget
{
    public ?string $active = 'barang';
    public ?string $module = '';

    public static function makeWithActive(string $active, string $module = ''): static
    {
        $widget = app(static::class);
        $widget->active = $active;
        $widget->module = $module;
        return $widget;
    }

    protected static string $view = 'filament.widgets.payment-tabs';

    protected function getViewData(): array
    {
        $user = auth()->user();
        $tabs = [];
        if ($this->module == 'penjualan') {

            if ($user->getCachedMenuAccess(3)->can_view) {
                $tabs[] = [
                    'label' => 'Surat Jalan / Faktur',
                    'url' => PenjualanBarangResource::getUrl('index'),
                    'active' => $this->active === 'barang',
                ];
            }

            if ($user->getCachedMenuAccess(4)->can_view) {
                $tabs[] = [
                    'label' => 'Payment Penjualan',
                    'url' => PaymentPenjualanResource::getUrl('index'),
                    'active' => $this->active === 'payment',
                ];
            }
        } elseif ($this->module == 'penerimaan') {
            if ($user->getCachedMenuAccess(2)->can_view) {
                $tabs[] = [
                    'label' => 'Penerimaan Barang',
                    'url' => PenerimaanBarangResource::getUrl('index'),
                    'active' => $this->active === 'barang',
                ];
            }

            if ($user->getCachedMenuAccess(9)->can_view) {
                $tabs[] = [
                    'label' => 'Payment Supplier',
                    'url' => PaymentPenerimaanResource::getUrl('index'),
                    'active' => $this->active === 'payment',
                ];
            }
        }

        return ['tabs' => $tabs];
    }
}
