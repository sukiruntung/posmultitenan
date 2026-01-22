<?php

namespace App\Filament\Resources\Accesses\UserResource\Pages;

use App\Filament\Resources\Accesses\UserResource;
use App\Models\Accesses\User;
use App\Models\Accesses\UserOutlet;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    return DB::transaction(function () use ($data) {
                        $data['user_id'] = Auth::id();
                        $user = User::create($data);
                        UserOutlet::create([
                            'user_id' => $user->id,
                            'outlet_id' => Auth::user()->userOutlet->outlet_id,
                        ]);
                        return $user;
                    });
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),

        ];
    }
}
