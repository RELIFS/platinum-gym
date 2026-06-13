<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * @param  array<string, mixed>  $portal
     * @param  array<int, array<string, mixed>>  $navigation
     */
    public function __construct(
        public array $portal = [],
        public array $navigation = [],
        public string $title = 'Admin Area',
    ) {}

    public function render(): View
    {
        return view('layouts.admin');
    }
}
