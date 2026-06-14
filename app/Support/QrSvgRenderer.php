<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Throwable;

class QrSvgRenderer
{
    public function render(string $payload, int $size = 220): string
    {
        try {
            $renderer = new ImageRenderer(new RendererStyle($size), new SvgImageBackEnd);

            return (new Writer($renderer))->writeString($payload);
        } catch (Throwable) {
            return '';
        }
    }
}
