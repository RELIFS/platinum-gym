<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class OwnerLayout extends Component
{
    /**
     * @param  array<string, mixed>  $portal
     * @param  array<int, array<string, mixed>>  $navigation
     */
    public function __construct(
        public array $portal = [],
        public array $navigation = [],
        public string $title = 'Owner',
    ) {}

    public function render(): View
    {
        return view('layouts.owner');
    }
}
