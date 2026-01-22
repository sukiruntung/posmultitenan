<?php

namespace App\Livewire;

use Closure;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CardMasterData extends Component
{
    public string $title;
    public array $items = [];

    public function mount(string $title, array $items = [])
    {
        $this->title = $title;
        $this->items = $items;
    }

    public function itemClicked($item)
    {
        return redirect()->to('/admin/' . $item);
    }
    public function render(): View|Closure|string
    {
        return view('livewire.card-master-data');
    }
}
