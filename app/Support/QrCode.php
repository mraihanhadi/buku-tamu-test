<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\HtmlString;

class QrCode
{
    /**
     * Render the given text as an inline SVG QR code.
     *
     * Uses the SVG backend so no gd/imagick extension is required, and the
     * result embeds directly into a Blade view via {!! ... !!}.
     */
    public static function svg(string $text, int $size = 280): HtmlString
    {
        $writer = new Writer(
            new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd)
        );

        return new HtmlString($writer->writeString($text));
    }

    /**
     * Render the given text as raw PNG binary.
     *
     * Uses the Imagick backend, so it requires the imagick extension. Handy
     * for a downloadable raster that pastes into documents and chat apps.
     */
    public static function png(string $text, int $size = 512): string
    {
        $writer = new Writer(
            new ImageRenderer(new RendererStyle($size, 1), new ImagickImageBackEnd)
        );

        return $writer->writeString($text);
    }
}
