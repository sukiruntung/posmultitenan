<?php

namespace App\Traits;

trait TransaksiHelper
{
    public $products = [];

    public function removeProduct($index): void
    {
        // dd($this->products[$index]);
        unset($this->products[$index]);
        $this->products = array_values($this->products); // reset index

        $this->updateTotal();
        $sessionProducts = session()->get('list_products', []);
        unset($sessionProducts[$index]);
        $sessionProducts = array_values($sessionProducts);

        session()->put('list_products', $sessionProducts);
    }

    public function updateSubtotal($index): void
    {
        $state = $this->form->getState();

        if (!isset($this->products[$index])) return;
        $qty    = (float) $this->products[$index]['qty'];
        $harga  = (float) $this->products[$index]['harga'];
        $disc   = (float) $this->products[$index]['disc'] ?? 0;
        $type   = $this->products[$index]['disc_type'] ?? 'percent';

        $subtotal = $qty * $harga;

        if ($type === 'percent') {
            $subtotal -= ($subtotal * $disc / 100);
        } else {
            $subtotal -= $disc;
        }

        $this->products[$index]['subtotal'] = max($subtotal, 0);

        $this->updateTotal();
    }
    public function updateHargaMode($index)
    {
        $mode = $this->products[$index]['harga_mode'] ?? null;

        if ($mode !== 'custom') {
            // kalau pilih angka (misalnya 1500, 2000, dst)
            $this->products[$index]['harga_default'] = (float) $mode;
            $this->updateSubTotalPenjualan($index);
        }
        // kalau custom, jangan diapa-apain, biarkan user input sendiri
    }
    public function updateSubTotalPenjualan($index): void
    {
        if (!isset($this->products[$index])) return;

        $qty = (float) $this->products[$index]['qty'];
        $productStockId = $this->products[$index]['id'];

        // Cek stock dari database
        $productStock = \App\Models\Products\ProductStock::find($productStockId);
        if ($productStock && $qty > $productStock->stock) {
            $this->products[$index]['errorQty'] = true;
            $this->products[$index]['qty'] = $productStock->stock;
            $qty = $productStock->stock;
        } else {
            $this->products[$index]['errorQty'] = false;
        }

        $harga  = (float) $this->products[$index]['harga_default'];
        $disc   = (float) $this->products[$index]['disc'] ?? 0;
        $type   = $this->products[$index]['disc_type'] ?? 'percent';

        $subtotal = $qty * $harga;

        if ($type === 'percent') {
            $subtotal -= ($subtotal * $disc / 100);
        } else {
            $subtotal -= $disc;
        }

        $this->products[$index]['subtotal'] = max($subtotal, 0);

        $this->updateTotal();
    }

    protected function updateTotal(): void
    {
        $state = $this->form->getState();
        $total = array_sum(array_column($this->products, 'subtotal'));
        // echo $total;
        $grandtotal = $total;
        $key_discount = $state['transaction_type'] . '_discount';
        $key_discounttype = $state['transaction_type'] . '_discounttype';
        $key_ppn = $state['transaction_type'] . '_ppn';
        $key_ongkir = $state['transaction_type'] . '_ongkir';
        $key_total = $state['transaction_type'] . '_total';
        $key_grandtotal = $state['transaction_type'] . '_grandtotal';
        $discount       = (float) ($state[$key_discount] ?? 0);
        $discounttype   = $state[$key_discounttype] ?? 'percent';
        $ppn   = $state[$key_ppn] ?? 0;
        $ongkir   = $state[$key_ongkir] ?? 0;
        if ($discount > 0) {
            if ($discounttype === 'percent') {
                $grandtotal -= ($total * $discount / 100);
            } else {
                $grandtotal -= $discount;
            }
        }

        if ($ppn > 0) {
            $grandtotal += ($grandtotal * $ppn / 100);
        }

        if ($ongkir > 0) {
            $grandtotal += $ongkir;
        }

        $this->form->fill([
            ...$this->form->getState(),
            $key_total     => $total,
            $key_grandtotal => max(round($grandtotal), 0),
        ]);
    }
}
