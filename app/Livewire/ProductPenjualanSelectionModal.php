<?php

namespace App\Livewire;

use App\Models\Mitra\CustomerProduct;
use App\Models\Products\ProductStock;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ProductPenjualanSelectionModal extends Component
{
    use WithPagination;
    protected $listeners = [
        'syncProducts' => 'updateProducts',
        'showValidationError' => 'showError'
    ];
    public $products = [];
    public $searchType = 'product_name';
    public $searchTerm = '';
    public $customerId;
    protected $paginationTheme = 'tailwind';


    public $errorMessage = '';

    public $search = '';
    public $perPage = 10;
    public $showModal = false;
    public function mount($products = [], $customerID = null)
    {
        $this->products = session('list_productsPenjualan', []);
        $this->customerId = $customerID;
    }

    public function boot()
    {
        $this->products = session('list_productsPenjualan', []);
    }
    public function getFilteredBarangProperty()
    {
        $query = ProductStock::query()
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->leftJoin('satuans', 'satuans.id', '=', 'products.satuan_id')
            ->leftJoin('merks', 'merks.id', '=', 'products.merk_id')
            ->leftJoin('customer_products', function ($join) {
                $join->on('products.id', '=', 'customer_products.product_id')
                    ->where('customer_products.customer_id', $this->customerId);
            })
            ->select(
                'product_stocks.*',
                'products.product_name',
                'satuans.satuan_name',
                'merks.merk_name',
                DB::raw('customer_products.frekuensi as customer_frequency')
            )
            ->where('stock', '>', 0)
            ->where('products.outlet_id', Auth::user()->userOutlet->outlet_id)
            ->orderByDesc('customer_frequency')
            ->orderBy('products.product_name', 'asc')
            ->limit(20);
        if ($this->searchTerm) {
            if ($this->searchType === 'product_catalog') {
                $query->where('products.product_catalog', 'like', "%{$this->searchTerm}%");
            } else {
                $query->where('products.product_name', 'like', "%{$this->searchTerm}%");
            }
        } else if ($this->customerId) {
            $customerProductIds = CustomerProduct::where('customer_id', $this->customerId)
                ->pluck('product_id');

            if ($customerProductIds->isNotEmpty()) {
                $query->whereIn('product_stocks.product_id', $customerProductIds);
            }
        }
        $currentPage = Paginator::resolveCurrentPage() ?: ($this->page ?? 1);

        $total = $query->count();
        $itemsForCurrentPage = $query->forPage($currentPage, $this->perPage)->get();

        return new LengthAwarePaginator(
            $itemsForCurrentPage,
            $total,
            $this->perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    public function addProduct($productId)
    {
        $this->dispatch('checkInvoiceNumber', productId: $productId);
    }


    public function removeProductModal($productId)
    {
        $this->dispatch('removeProductModal', productId: $productId);
    }
    public function updateProducts($products)
    {
        $this->products = $products;
        $this->dispatch('$refresh'); // refres UI modal
    }

    public function showError($message)
    {
        $this->errorMessage = $message;
    }

    public function noop() {}

    public function render()
    {
        return view('livewire.product-penjualan-selection-modal');
    }
}
