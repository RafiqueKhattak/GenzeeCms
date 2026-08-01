<?php

namespace App\Services\PolicyChecker;

/**
 * Heuristic checks modelled on Google's publisher content policies
 * (support.google.com/adsense/answer/1348688 and /answer/9335564): original,
 * substantial content; no prohibited content; clear navigation/metadata;
 * no deceptive or manipulated content. This is a best-effort automated
 * screen, not a guarantee — Google's own review is the final word, and the
 * UI must always say so.
 */
class RuleBasedPolicyChecker implements ContentPolicyCheckerInterface
{
    /**
     * Terms that, on their own, are strong signals of content Google
     * Publisher Policies prohibit outright (adult content, violence,
     * weapons, illegal drugs, hacking/cracking, counterfeit goods, gambling
     * without disclosure). Kept short and high-precision on purpose — the
     * goal is to catch obvious cases, not to police normal editorial
     * language, which would just train editors to ignore the tool.
     */
    protected const PROHIBITED_TERMS = [
        'porn', 'xxx', 'explicit sex', 'nude photos', 'sex tape',
        'child abuse', 'bestiality',
        'buy a gun online', 'untraceable firearm', 'how to make a bomb',
        'buy cocaine', 'buy heroin', 'buy meth', 'illegal drugs for sale',
        'crack this software', 'free serial key', 'download cracked',
        'counterfeit designer', 'replica handbags for sale',
        'hire a hacker', 'ddos service', 'phishing kit',
        'guaranteed casino win', 'unlicensed betting',
    ];

    /** Filler that signals the editor left placeholder text in. */
    protected const PLACEHOLDER_TERMS = ['lorem ipsum', 'todo:', 'tbd', 'placeholder text', 'fill this in'];

    public function check(PolicyCheckRequest $request): PolicyCheckResult
    {
        $findings = array_filter([
            $this->checkProhibitedContent($request),
            $this->checkPlaceholderText($request),
            $this->checkWordCount($request),
            $this->checkTitle($request),
            $this->checkMetaDescription($request),
            $this->checkFeaturedImage($request),
            $this->checkCategorisation($request),
            $this->checkHeadingStructure($request),
            $this->checkRepetition($request),
            $this->checkExcerpt($request),
        ]);

        $findings = array_values($findings);
        $score = $this->scoreFrom($findings);
        $status = $this->statusFrom($findings, $score);

        return new PolicyCheckResult($score, $status, $findings);
    }

    protected function checkProhibitedContent(PolicyCheckRequest $request): PolicyFinding
    {
        $haystack = mb_strtolower($request->title.' '.$request->plainBody().' '.$request->excerpt);

        foreach (self::PROHIBITED_TERMS as $term) {
            if (str_contains($haystack, $term)) {
                return new PolicyFinding(
                    'prohibited_content',
                    'Prohibited content',
                    'fail',
                    "Found text matching a disallowed content category (\"{$term}\"). Google AdSense will not serve ads on — and may suspend the whole site for — adult, violent, weapons/drugs-related, hacking, counterfeit-goods or unlicensed-gambling content.",
                    'Remove or substantially rewrite this section before publishing.'
                );
            }
        }

        return new PolicyFinding('prohibited_content', 'Prohibited content', 'pass', 'No disallowed content categories detected.');
    }

    protected function checkPlaceholderText(PolicyCheckRequest $request): ?PolicyFinding
    {
        $haystack = mb_strtolower($request->plainBody());

        foreach (self::PLACEHOLDER_TERMS as $term) {
            if (str_contains($haystack, $term)) {
                return new PolicyFinding(
                    'placeholder_text',
                    'Placeholder text',
                    'fail',
                    "Looks like unfinished/placeholder text is still in the post (\"{$term}\").",
                    'Replace the placeholder with real content before publishing.'
                );
            }
        }

        return null;
    }

    protected function checkWordCount(PolicyCheckRequest $request): PolicyFinding
    {
        $words = $request->wordCount();

        if ($words < 150) {
            return new PolicyFinding(
                'word_count',
                'Content length',
                'fail',
                "Only {$words} words. Google's \"high-quality, substantial content\" bar generally expects several hundred words of original material — thin content is one of the most common AdSense rejection reasons.",
                'Expand with more original detail, examples, or explanation — aim for 500+ words.'
            );
        }

        if ($words < 400) {
            return new PolicyFinding(
                'word_count',
                'Content length',
                'warn',
                "{$words} words is on the thin side. Longer, more thorough posts are safer for AdSense review and tend to rank better.",
                'Consider expanding to 500+ words if the topic supports it.'
            );
        }

        return new PolicyFinding('word_count', 'Content length', 'pass', "{$words} words — substantial enough for review.");
    }

    protected function checkTitle(PolicyCheckRequest $request): PolicyFinding
    {
        $title = trim($request->title);
        $len = mb_strlen($title);

        if ($len === 0) {
            return new PolicyFinding('title', 'Title', 'fail', 'Title is empty.', 'Add a clear, descriptive title.');
        }

        if (mb_strtoupper($title) === $title && $len > 8) {
            return new PolicyFinding(
                'title',
                'Title',
                'warn',
                'Title is in ALL CAPS, which reads as spammy/clickbait to both readers and ad reviewers.',
                'Use normal sentence or title case.'
            );
        }

        if (preg_match('/[!?]{2,}/', $title)) {
            return new PolicyFinding('title', 'Title', 'warn', 'Title uses repeated punctuation (e.g. "!!!" or "??"), a common clickbait signal.', 'Use a single punctuation mark, or none.');
        }

        if ($len > 70) {
            return new PolicyFinding('title', 'Title', 'warn', "Title is {$len} characters — likely to be truncated in search results.", 'Keep titles under ~60-70 characters.');
        }

        return new PolicyFinding('title', 'Title', 'pass', 'Title looks fine.');
    }

    protected function checkMetaDescription(PolicyCheckRequest $request): PolicyFinding
    {
        $desc = trim((string) $request->metaDescription);

        if ($desc === '') {
            return new PolicyFinding(
                'meta_description',
                'Meta description',
                'warn',
                'No meta description set — search engines will fall back to an auto-generated snippet.',
                'Write a 1-2 sentence summary (under 160 characters).'
            );
        }

        if (mb_strlen($desc) > 160) {
            return new PolicyFinding('meta_description', 'Meta description', 'warn', 'Meta description is over 160 characters and will likely be truncated in search results.', 'Trim it to ~150-160 characters.');
        }

        return new PolicyFinding('meta_description', 'Meta description', 'pass', 'Meta description looks fine.');
    }

    protected function checkFeaturedImage(PolicyCheckRequest $request): PolicyFinding
    {
        if (empty($request->featuredImage)) {
            return new PolicyFinding(
                'featured_image',
                'Featured image',
                'warn',
                'No featured image set. Pages with no original imagery can look thin to both readers and ad reviewers.',
                'Add a relevant featured image.'
            );
        }

        return new PolicyFinding('featured_image', 'Featured image', 'pass', 'Featured image set.');
    }

    protected function checkCategorisation(PolicyCheckRequest $request): PolicyFinding
    {
        $hasCategory = ! empty($request->category);
        $hasTags = count($request->tags) > 0;

        if (! $hasCategory && ! $hasTags) {
            return new PolicyFinding(
                'categorisation',
                'Organisation',
                'warn',
                'No category or tags set. Clear site navigation/organisation is part of Google\'s quality guidelines.',
                'Assign a category and a few relevant tags.'
            );
        }

        return new PolicyFinding('categorisation', 'Organisation', 'pass', 'Categorised.');
    }

    protected function checkHeadingStructure(PolicyCheckRequest $request): PolicyFinding
    {
        $hasHeading = (bool) preg_match('/<h[23][ >]/i', $request->bodyHtml);
        $words = $request->wordCount();

        if (! $hasHeading && $words > 300) {
            return new PolicyFinding(
                'structure',
                'Structure',
                'warn',
                'Long post with no subheadings (H2/H3) — harder to scan and a weaker quality signal.',
                'Break the content up with a few descriptive subheadings.'
            );
        }

        return new PolicyFinding('structure', 'Structure', 'pass', 'Structure looks reasonable.');
    }

    protected function checkRepetition(PolicyCheckRequest $request): ?PolicyFinding
    {
        $text = mb_strtolower($request->plainBody());
        $sentences = array_filter(array_map('trim', preg_split('/(?<=[.!?])\s+/', $text) ?: []));

        if (count($sentences) < 4) {
            return null;
        }

        $counts = array_count_values($sentences);
        arsort($counts);
        $topCount = reset($counts) ?: 0;

        if ($topCount >= 3 && $topCount / count($sentences) > 0.2) {
            return new PolicyFinding(
                'repetition',
                'Originality',
                'warn',
                'The same sentence repeats several times, which can look like keyword-stuffed or auto-generated content.',
                'Vary the wording and remove duplicated sentences.'
            );
        }

        return new PolicyFinding('repetition', 'Originality', 'pass', 'No obvious repetition detected.');
    }

    protected function checkExcerpt(PolicyCheckRequest $request): ?PolicyFinding
    {
        if ($request->type !== 'news') {
            return null;
        }

        if (empty(trim((string) $request->excerpt))) {
            return new PolicyFinding(
                'excerpt',
                'Summary',
                'warn',
                'News items normally show a one-line summary on the news index — none is set.',
                'Add a short summary/excerpt.'
            );
        }

        return new PolicyFinding('excerpt', 'Summary', 'pass', 'Summary set.');
    }

    /** @param PolicyFinding[] $findings */
    protected function scoreFrom(array $findings): int
    {
        $score = 100;
        foreach ($findings as $finding) {
            $score -= match ($finding->severity) {
                'fail' => 30,
                'warn' => 8,
                default => 0,
            };
        }

        return max(0, min(100, $score));
    }

    /** @param PolicyFinding[] $findings */
    protected function statusFrom(array $findings, int $score): string
    {
        foreach ($findings as $finding) {
            if ($finding->severity === 'fail') {
                return 'not_approvable';
            }
        }

        return $score >= 85 ? 'approvable' : 'needs_work';
    }
}
