<?php

namespace App\Filament\Resources\Mitra\CustomerResource\Pages;

use App\Filament\Resources\Mitra\CustomerResource;
use App\Models\Mitra\Customer;
use App\Models\Mitra\CustomerMarketing;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListCustomer extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id();
                    $data['outlet_id'] = Auth::user()->userOutlet->outlet_id;
                    $marketingId = $data['marketing_id'] ?? null;
                    unset($data['marketing_id']); // biar aman saat create Customer

                    $customer = Customer::create($data);

                    if ($marketingId) {
                        CustomerMarketing::updateOrCreate(
                            [
                                'customer_id'  => $customer->id,
                            ],
                            [
                                'marketing_id' => $marketingId,
                                'user_id'      => Auth::id(),
                            ]
                        );
                    }
                    return $customer;
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
