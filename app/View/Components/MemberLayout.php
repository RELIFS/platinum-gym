<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class MemberLayout extends Component
{
    /**
     * @param  array<string, mixed>  $portal
     */
    public function __construct(
        public array $portal = [],
        public string $title = 'Member Portal',
    ) {}

    public function render(): View
    {
        return view('layouts.member');
    }
}
