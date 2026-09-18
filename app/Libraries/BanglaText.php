<?php

namespace App\Libraries;

/**
 * Is this text actually Bangla?
 *
 * The pipeline rewrites foreign wires (Al Jazeera, Dawn, BSS, The Daily Star) into Bangla,
 * and the model sometimes leaves a clause untranslated — real examples caught in testing:
 * "dozens of student dressed in black shown up", the Dutch "geweld" and "aldus", and bare
 * place names like Bhutan/Macau. A prompt reduces that; it cannot guarantee it. This is the
 * measurement the publish gate uses so such an article is filed as a draft instead of going
 * live under the masthead.
 *
 * Note on the danda: "।" is U+0964, which lives in the *Devanagari* Unicode block but is the
 * normal Bangla sentence terminator. Counting it as foreign script marks every correct
 * Bangla article as broken, so it is explicitly excluded.
 */
final class BanglaText
{
    /** Latin words of this many letters or more count as untranslated text. */
    private const MIN_LATIN_WORD = 2;

    /** Allow up to this many Latin words before an article is held back. */
    public const MAX_LATIN_WORDS = 2;

    /** Foreign scripts that should never appear in Bangla copy (danda handled separately). */
    private const FOREIGN_RANGES = [
        'Devanagari' => [[0x0900, 0x0963], [0x0966, 0x097F]],   // skips ।(0964) and ॥(0965)
        'Gurmukhi'   => [[0x0A00, 0x0A7F]],
        'Gujarati'   => [[0x0A80, 0x0AFF]],
        'Odia'       => [[0x0B00, 0x0B7F]],
        'Tamil'      => [[0x0B80, 0x0BFF]],
        'Telugu'     => [[0x0C00, 0x0C7F]],
        'Kannada'    => [[0x0C80, 0x0CFF]],
        'Malayalam'  => [[0x0D00, 0x0D7F]],
        'Arabic'     => [[0x0600, 0x06FF]],
        'CJK'        => [[0x4E00, 0x9FFF]],
    ];

    /**
     * @return array{latin_words:list<string>,foreign:array<string,int>,latin_digits:int}
     */
    public static function inspect(?string $text): array
    {
        $text = (string) $text;

        preg_match_all('/[A-Za-z][A-Za-z\'’]{' . (self::MIN_LATIN_WORD - 1) . ',}/u', $text, $m);
        $latinWords = $m[0] ?? [];

        $foreign = [];
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            $cp = mb_ord($ch, 'UTF-8');
            if ($cp === false) {
                continue;
            }
            foreach (self::FOREIGN_RANGES as $name => $ranges) {
                foreach ($ranges as [$lo, $hi]) {
                    if ($cp >= $lo && $cp <= $hi) {
                        $foreign[$name] = ($foreign[$name] ?? 0) + 1;
                        continue 3;
                    }
                }
            }
        }

        return [
            'latin_words'  => $latinWords,
            'foreign'      => $foreign,
            'latin_digits' => preg_match_all('/[0-9]/', $text),
        ];
    }

    /**
     * Human-readable reasons this text is not publishable Bangla. Empty = fine.
     *
     * @return list<string>
     */
    public static function problems(?string $text): array
    {
        $r        = self::inspect($text);
        $problems = [];

        if ($r['foreign'] !== []) {
            $parts = [];
            foreach ($r['foreign'] as $script => $n) {
                $parts[] = "{$script}×{$n}";
            }
            $problems[] = 'contains non-Bangla script (' . implode(', ', $parts) . ')';
        }

        if (count($r['latin_words']) > self::MAX_LATIN_WORDS) {
            $sample = array_slice(array_unique($r['latin_words']), 0, 5);
            $problems[] = sprintf('%d untranslated Latin words (%s…)', count($r['latin_words']), implode(', ', $sample));
        }

        return $problems;
    }

    /**
     * Repair text the model escaped wrongly: a literal backslash-n instead of a newline.
     * Seen in real output — it reaches the page as the characters "\n" in the middle of a
     * sentence, and splits words when the paragraph is re-joined.
     */
    public static function fixEscapedNewlines(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        return preg_replace('/\\\\r\\\\n|\\\\n|\\\\r/', "\n", $text);
    }
}
