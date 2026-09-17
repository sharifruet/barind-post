<?php

if (! function_exists('excerpt_text')) {
    /**
     * Strip markup from a news field and trim it to a word count, for card/list
     * summaries. See story_excerpt() for the subtitle -> key points -> content
     * fallback order.
     */
    function excerpt_text(?string $text, int $words = 22): string
    {
        if (empty($text)) {
            return '';
        }

        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if ($text === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $text);

        if (count($parts) <= $words) {
            return $text;
        }

        return implode(' ', array_slice($parts, 0, $words)) . '…';
    }
}

if (! function_exists('key_points')) {
    /**
     * The article's key points, from `lead_text` (one point per line). Leading
     * bullet characters an editor may have typed are stripped. A single line
     * means the field holds a legacy one-sentence intro rather than key points —
     * the article view renders that as a paragraph, not a one-item list.
     */
    function key_points(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $points = [];
        foreach (preg_split('/\R+/u', $text) as $line) {
            $line = trim(preg_replace('/^[\s•·\-–—*]+/u', '', trim($line)));
            if ($line !== '') {
                $points[] = $line;
            }
        }

        return $points;
    }
}

if (! function_exists('story_excerpt')) {
    /**
     * Excerpt for a news row: the subtitle (standfirst) first, then the key
     * points joined into one line, then the body.
     */
    function story_excerpt(array $news, int $words = 22): string
    {
        if (! empty($news['subtitle'])) {
            $source = $news['subtitle'];
        } elseif (! empty($news['lead_text'])) {
            $source = implode(' · ', key_points($news['lead_text']));
        } else {
            $source = $news['content'] ?? '';
        }

        return excerpt_text($source, $words);
    }
}

if (! function_exists('seo_description')) {
    /**
     * Meta / Open Graph / Twitter description for an article: the subtitle,
     * else the key points as one sentence-ish line, else the title.
     */
    function seo_description(array $news, string $fallbackSuffix = ' - বারিন্দ পোস্ট'): string
    {
        if (! empty($news['subtitle'])) {
            return $news['subtitle'];
        }

        $points = key_points($news['lead_text'] ?? null);
        if ($points !== []) {
            return excerpt_text(implode('। ', $points), 40);
        }

        return ($news['title'] ?? '') . $fallbackSuffix;
    }
}

if (! function_exists('bn_number')) {
    /**
     * Render digits in Bengali numerals.
     */
    function bn_number($value): string
    {
        return strtr((string) $value, [
            '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
            '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
        ]);
    }
}

if (! function_exists('reading_time')) {
    /**
     * Approximate reading time in Bengali, derived from the stored word_count
     * (or the body copy when that column is empty). ~180 wpm is a reasonable
     * pace for Bangla prose.
     */
    function reading_time(array $news): string
    {
        $words = (int) ($news['word_count'] ?? 0);

        if ($words <= 0 && ! empty($news['content'])) {
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($news['content'])));
            $words = $plain === '' ? 0 : count(preg_split('/\s+/u', $plain));
        }

        if ($words <= 0) {
            return '';
        }

        $minutes = max(1, (int) round($words / 180));

        return bn_number($minutes) . ' মিনিট পড়া';
    }
}

if (! function_exists('limitTo15Words')) {
    /**
     * Kept for older view code that still calls it directly.
     */
    function limitTo15Words($text)
    {
        return excerpt_text($text, 15);
    }
}

if (! function_exists('render_article_body')) {
    /**
     * Prepare stored article HTML for the public page.
     *
     * The editor's "link a story" feature stores a related-story block as a
     * plain blockquote — the classic CKEditor build strips any custom element or
     * class the next time the article is edited, so that is the only shape that
     * survives a round trip. Here that exact shape becomes a styled callout;
     * ordinary blockquotes are left untouched.
     */
    function render_article_body(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // Colon may sit inside or outside <strong>; CKEditor may emit &nbsp;.
        $pattern = '~<blockquote>\s*<p>\s*<strong>\s*আরও\s*পড়ুন\s*[:：]?\s*</strong>\s*[:：]?(?:\s|&nbsp;)*'
                 . '<a\s+href="([^"]+)"[^>]*>(.*?)</a>\s*</p>\s*</blockquote>~su';

        return preg_replace_callback($pattern, static function (array $m): string {
            // Both captures are already entity-encoded HTML from the stored body
            // (which is output raw): re-escape without double-encoding, and only
            // strip stray tags from the title.
            $href  = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8', false);
            $title = strip_tags($m[2]);

            return '<aside class="related-inline">'
                . '<span class="related-inline__label"><i class="fas fa-newspaper"></i> আরও পড়ুন</span>'
                . '<a class="related-inline__link" href="' . $href . '">' . $title . '</a>'
                . '</aside>';
        }, $html);
    }
}
