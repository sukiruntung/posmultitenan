<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ConnectPrinter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.connect-printer';
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
    public function connectPrinter()
    {
        $this->dispatch('printer-connect');
        
        // Optional: Add notification
        \Filament\Notifications\Notification::make()
            ->title('Menghubungkan printer...')
            ->info()
            ->send();
    }
}
