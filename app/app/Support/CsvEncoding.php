<?php

namespace App\Support;

class CsvEncoding
{
    /** Caracteres de MacRoman para los bytes 0x80-0xFF, en orden. Sin depender de iconv del servidor. */
    private const MAC_ROMAN_HIGH = 'ÄÅÇÉÑÖÜáàâäãåçéèêëíìîïñóòôöõúùûü†°¢£§•¶ß®©™´¨≠ÆØ∞±≤≥¥µ∂∑∏π∫ªºΩæø¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷◊ÿŸ⁄€‹›ﬁﬂ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ˜¯˘˙˚¸˝˛ˇ';

    /**
     * Excel guarda "CSV" en ANSI: Windows-1252 en Windows, MacRoman en Mac. Los bytes 0x80-0x9F son
     * letras acentuadas en MacRoman (ó = 0x97) pero signos raros en Windows-1252, así que sirven para
     * distinguirlos. Lo que ya es UTF-8 válido no se toca.
     */
    public static function toUtf8(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        if (preg_match('/[\x80-\x9F]/', $value)) {
            return strtr($value, self::macRomanMap());
        }

        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }

    /** @return array<string, string> */
    private static function macRomanMap(): array
    {
        static $map = null;

        if ($map === null) {
            $map = [];
            foreach (mb_str_split(self::MAC_ROMAN_HIGH) as $i => $char) {
                $map[chr(0x80 + $i)] = $char;
            }
        }

        return $map;
    }
}
