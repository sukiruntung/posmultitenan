<?php

namespace App\Filament\Resources\Pembelian\KasPengeluaranResource\Pages;

use App\Filament\Resources\Pembelian\KasPengeluaranResource;
use App\Models\Accounting\KasHarian;
use App\Models\Accounting\KasPemasukkan;
use App\Models\Accounting\KasPengeluaran;
use App\Models\Accounting\KategoriPengeluaran;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListKasPengeluarans extends ListRecords
{
    protected static string $resource = KasPengeluaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Tambah Kas Pengeluaran')
                ->modalWidth('lg')
                ->using(function (array $data) {
                    DB::transaction(function () use ($data) {

                        if (Auth::user()->is_kasir == false) {
                            Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Akun anda tidak berhak melakukan transaksi ini')
                                ->danger()
                                ->send();

                            return null;
                        }
                        $kategoriPengeluaran = KategoriPengeluaran::find($data['kategori_pengeluaran_id']);
                        $kas = KasHarian::where('kasir_id', Auth::id())
                            ->where('outlet_id', $data['outlet_id'])
                            ->whereDate('kas_harian_tanggalbuka', today())
                            ->first();

                        if (!$kas) {
                            $kas = KasHarian::create([
                                'kasir_id' => Auth::id(),
                                'outlet_id' => $data['outlet_id'],
                                'kas_harian_tanggalbuka' => now(),
                                'kas_harian_tanggaltutup' => null,
                                'kas_harian_status' => 'buka',
                                'user_id' => Auth::id(),
                            ]);
                        }
                        $data['kas_harian_id'] = $kas->id;
                        $data['kasir_id'] = Auth::id();
                        $data['user_id'] = Auth::id();

                        $kasPengeluaran = KasPengeluaran::create($data);
                        return  KasPemasukkan::create([
                            'kas_harian_id' =>  $kas->id, // <— ambil ID kas hari ini
                            'kasir_id' => Auth::id(),
                            'kas_pemasukkan_jenis' => 'keluar',
                            'kas_pemasukkan_jumlah' => $data['kas_pengeluaran_jumlah'],
                            'kas_pemasukkan_sumber' => 'KasPengeluaran',
                            'kas_pemasukkan_reference' => $kasPengeluaran->id,
                            'kas_pemasukkan_notransaksi' => $kategoriPengeluaran->kategori_pengeluaran_name,
                            'kas_pemasukkan_notes' => $data['kas_pengeluaran_notes'],
                            'user_id' => Auth::id(),
                        ]);
                    });
                }),
        ];
    }
}
