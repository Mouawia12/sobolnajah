<?php

namespace App\Support;

use ArPHP\I18N\Arabic;

class PdfArabicText
{
    private ?Arabic $arabic;

    public function __construct(private readonly bool $enabled = true)
    {
        $this->arabic = $this->enabled && class_exists(Arabic::class)
            ? new Arabic()
            : null;
    }

    public function shape(mixed $value): string
    {
        $text = trim((string) $value);

        if ($text === '' || !$this->arabic) {
            return (string) $value;
        }

        return $this->arabic->utf8Glyphs($text, 500, false, true);
    }
}
