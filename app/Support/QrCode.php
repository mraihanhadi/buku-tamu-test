<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Illuminate\Support\HtmlString;

class QrCode
{
    /**
     * Quiet zone kept around the code, in pixels. Subtracted from the requested
     * size so the rendered image is exactly `$size` x `$size` either way.
     */
    private const MARGIN = 8;

    /**
     * Render the given text as an inline SVG QR code.
     *
     * SVG needs no image extension, and the XML declaration is dropped so the
     * result embeds directly into a Blade view via {!! ... !!}.
     */
    public static function svg(string $text, int $size = 280): HtmlString
    {
        return new HtmlString(self::render($text, $size, new SvgWriter, [
            SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
        ]));
    }

    /**
     * Render the given text as raw PNG binary.
     *
     * Uses endroid's GD-backed PNG writer, so it requires the gd extension.
     * Handy for a downloadable raster that pastes into documents and chat apps.
     */
    public static function png(string $text, int $size = 512): string
    {
        return self::render($text, $size, new PngWriter);
    }

    /**
     * Build a QR code for the given writer and return its binary/text output.
     *
     * @param  array<string, mixed>  $writerOptions
     */
    private static function render(string $text, int $size, WriterInterface $writer, array $writerOptions = []): string
    {
        return (new Builder(
            writer: $writer,
            writerOptions: $writerOptions,
            data: $text,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: max(1, $size - (2 * self::MARGIN)),
            margin: self::MARGIN,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build()->getString();
    }
}
