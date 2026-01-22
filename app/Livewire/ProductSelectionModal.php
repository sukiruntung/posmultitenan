<?php

namespace App\Livewire;

use App\Models\Products\Product;
use App\Models\Mitra\SupplierProduct;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ProductSelectionModal extends Component
{
    use WithPagination;
    protected $listeners = [
        'syncProducts' => 'updateProducts',
        'showValidationError' => 'showError'
    ];
    public $products = [];
    public $searchType = 'product_name';
    public $searchTerm = '';
    public $supplierId;
    protected $paginationTheme = 'tailwind';


    public $errorMessage = '';

    public $search = '';
    public $perPage = 10;
    public $showModal = false;
    public function mount($products = [], $supplierId = null)
    {
        $this->products = session('list_products', []);
        $this->supplierId = $supplierId;
    }

    public function boot()
    {
        $this->products = session('list_products', []);
    }
    public function getFilteredBarangProperty()
    {
        $query = Product::with(['satuan', 'merk'])
            ->leftJoin('supplier_products', function ($join) {
                $join->on('products.id', '=', 'supplier_products.product_id')
                    ->where('supplier_products.supplier_id', $this->supplierId);
            })
            ->select('products.*', DB::raw('supplier_products.frekuensi as supplier_frequency'))
            ->where('outlet_id', Auth::user()->userOutlet->outlet_id)
            ->orderByDesc('supplier_frequency')
            ->limit(20);

        if ($this->searchTerm) {
            if ($this->searchType === 'product_catalog') {
                $query->where('product_catalog', 'like', "%{$this->searchTerm}%");
            } else {
                $query->where('product_name', 'like', "%{$this->searchTerm}%");
            }
        } else if ($this->supplierId) {
            $supplierProductIds = SupplierProduct::where('supplier_id', $this->supplierId)
                ->pluck('product_id');

            if ($supplierProductIds->isNotEmpty()) {
                $query->whereIn('products.id', $supplierProductIds);
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
        return view('livewire.product-selection-modal');
    }
}
