<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * One-time backfill: assigns relevant topic tags to posts that have none,
 * matched from this site's actual subject vocabulary (money, tax, crypto,
 * generations, the tools it offers) — not generic "trending" terms, which
 * would be off-topic keyword stuffing (see CLAUDE.md: this was explicitly
 * declined before). Idempotent — only adds tags to posts with zero tags, so
 * re-running never touches a post someone has since tagged by hand.
 */
class TagExistingPosts extends Command
{
    protected $signature = 'content:tag-posts {--dry-run : List what would be tagged without saving}';

    protected $description = "Backfill relevant tags on posts that currently have none";

    /**
     * term => tag label. Matched case-insensitively against title+excerpt
     * using word-boundary regex — which means inflected forms (plurals,
     * "-ing", "-s") of a root need their own explicit entry, since \b does
     * not stem. Where that bit a real post (e.g. "Investing" vs "invest",
     * "Exchange Rates" vs "exchange rate"), the inflected form is listed
     * alongside the original rather than replacing it, so no previously
     * matched post loses a tag on re-run.
     */
    protected const VOCABULARY = [
        'income tax' => 'Income Tax', 'tax' => 'Tax', 'vat' => 'VAT', 'gst' => 'GST',
        'salary' => 'Salary', 'zakat' => 'Zakat', 'pension' => 'Pension', 'budget' => 'Budget',
        'inflation' => 'Inflation', 'interest rate' => 'Interest Rates', 'loan' => 'Loans',
        'mortgage' => 'Mortgage', 'debt' => 'Debt', 'savings' => 'Savings', 'saving' => 'Savings',
        'invest' => 'Investing', 'investing' => 'Investing', 'index fund' => 'Investing',
        'remittance' => 'Remittances', 'exchange rate' => 'Exchange Rates', 'exchange rates' => 'Exchange Rates',
        'refund' => 'Tax Refunds',
        'deduction' => 'Tax Deductions', 'fbr' => 'FBR', 'hmrc' => 'HMRC', 'irs' => 'IRS',
        'zatca' => 'ZATCA', 'sbp' => 'State Bank of Pakistan', 'rbi' => 'RBI', 'fed' => 'Federal Reserve',
        'bitcoin' => 'Bitcoin', 'crypto' => 'Crypto', 'ethereum' => 'Ethereum', 'stablecoin' => 'Stablecoins',
        'stock' => 'Stock Market', 'etf' => 'ETFs', 'psx' => 'PSX',
        'freelance' => 'Freelancing', 'remote work' => 'Remote Work', 'student loan' => 'Student Loans',
        'cost of living' => 'Cost of Living', 'rent' => 'Renting',
        'ai' => 'AI', 'artificial intelligence' => 'AI', 'crypto regulat' => 'Crypto Regulation',
        'emi' => 'EMI', 'credit score' => 'Credit Score', 'invoice' => 'Invoicing',
        'gen z' => 'Gen Z', 'generation z' => 'Gen Z', 'gen alpha' => 'Gen Alpha',
        'millennial' => 'Millennials', 'boomer' => 'Baby Boomers',
        'uk' => 'UK', 'uae' => 'UAE', 'saudi' => 'Saudi Arabia', 'pakistan' => 'Pakistan',
        'india' => 'India', 'canada' => 'Canada', 'us' => 'United States',
        '2026' => '2026',
        // Added after auditing the posts left untagged by the original list.
        'compound interest' => 'Compound Interest', 'bmi' => 'BMI', 'body mass index' => 'BMI',
        'discount' => 'Discounts', 'discounts' => 'Discounts', 'profit margin' => 'Profit Margin',
        'calorie' => 'Calories', 'calories' => 'Calories', 'tdee' => 'Calories', 'bmr' => 'Calories',
        'ideal weight' => 'Health', 'pregnancy' => 'Health', 'due date' => 'Health',
        'password' => 'Online Safety', 'two-factor' => 'Online Safety', 'phishing' => 'Online Safety',
        'qr code' => 'Technology', 'image optimization' => 'Technology',
        'csv' => 'Technology', 'json' => 'Technology', 'base64' => 'Technology',
    ];

    /** Cap so a post doesn't end up with a wall of tags — 3-6 stays useful. */
    protected const MAX_TAGS_PER_POST = 6;

    public function handle(): int
    {
        $posts = Post::withTrashed()->doesntHave('tags')->with('category')->get();
        $tagged = 0;

        foreach ($posts as $post) {
            $haystack = mb_strtolower($post->title.' '.$post->excerpt);
            $labels = [];

            if ($post->category) {
                $labels[] = $post->category->name;
            }

            foreach (self::VOCABULARY as $term => $label) {
                if (count($labels) >= self::MAX_TAGS_PER_POST) {
                    break;
                }
                // Word-boundary match — plain str_contains() would let short
                // terms like "ai" or "us" match inside "retailers"/"focus".
                if (preg_match('/\b'.preg_quote($term, '/').'\b/i', $haystack) && ! in_array($label, $labels, true)) {
                    $labels[] = $label;
                }
            }

            if (empty($labels)) {
                continue;
            }

            $this->info("{$post->id}: {$post->title} -> ".implode(', ', $labels));

            if ($this->option('dry-run')) {
                continue;
            }

            $tagIds = collect($labels)->map(
                fn (string $label) => Tag::firstOrCreate(['slug' => Str::slug($label)], ['name' => $label])->id
            );

            $post->tags()->sync($tagIds);
            $tagged++;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run — re-run without --dry-run to apply.');

            return self::SUCCESS;
        }

        $this->info("Tagged {$tagged} post(s).");

        return self::SUCCESS;
    }
}
