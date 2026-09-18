<?php

namespace App\Libraries;

/**
 * Cheap headline similarity for cross-source duplicate detection.
 *
 * Two outlets covering the same event share the entity words of the headline
 * ("ডেঙ্গুতে", "৫", "জনের", "মৃত্যু") even when the phrasing differs, so the score
 * is word-overlap after dropping punctuation, function words and case, with
 * Bengali digits normalised. It is deliberately simple: it runs at intake on a
 * few hundred recent titles and only *flags* a draft for the editor.
 */
final class TitleSimilarity
{
    /** Flag as a possible duplicate at or above this score. */
    public const THRESHOLD = 0.6;

    /** Minimum shared content words before a score counts at all. */
    private const MIN_SHARED = 3;

    private const STOPWORDS = [
        'ও', 'এ', 'এর', 'এই', 'সে', 'তার', 'তাদের', 'যে', 'কি', 'না', 'করে', 'করা', 'হয়', 'হয়েছে', 'হবে', 'হচ্ছে',
        'নিয়ে', 'জন্য', 'থেকে', 'মধ্যে', 'সাথে', 'সঙ্গে', 'পর', 'আর', 'আরও', 'আরো', 'নতুন', 'দিয়ে', 'কাছে', 'উপর', 'ওপর',
        'দিকে', 'মাধ্যমে', 'বলে', 'বলেন', 'বললেন', 'জানান', 'জানিয়েছে', 'জানিয়েছেন', 'যা', 'এক', 'একটি', 'কোনো', 'সব',
        'the', 'a', 'an', 'of', 'in', 'on', 'to', 'for', 'and', 'with', 'at', 'by', 'is', 'are', 'was', 'were', 'as', 'from',
    ];

    /**
     * Content words of a headline, lower-cased, digits normalised, de-duplicated.
     *
     * @return list<string>
     */
    public static function tokens(string $title): array
    {
        $t = mb_strtolower($title, 'UTF-8');
        $t = strtr($t, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        $t = preg_replace('/[\p{P}\p{S}]+/u', ' ', $t) ?? '';

        $words = [];
        foreach (preg_split('/\s+/u', trim($t)) ?: [] as $w) {
            // Numbers of any length stay: "৫ জনের মৃত্যু" vs "৩ জনের মৃত্যু" are different stories.
            if ($w === '' || (mb_strlen($w, 'UTF-8') < 2 && ! ctype_digit($w)) || in_array($w, self::STOPWORDS, true)) {
                continue;
            }
            $words[$w] = true;
        }

        return array_map('strval', array_keys($words)); // numeric-string keys come back as ints otherwise
    }

    /**
     * 0.0–1.0: the larger of containment (shared / shorter title) and Jaccard.
     */
    public static function score(string $a, string $b): float
    {
        $ta = self::tokens($a);
        $tb = self::tokens($b);
        if (count($ta) < self::MIN_SHARED || count($tb) < self::MIN_SHARED) {
            return 0.0;
        }

        $shared = count(array_intersect($ta, $tb));
        if ($shared < self::MIN_SHARED) {
            return 0.0;
        }

        $containment = $shared / min(count($ta), count($tb));
        $jaccard     = $shared / count(array_unique(array_merge($ta, $tb)));

        return round(max($containment, $jaccard), 3);
    }

    /**
     * How much of a headline is actually present in its own article, 0.0–1.0.
     *
     * A model can write a correct article and then top it with a headline about something
     * else entirely. That happened on the live site: an article about a CID fraud arrest was
     * published as "মহাবিদ্যালয় সভাপতি আদনানের হাত থেকে বান্ধবী উদ্ধার", which shares not one
     * word with its own body. Across 40 real articles the lowest genuine value was 0.50;
     * the fabricated headline scored 0.00, so GROUNDED_MIN sits between the two.
     *
     * Deliberately not score(): that requires 3 shared words before it reports anything,
     * and short legitimate headlines share only 2.
     */
    public static function groundedness(string $headline, string $body): float
    {
        $h = self::tokens($headline);
        if ($h === []) {
            return 1.0;   // nothing to check
        }

        $shared = count(array_intersect($h, self::tokens($body)));

        return round($shared / count($h), 3);
    }

    /** Below this, a headline is not about the article underneath it. */
    public const GROUNDED_MIN = 0.34;

    public static function isDuplicate(float $score): bool
    {
        return $score >= self::THRESHOLD;
    }
}
