<?php

namespace App\Services;

use App\Models\RepairOrder;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    public function generateForRepairOrder(RepairOrder $order): string
    {
        $renderer = new ImageRenderer(new RendererStyle(240), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($order->order_number);
    }
}
