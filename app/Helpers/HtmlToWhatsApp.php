<?php

namespace App\Helpers;

class HtmlToWhatsApp
{
    public static function convert(string $html): string
    {
        // Decode entity HTML (&nbsp;, &amp;, dll)
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

// Normalisasi: hapus spasi di dalam tag format
$text = preg_replace_callback(
    '/<(b|strong|i|em|s|del|u)>(.*?)<\/\1>/is',
    fn($m) => "<{$m[1]}>" . trim($m[2]) . "</{$m[1]}>",
    $text
);

        // Izinkan tag tertentu supaya bisa di-mapping
        $text = strip_tags($text, '<b><strong><i><em><s><del><code><br><div><p><u>');

        // Mapping tag ke format WA
        $replace = [
            '<b>'   => '*', '</b>'  => '*',
            '<strong>' => '*', '</strong>' => '*',
            '<i>'   => '_', '</i>'  => '_',
            '<em>'  => '_', '</em>' => '_',
            '<s>'   => '~', '</s>'  => '~',
            '<del>' => '~', '</del>' => '~',
            '<u>'   => '_', '</u>'  => '_', // fallback underline -> italic
            '<code>' => '```', '</code>' => '```',
            '<br>'  => "\n", '<br/>' => "\n",
            '<div>' => "\n", '</div>' => "",
            '<p>'   => "\n\n", '</p>'  => "",
        ];

        $text = strtr($text, $replace);

        // Bersihkan sisa tag
        $text = strip_tags($text);

        // Rapikan baris kosong berlebih
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }
}
