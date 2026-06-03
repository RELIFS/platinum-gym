<?php

namespace App\View\Components;

use App\Features\PublicWebsite\ViewModels\PublicChatbotViewModel;
use App\Features\PublicWebsite\ViewModels\PublicLayoutViewModel;
use Illuminate\View\Component;
use Illuminate\View\View;

class PublicLayout extends Component
{
    public array $layoutMeta;

    public array $chatbotConfig;

    public function __construct(
        public array $settings = [],
        public string $title = 'Platinum Gym Padang',
        public string $description = 'Platinum Gym Padang adalah pusat kebugaran premium di Padang untuk gym, senam, personal trainer, Muaythai, Poundfit, dan produk fitness.',
    ) {
        $this->layoutMeta = PublicLayoutViewModel::make($settings, $title, $description);
        $this->chatbotConfig = PublicChatbotViewModel::make($settings);
    }

    public function render(): View
    {
        return view('layouts.public');
    }
}
