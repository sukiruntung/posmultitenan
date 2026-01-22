<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardMasterData extends Component
{
    public string $title;
    public array $items;

    public function __construct(string $title, array $items = [])
    {
        $this->title = $title;
        $this->items = $items;
    }

    public function render(): View|Closure|string
    {
        return view('components.card-master-data');
    }
}
