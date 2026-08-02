<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Creates the news "department" categories used to group the News index
 * page (Today/Yesterday + department tabs) and best-effort assigns any
 * uncategorised news post to one by keyword match against its slug+title.
 *
 * The category set reflects what this site actually publishes (finance,
 * tax/regulatory, fintech/AI, macro economy, research write-ups) rather
 * than a generic newsroom taxonomy — there is no science desk here. New
 * departments can be added any time from /admin/categories; this seeder
 * only guarantees the starting set exists and nothing is left uncategorised.
 *
 * Idempotent: categories use updateOrCreate, and posts are only touched
 * while category_id is still null, so re-running never overwrites an
 * editor's manual re-categorisation.
 */
class NewsDepartmentSeeder extends Seeder
{
    /**
     * Ordered [slug, name, keywords] triples — checked top to bottom,
     * first keyword match wins. Order matters: more specific desks (like
     * Research) are checked before broader ones (like Finance & Markets).
     */
    protected const DEPARTMENTS = [
        ['research', 'Research', ['study', 'research', 'report finds', 'survey']],
        ['technology', 'Technology', ['ai ', ' ai', 'agentic', 'stablecoin', 'app launch', 'digital payments', 'e-invoicing', 'fintech']],
        ['tax-policy', 'Tax & Policy', ['tax', 'gst', 'vat', 'itr', 'stamp duty', 'zatca', 'fbr', 'wage protection', 'golden visa', 'treasury bill']],
        ['finance-markets', 'Finance & Markets', ['stock', 'market', 'etf', 'crypto', 'bitcoin', 'interest rate', 'repo rate', 'earnings', 'ipo', 'bond', 'psx', 'kse', 'rbi', 'sbp', 'fed ', 'coinbase', 'robinhood', 'paypal', 'stripe', 'sec ', 'sec approves', 'acquisition'],
        ],
        ['global-economy', 'Global Economy', ['remittance', 'wage', 'pay raise', 'employment', 'unemployment', 'jobs', 'budget', 'economy']],
        ['general-news', 'General News', []],
    ];

    public function run(): void
    {
        $categories = [];
        foreach (self::DEPARTMENTS as $i => [$slug, $name, $keywords]) {
            $categories[$slug] = Category::firstOrCreate(
                ['type' => 'news', 'slug' => $slug],
                ['name' => $name, 'order' => $i + 1]
            );
        }

        $general = $categories['general-news'];

        Post::where('type', 'news')->whereNull('category_id')->get()->each(function (Post $post) use ($categories, $general) {
            $haystack = ' '.mb_strtolower($post->slug.' '.$post->title).' ';

            foreach (self::DEPARTMENTS as [$slug, $name, $keywords]) {
                foreach ($keywords as $keyword) {
                    if (str_contains($haystack, mb_strtolower($keyword))) {
                        $post->update(['category_id' => $categories[$slug]->id]);

                        return;
                    }
                }
            }

            $post->update(['category_id' => $general->id]);
        });
    }
}
