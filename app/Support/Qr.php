<?php

namespace App\Support;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

final class Qr
{
    /** Returns an inline SVG QR code for the given text. */
    public static function svg(string $text, int $size = 180): string
    {
        $qr = new QrCode(data: $text, size: $size, margin: 0);

        return (new SvgWriter)
            ->write($qr, null, null, [SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true])
            ->getString();
    }
}
