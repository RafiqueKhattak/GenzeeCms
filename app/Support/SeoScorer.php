<?php

namespace App\Support;

/**
 * Cheap, on-the-fly SEO completeness score (0-100) for an admin index row —
 * metadata presence/length only, no duplicate-content or AdSense-policy
 * checks (those live in App\Services\PolicyChecker and run per-request in
 * the post editor, not across a whole paginated list). Not persisted.
 */
class SeoScorer
{
    public static function score(
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $image,
        ?string $content,
        bool $requireImage = true,
    ): int {
        $score = 100;

        if (empty($metaTitle)) {
            $score -= 15;
        } elseif (mb_strlen($metaTitle) > 70) {
            $score -= 5;
        }

        if (empty($metaDescription)) {
            $score -= 25;
        } elseif (mb_strlen($metaDescription) < 50 || mb_strlen($metaDescription) > 160) {
            $score -= 10;
        }

        if ($requireImage && empty($image)) {
            $score -= 15;
        }

        $words = $content ? str_word_count(strip_tags($content)) : 0;
        if ($words < 150) {
            $score -= 20;
        } elseif ($words < 300) {
            $score -= 5;
        }

        return max(0, min(100, $score));
    }
}
