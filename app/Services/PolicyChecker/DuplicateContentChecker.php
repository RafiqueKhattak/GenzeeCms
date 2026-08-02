<?php

namespace App\Services\PolicyChecker;

use App\Models\Post;
use App\Support\ContentSimilarity;

/**
 * Checks a draft against already-published posts for near-duplicate content.
 *
 * Kept separate from ContentPolicyCheckerInterface implementations on purpose:
 * that interface is deliberately free of Eloquent so a future LLM-backed
 * checker only needs to depend on the PolicyCheckRequest DTO. This class is
 * the part that does need database access, and PolicyCheckController merges
 * its finding into the result.
 */
class DuplicateContentChecker
{
    /** At or above this similarity, treat as a duplicate (hard fail). */
    protected const FAIL_THRESHOLD = 0.45;

    /** At or above this, warn — likely heavy overlap worth reviewing. */
    protected const WARN_THRESHOLD = 0.22;

    /** Cap how many posts we compare against, newest first, to bound cost. */
    protected const MAX_COMPARISONS = 300;

    public function check(string $title, string $bodyHtml, ?int $ignorePostId = null): PolicyFinding
    {
        $candidates = Post::query()
            ->when($ignorePostId, fn ($q) => $q->where('id', '!=', $ignorePostId))
            ->orderByDesc('updated_at')
            ->limit(self::MAX_COMPARISONS)
            ->get(['id', 'type', 'slug', 'title', 'body']);

        $best = null;
        $bestScore = 0.0;

        foreach ($candidates as $candidate) {
            $score = ContentSimilarity::score($bodyHtml, $candidate->body ?? '');

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        $percent = round($bestScore * 100);

        if ($best && $bestScore >= self::FAIL_THRESHOLD) {
            return new PolicyFinding(
                'duplicate_content',
                'Duplicate content',
                'fail',
                "This post is about {$percent}% identical to \"{$best->title}\" (/{$best->type}/{$best->slug}/). Google's policies require original content — near-duplicate pages can be excluded from the index and count against an AdSense review.",
                'Rewrite the overlapping sections in your own words, or merge the two posts into one and redirect the old URL.'
            );
        }

        if ($best && $bestScore >= self::WARN_THRESHOLD) {
            return new PolicyFinding(
                'duplicate_content',
                'Duplicate content',
                'warn',
                "This post shares about {$percent}% of its phrasing with \"{$best->title}\" (/{$best->type}/{$best->slug}/). That is not necessarily a problem for closely-related topics, but worth a look.",
                'Check the overlapping sections read as genuinely distinct, and consider linking the two posts instead of repeating material.'
            );
        }

        return new PolicyFinding(
            'duplicate_content',
            'Duplicate content',
            'pass',
            $candidates->isEmpty()
                ? 'No other posts to compare against yet.'
                : "No significant overlap with your {$candidates->count()} existing posts."
        );
    }
}
