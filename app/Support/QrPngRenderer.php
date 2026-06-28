<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Throwable;

class QrPngRenderer
{
    public function render(string $payload, int $size = 640, int $margin = 4): string
    {
        if (! extension_loaded('gd')) {
            return '';
        }

        try {
            $matrix = Encoder::encode($payload, ErrorCorrectionLevel::M())->getMatrix();
            $moduleCount = $matrix->getWidth();
            $totalModules = $moduleCount + ($margin * 2);
            $moduleSize = max(1, intdiv($size, $totalModules));
            $qrSize = $moduleSize * $moduleCount;
            $actualSize = $moduleSize * $totalModules;
            $offset = intdiv($actualSize - $qrSize, 2);

            $image = imagecreatetruecolor($actualSize, $actualSize);

            if ($image === false) {
                return '';
            }

            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);

            imagefill($image, 0, 0, $white);

            for ($y = 0; $y < $moduleCount; $y++) {
                for ($x = 0; $x < $moduleCount; $x++) {
                    if ($matrix->get($x, $y) !== 1) {
                        continue;
                    }

                    imagefilledrectangle(
                        $image,
                        $offset + ($x * $moduleSize),
                        $offset + ($y * $moduleSize),
                        $offset + (($x + 1) * $moduleSize) - 1,
                        $offset + (($y + 1) * $moduleSize) - 1,
                        $black,
                    );
                }
            }

            ob_start();
            imagepng($image, null, 0);
            $png = (string) ob_get_clean();
            imagedestroy($image);

            return $png;
        } catch (Throwable) {
            return '';
        }
    }
}
