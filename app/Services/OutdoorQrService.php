<?php

namespace App\Services;

use App\Models\OohInventory;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class OutdoorQrService
{
    public function verifyUrl(OohInventory $inventory): string
    {
        return route('outdoor.verify', $inventory->qr_token);
    }

    public function svg(OohInventory $inventory): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(280),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);

        return $writer->writeString($this->verifyUrl($inventory));
    }
}
