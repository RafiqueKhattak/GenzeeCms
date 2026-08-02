<?php

namespace App\Services\Keywords;

/**
 * Scores how well a keyword or headline fits this site's actual subject area.
 *
 * This exists because raw "trending now" feeds are dominated by sport,
 * celebrities and breaking local news — none of which this site covers.
 * Publishing content chasing those terms would produce off-topic pages that
 * rank for nothing and dilute the site's topical focus, which is exactly what
 * Google's helpful-content guidance warns against. Scoring lets the admin
 * panel surface the small slice of trending terms that genuinely fit.
 */
class KeywordRelevance
{
    /** Core subject areas, weighted by how central they are to the site. */
    protected const NICHE_TERMS = [
        // Money / tax — the site's strongest area
        'tax' => 30, 'income tax' => 35, 'vat' => 30, 'gst' => 30, 'salary' => 30,
        'wage' => 25, 'pension' => 25, 'budget' => 25, 'inflation' => 28, 'interest rate' => 30,
        'loan' => 30, 'mortgage' => 30, 'debt' => 25, 'savings' => 28, 'invest' => 25,
        'zakat' => 30, 'remittance' => 25, 'currency' => 25, 'exchange rate' => 28,
        'social security' => 28, 'tariff' => 22, 'refund' => 22, 'deduction' => 25,
        'fbr' => 30, 'hmrc' => 28, 'irs' => 28, 'zatca' => 28, 'sbp' => 25, 'rbi' => 25,

        // Crypto / markets
        'bitcoin' => 25, 'crypto' => 25, 'ethereum' => 22, 'stablecoin' => 22,
        'stock' => 20, 'etf' => 22, 'psx' => 22, 'nasdaq' => 18,

        // Work / cost of living
        'freelance' => 25, 'remote work' => 22, 'unemployment' => 22, 'job market' => 22,
        'cost of living' => 28, 'rent' => 22, 'student loan' => 30,

        // Tech / tools the site actually has
        'ai' => 18, 'artificial intelligence' => 20, 'iphone' => 15, 'android' => 15,
        'qr code' => 25, 'password' => 22, 'converter' => 25, 'calculator' => 35,

        // Health tools the site has
        'bmi' => 28, 'calorie' => 25, 'due date' => 25,

        // Generational — the site's brand area
        'gen z' => 35, 'generation z' => 35, 'gen alpha' => 30, 'millennial' => 30,
        'boomer' => 28, 'generation' => 20,
    ];

    /**
     * Strong signals the item is outside the site's scope. Applied as a
     * penalty rather than a hard block, so a genuinely relevant headline that
     * happens to mention a team name isn't discarded outright.
     */
    protected const OFF_TOPIC_TERMS = [
        'vs', 'match', 'league', 'cup', 'fc ', 'united', 'city', 'season', 'episode',
        'trailer', 'box office', 'movie', 'film', 'actor', 'actress', 'singer', 'album',
        'wwe', 'nba', 'nfl', 'mlb', 'cricket', 'football', 'soccer', 'goal', 'score',
        'shooting', 'murder', 'crash', 'fire', 'storm', 'earthquake', 'weather',
        'election', 'senator', 'minister', 'president', 'party', 'protest',
        'died', 'death', 'obituary', 'wedding', 'divorce', 'dating',
    ];

    public function score(string $text): int
    {
        $haystack = ' '.mb_strtolower(trim($text)).' ';

        $score = 0;
        foreach (self::NICHE_TERMS as $term => $weight) {
            if (str_contains($haystack, ' '.$term) || str_contains($haystack, $term.' ')) {
                $score += $weight;
            }
        }

        $penalty = 0;
        foreach (self::OFF_TOPIC_TERMS as $term) {
            if (str_contains($haystack, ' '.$term)) {
                $penalty += 20;
            }
        }

        return (int) max(0, min(100, $score - $penalty));
    }

    /**
     * Which content type a keyword is better suited to. Time-sensitive terms
     * make better news items; evergreen "how much / what is" terms make better
     * blog posts, which is also what the tools link out from.
     */
    public function suggestType(string $text): string
    {
        $haystack = mb_strtolower($text);

        foreach (['announce', 'launch', 'deadline', 'raises', 'cuts', 'ruling', 'approved', 'extended', 'record', 'report', '2026'] as $newsy) {
            if (str_contains($haystack, $newsy)) {
                return 'news';
            }
        }

        return 'blog';
    }
}
