<?php

namespace App\Support;

/**
 * Near-duplicate detection between two pieces of prose, using Jaccard
 * similarity over word shingles (overlapping n-word sequences).
 *
 * Shingles rather than plain word overlap because word-frequency comparison
 * scores any two articles on the same topic as highly similar — shared
 * *sequences* are a much stronger signal that text was actually copied or
 * lightly reworded, which is what Google's duplicate-content policy is about.
 */
class ContentSimilarity
{
    /** Sequences of this many consecutive words form one shingle. */
    protected const SHINGLE_SIZE = 5;

    /**
     * Ratio of shared shingles, 0.0 (nothing in common) to 1.0 (identical).
     */
    public static function score(string $a, string $b): float
    {
        $shinglesA = static::shingles($a);
        $shinglesB = static::shingles($b);

        if (empty($shinglesA) || empty($shinglesB)) {
            return 0.0;
        }

        $intersection = count(array_intersect_key($shinglesA, $shinglesB));
        $union = count($shinglesA + $shinglesB);

        return $union > 0 ? $intersection / $union : 0.0;
    }

    /**
     * @return array<string, true> shingle hash => true, for cheap key lookups
     */
    protected static function shingles(string $text): array
    {
        $words = static::words($text);

        if (count($words) < self::SHINGLE_SIZE) {
            // Too short to shingle — fall back to the whole thing as one unit.
            return $words ? [implode(' ', $words) => true] : [];
        }

        $shingles = [];
        $limit = count($words) - self::SHINGLE_SIZE;

        for ($i = 0; $i <= $limit; $i++) {
            $shingles[implode(' ', array_slice($words, $i, self::SHINGLE_SIZE))] = true;
        }

        return $shingles;
    }

    /** @return string[] */
    protected static function words(string $text): array
    {
        $text = strip_tags(str_replace(['<', '>'], [' <', '> '], $text));
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);

        return array_values(array_filter(preg_split('/\s+/', trim($text)) ?: []));
    }
}
