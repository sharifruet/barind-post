<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Server-side share card (GD), 1200×630: the Open Graph image for articles that have
 * no photo and no editor-made card (PublicSite::ogImage → /og/{id}.png).
 *
 * GD only shapes Bangla when libgd was built with libraqm, which PHP's bundled GD
 * (Docker, cPanel) is not: pre-base vowels land after their consonant and conjuncts
 * fall apart. So this renderer checks at runtime (canShapeBengali) and, without a
 * shaping engine, draws a brand card with no Bangla text at all rather than a
 * garbled headline. The professional cards are drawn in the browser instead
 * (public/assets/js/photo-card.js) and saved by admin → Photo Cards.
 */
class PhotoCard
{
    public const WIDTH  = 1200;
    public const HEIGHT = 630;

    private const INK    = [20, 20, 15];
    private const ACCENT = [200, 16, 46];

    /**
     * @param array  $news     id, title, category_name, published_at, image_url
     * @param string $template kept for callers of the old API; every card uses the one design
     *
     * @return string PNG bytes
     */
    public function render(array $news, string $template = 'default'): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('The GD extension is required to render share cards');
        }

        $w  = self::WIDTH;
        $h  = self::HEIGHT;
        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, true);
        $ink    = imagecolorallocate($im, ...self::INK);
        $accent = imagecolorallocate($im, ...self::ACCENT);
        $white  = imagecolorallocate($im, 255, 255, 255);
        $soft   = imagecolorallocate($im, 205, 205, 200);
        imagefill($im, 0, 0, $ink);

        $photo = $this->loadPhoto($news['image_url'] ?? null);
        if ($photo !== null) {
            $this->drawCover($im, $photo, $w, $h);
            imagedestroy($photo);
            $this->shade($im, $w, $h);
        }

        imagefilledrectangle($im, 0, 0, $w, 10, $accent);

        $sans   = self::fontPath('NotoSansBengali-Bold.ttf');
        $serif  = self::fontPath('NotoSerifBengali-Bold.ttf');
        $shaped = $sans !== null && self::canShapeBengali($sans);
        $site   = config('SiteInfo');
        $host   = self::host();
        $handle = '@' . (basename(rtrim((string) $site->facebook, '/')) ?: 'barindpost');
        $margin = 56;
        $logo   = $this->loadLogo();

        $ruleY = $h - $margin - 46;
        imagefilledrectangle($im, $margin, $ruleY, $w - $margin, $ruleY + 1, imagecolorallocatealpha($im, 255, 255, 255, 90));

        if ($sans !== null && $serif !== null && $shaped) {
            // Brand row: logo chip + wordmark, then the headline above the footer.
            $x    = $margin;
            $chip = 72;
            if ($logo !== null) {
                imagecopyresampled($im, $logo, $x, $margin, 0, 0, $chip, $chip, imagesx($logo), imagesy($logo));
                $x += $chip + 20;
            }
            imagettftext($im, 26, 0, $x, $margin + 48, $white, $sans, (string) $site->siteName);

            $bottom = $ruleY - 36;
            $fit    = $this->fit((string) ($news['title'] ?? ''), $serif, $w - 2 * $margin, 3, 54, 34);
            $lineH  = (int) round($fit['size'] * 1.42);
            $top    = $bottom - count($fit['lines']) * $lineH;
            $y      = $top + (int) round($fit['size'] * 0.9);
            foreach ($fit['lines'] as $line) {
                imagettftext($im, $fit['size'], 0, $margin, $y, $white, $serif, $line);
                $y += $lineH;
            }
            $kicker = trim((string) ($news['category_name'] ?? ''));
            if ($kicker !== '') {
                imagefilledrectangle($im, $margin, $top - 58, $margin + 56, $top - 52, $accent);
                imagettftext($im, 22, 0, $margin, $top - 18, $accent, $sans, $kicker);
            }
            $date  = bangla_date($news['published_at'] ?? null) ?? '';
            $right = $host;
        } else {
            // No shaping engine: a Bangla headline would come out garbled, so this is a brand
            // card — the logo centred with the site address beneath it, nothing in Bangla.
            $size = 180;
            $top  = intdiv($h, 2) - 140;
            if ($logo !== null) {
                imagecopyresampled($im, $logo, intdiv($w - $size, 2), $top, 0, 0, $size, $size, imagesx($logo), imagesy($logo));
            }
            imagefilledrectangle($im, intdiv($w - 72, 2), $top + $size + 28, intdiv($w + 72, 2), $top + $size + 34, $accent);
            if ($sans !== null) {
                $box = imagettfbbox(40, 0, $sans, $host);
                imagettftext($im, 40, 0, intdiv($w - abs($box[2] - $box[0]), 2), $top + $size + 96, $white, $sans, $host);
            }
            $date  = ! empty($news['published_at']) && ($ts = strtotime((string) $news['published_at'])) !== false ? date('d.m.Y', $ts) : '';
            $right = $handle;
        }
        if ($logo !== null) {
            imagedestroy($logo);
        }

        if ($sans !== null) {
            if ($date !== '') {
                imagettftext($im, 19, 0, $margin, $ruleY + 42, $soft, $sans, $date);
            }
            $box = imagettfbbox(19, 0, $sans, $right);
            imagettftext($im, 19, 0, $w - $margin - abs($box[2] - $box[0]), $ruleY + 42, $soft, $sans, $right);
        }

        ob_start();
        imagepng($im);
        $png = (string) ob_get_clean();
        imagedestroy($im);

        return $png;
    }

    /** Absolute path of a shipped font (public/assets/fonts), or null when it is missing. */
    public static function fontPath(string $file): ?string
    {
        $path = ROOTPATH . 'public/assets/fonts/' . $file;

        return is_file($path) ? $path : null;
    }

    /**
     * Does this GD shape Bangla? "কি" must draw the vowel sign ি before the ক; GD without
     * a shaping engine draws the ক first, so the leading ink columns of "কি" and "ক" match.
     */
    public static function canShapeBengali(?string $font = null): bool
    {
        static $memo = [];

        $font ??= self::fontPath('NotoSansBengali-Bold.ttf');
        if ($font === null || ! is_file($font) || ! function_exists('imagettftext')) {
            return false;
        }
        if (! array_key_exists($font, $memo)) {
            $memo[$font] = self::leadingInk('কি', $font) !== self::leadingInk('ক', $font);
        }

        return $memo[$font];
    }

    /** Dark-pixel count per column for the first 26 columns of the rendered text. */
    private static function leadingInk(string $text, string $font): string
    {
        $im = imagecreatetruecolor(160, 90);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
        imagettftext($im, 36, 0, 8, 62, imagecolorallocate($im, 0, 0, 0), $font, $text);
        $columns = '';
        for ($x = 0; $x < 26; $x++) {
            $dark = 0;
            for ($y = 0; $y < 90; $y++) {
                if ((imagecolorat($im, $x, $y) & 0xFF) < 128) {
                    $dark++;
                }
            }
            $columns .= chr(min(255, $dark));
        }
        imagedestroy($im);

        return $columns;
    }

    /** Largest size (max → min) at which the text wraps into at most $maxLines lines. */
    private function fit(string $text, string $font, int $maxWidth, int $maxLines, int $maxSize, int $minSize): array
    {
        $lines = [];
        for ($size = $maxSize; $size >= $minSize; $size -= 2) {
            [$lines, $overflow] = $this->wrap($text, $font, $size, $maxWidth, $maxLines);
            if (! $overflow) {
                return ['size' => $size, 'lines' => $lines];
            }
        }

        return ['size' => $minSize, 'lines' => $lines];
    }

    /** @return array{0: list<string>, 1: bool} lines and whether text was cut (last line then ends in …) */
    private function wrap(string $text, string $font, int $size, int $maxWidth, int $maxLines): array
    {
        $width = fn (string $s): int => ($b = imagettfbbox($size, 0, $font, $s)) ? abs($b[2] - $b[0]) : PHP_INT_MAX;
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $line  = '';
        $cut   = false;
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if ($line !== '' && $width($candidate) > $maxWidth) {
                $lines[] = $line;
                $line    = $word;
                if (count($lines) === $maxLines) {
                    $cut = true;
                    break;
                }
            } else {
                $line = $candidate;
            }
        }
        if (! $cut && $line !== '') {
            $lines[] = $line;
        }
        if ($cut) {
            $last = $lines[$maxLines - 1];
            while ($last !== '' && $width($last . '…') > $maxWidth) {
                $last = (string) preg_replace('/\s*\S+$/u', '', $last);
            }
            $lines[$maxLines - 1] = $last . '…';
        }

        return [$lines, $cut];
    }

    /** @return \GdImage|null */
    private function loadPhoto(?string $imageUrl)
    {
        if ($imageUrl === null || $imageUrl === '' || preg_match('~^https?://~i', $imageUrl)) {
            return null;
        }
        $path = ROOTPATH . ltrim($imageUrl, '/');
        if (! is_file($path)) {
            return null;
        }
        $data = @file_get_contents($path);
        $im   = $data === false ? false : @imagecreatefromstring($data);

        return $im === false ? null : $im;
    }

    /** @return \GdImage|null */
    private function loadLogo()
    {
        $path = ROOTPATH . 'public/logo.png';
        $im   = is_file($path) ? @imagecreatefrompng($path) : false;

        return $im === false ? null : $im;
    }

    /** Scale-to-cover the photo into the canvas, cropping around the centre. */
    private function drawCover($im, $photo, int $w, int $h): void
    {
        $pw    = imagesx($photo);
        $ph    = imagesy($photo);
        $scale = max($w / $pw, $h / $ph);
        $sw    = (int) round($w / $scale);
        $sh    = (int) round($h / $scale);
        imagecopyresampled($im, $photo, 0, 0, intdiv($pw - $sw, 2), intdiv($ph - $sh, 2), $w, $h, $sw, $sh);
    }

    /** Ink gradients over the top and lower parts so text stays readable on any photo. */
    private function shade($im, int $w, int $h): void
    {
        $line = function (int $y, float $opacity) use ($im, $w): void {   // opacity 0…1 → GD alpha 127…0
            imageline($im, 0, $y, $w, $y, imagecolorallocatealpha($im, self::INK[0], self::INK[1], self::INK[2], (int) round(127 * (1 - min(1, $opacity)))));
        };
        $topEnd = (int) ($h * 0.3);
        for ($y = 0; $y < $topEnd; $y++) {
            $line($y, 0.55 * (1 - $y / $topEnd));
        }
        for ($y = $topEnd; $y < $h; $y++) {
            $line($y, 1.15 * ($y - $topEnd) / ($h - $topEnd));
        }
    }

    private static function host(): string
    {
        $host = (string) parse_url(base_url(), PHP_URL_HOST);
        $host = (string) preg_replace('/^www\./', '', $host);

        return $host !== '' ? $host : 'barindpost.com';
    }
}
