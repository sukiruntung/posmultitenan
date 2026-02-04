<?php

namespace App\Filament\Resources\Laporan\LaporanResource\Pages;

use App\Filament\Resources\Laporan\LaporanResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Accesses\Laporan;
use App\Models\Accesses\LaporanAccess;
use App\Traits\CheckPermissionAccess;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ListLaporan extends Page
{
    use CheckPermissionAccess;
    protected static string $resource = LaporanResource::class;
    protected static string $view = 'filament.resources.laporan.list-laporan';
    public int $userGroupId;
    public ?int $outletID = null;
    public ?array $data = [];
    public ?array $laporanData = [];
    public array $laporanParams = [];
    public $url = '';
    public $namareport = 'rpt_omsetperiode';
    public function mount(): void
    {
        $this->userGroupId = Auth::User()->user_group_id;
        $this->outletID = Auth::User()->userOutlet->outlet_id;

        // muat daftar laporan yang user punya akses
        $this->loadLaporansForUser();

        // set default tanggal jika parameter ada
        $defaults = [
            'PStartDate' => Carbon::now()->startOfMonth()->toDateString(),
            'PEndDate' => Carbon::now()->toDateString(),
        ];

        // isi form state hanya untuk key yang relevan nanti (form akan menampilkan berdasarkan params)
        $this->form->fill($defaults);

        // Load params ke instance untuk menghindari query berulang
        $this->loadLaporanParams();
    }

    protected function loadLaporansForUser(): void
    {
        if (! $this->userGroupId) {
            return;
        }
        // dd($this->userGroupId);
        $this->laporanData = static::checkLaporanAccess($this->userGroupId);
    }

    protected function loadLaporanParams(): void
    {
        if (!$this->userGroupId) {
            return;
        }
        foreach ($this->laporanData as $id => $meta) {
            $this->laporanParams[$id] = $meta['params'];
        }
    }

    protected function getLaporanOptions(): array
    {
        $options = [];
        foreach ($this->laporanData as $id => $meta) {
            $options[$id] = $meta['nama'];
        }
        return $options;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('laporan_id')
                    ->label('Pilih Laporan')
                    ->options(fn() => $this->getLaporanOptions())
                    ->reactive()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $set('path', $this->laporanData[$state]['path'] ?? '');
                            $set('is_excel', $this->laporanData[$state]['is_excel'] == 1 ? true : false);
                            $set('path_excel', $this->laporanData[$state]['path_excel'] ?? '');
                            $set('laporan_name', $this->laporanData[$state]['nama'] ?? '');
                        }
                    }),
                Forms\Components\Hidden::make('path'),
                Forms\Components\Hidden::make('laporan_name'),
                Forms\Components\Hidden::make('is_excel'),
                Forms\Components\Hidden::make('path_excel'),
                Forms\Components\Group::make()
                    ->schema(function ($get) {
                        $laporanId = $get('laporan_id');
                        if (! $laporanId || empty($this->laporanParams[$laporanId])) {
                            return [];
                        }

                        $schema = [];
                        foreach ($this->laporanParams[$laporanId] as $key => $config) {
                            $type = $config['type'] ?? 'text';
                            $label = $config['label'] ?? ucfirst(str_replace('_', ' ', $key));

                            // Buat field sesuai tipe-nya
                            switch ($type) {
                                case 'date':
                                    $schema[] = Forms\Components\DatePicker::make($key)
                                        ->label($label)
                                        ->native(false)
                                        ->required();
                                    break;

                                case 'select':
                                    $options = [];

                                    // Kalau pakai source model
                                    if (!empty($config['source']) && class_exists($config['source'])) {
                                        $modelClass = $config['source'];
                                        $display = $config['display_column'] ?? 'name';
                                        $value = $config['value_column'] ?? 'id';

                                        // Jalankan query model → ambil semua data
                                        $options = $modelClass::query()
                                            ->pluck($display, $value)
                                            ->toArray();
                                    }
                                    // Kalau ada options statis, pakai itu
                                    elseif (!empty($config['options'])) {
                                        $options = $config['options'];
                                    }

                                    $schema[] = Forms\Components\Select::make($key)
                                        ->label($label)
                                        ->options($options)
                                        ->searchable();
                                    break;

                                default:
                                    $schema[] = Forms\Components\TextInput::make($key)
                                        ->label($label)
                                        ->required();
                                    break;
                            }
                        }

                        return $schema;
                    })
                    ->columns(2)
                    ->visible(fn($get) => $get('laporan_id')),
            ])
            ->statePath('data');
    }

    public function generateReport() {}


    public function printReport()
    {
        if (empty($this->data['laporan_id'])) {
            return abort(404);
        }
        if ($this->data['is_excel']) {
            // dd($this->data);
            $this->url = '';
            return response()->streamDownload(function () {
                echo Excel::raw(
                    new ($this->data['path_excel'])(
                        $this->data['PStartDate'],
                        $this->data['PEndDate']
                    ),
                    \Maatwebsite\Excel\Excel::XLSX
                );
            }, "{$this->data['laporan_name']}.xlsx");
        }
        $this->buildPdfUrl();
    }

    public function getTitle(): string
    {
        return 'Laporan lain-lain';
    }
    protected function buildPdfUrl(): void
    {
        $username = auth()->user()->name;
        $host = request()->getScheme() . '://' . request()->getHost();

        if ($port = config('app.report_port')) {
            $host .= ":{$port}";
        }

        $db = config('database.connections.mysql.database');
        $laporanId = $this->data['laporan_id'];
        $params = $this->laporanParams[$laporanId] ?? [];

        $paramNames = [];
        $paramTypes = [];
        $paramValues = [];

        foreach ($params as $key => $config) {
            $paramNames[] = $key;
            $paramTypes[] = $config['type'] === 'date' ? 'd' : 's';
            $paramValues[] = $this->data[$key] ?? '';
        }

        $this->url =
            "{$host}/rpt/?r={$this->data['path']}"
            . "&d={$db}"
            . "&p=" . implode('|', $paramNames) . '|POutletID'
            . "&t=" . implode('|', $paramTypes) . '|s'
            . "&v=" . implode('|', $paramValues) . '|' . $this->outletID
            . "&u={$username}"
            . "&f=pdf&tm=" . now()->format('YmdHis');
    }
}
