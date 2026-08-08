<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fourth batch of post-launch news additions (Aug 2026 growth push): two
 * items covering the requested SBP/PKR keyword cluster (real, sourced),
 * plus four more diverse real stories — checked against all 59 existing
 * news titles first to avoid duplicate coverage. Idempotent
 * (updateOrCreate) so it's safe to re-run on every deploy.
 */
class NewNewsBatch4Seeder extends Seeder
{
    public function run(): void
    {
        $editors = User::where('role', 'editor')->pluck('id');

        foreach ($this->posts() as $i => $post) {
            $category = Category::where('type', 'news')->where('slug', $post['department'])->first();

            $record = Post::updateOrCreate(
                ['type' => 'news', 'slug' => $post['slug']],
                [
                    'category_id' => $category?->id,
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 4) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'source_name' => $post['source_name'],
                    'source_url' => $post['source_url'],
                    'status' => 'published',
                    'published_at' => now()->subHours(($i + 19) * 4),
                ]
            );

            $tagIds = collect($post['tags'])->map(
                fn (string $name) => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id
            );
            $record->tags()->sync($tagIds);
        }
    }

    protected function posts(): array
    {
        return [
            [
                'slug' => 'sbp-forex-reserves-rise-17-billion-governor',
                'title' => 'SBP\'s Forex Reserves Rise to $17.04 Billion as Governor Says Bank Bought $9 Billion Last Year',
                'excerpt' => 'The State Bank of Pakistan\'s reserves rose $13 million to $17.04 billion for the week ended 31 July, as Governor Jameel Ahmad said the bank bought around $9 billion from the open market last fiscal year.',
                'meta_title' => 'SBP Forex Reserves Rise to $17.04 Billion',
                'meta_description' => 'State Bank of Pakistan reserves rose to $17.04 billion for the week ended 31 July 2026, with Governor Jameel Ahmad confirming $9 billion in open-market dollar purchases last fiscal year.',
                'department' => 'tax-policy',
                'source_name' => 'Business Recorder',
                'source_url' => 'https://www.brecorder.com/news/40433627/sbp-held-foreign-exchange-reserves-up-13mn-to-1704bn',
                'tags' => ['State Bank of Pakistan', 'Pakistani Rupee', 'SBP'],
                'body' => <<<'HTML'
<p class="lead">The State Bank of Pakistan's foreign exchange reserves rose $13 million to $17.04 billion for the week ended 31 July, according to SBP data — a modest weekly gain that keeps reserves broadly stable heading into the new fiscal year.</p>
<h2>The full reserves picture</h2>
<p>Pakistan's total liquid foreign currency reserves stood at $22.47 billion at the end of July, combining the SBP's $17.04 billion with commercial banks' net foreign currency deposits of $5.43 billion. Current SBP holdings provide roughly 2.51 months of import cover — a commonly watched benchmark for external payment resilience.</p>
<h2>The Governor's comment on how reserves were built</h2>
<p>SBP Governor Jameel Ahmad said the central bank bought around $9 billion from the open market during the last fiscal year specifically to cushion foreign exchange reserves, with the interbank currency market serving as the primary channel for those inflows. That scale of open-market buying is a significant, deliberate reserve-building effort rather than a passive accumulation.</p>
<h2>Why reserve levels matter beyond the headline number</h2>
<p>Higher, more stable reserves give the SBP greater capacity to manage currency volatility and meet external payment obligations without resorting to emergency measures — a factor that feeds directly into investor and creditor confidence in Pakistan's macroeconomic stability, independent of any single week's exchange rate movement.</p>
HTML,
            ],
            [
                'slug' => 'pakistani-rupee-holds-steady-279-dollar',
                'title' => 'Pakistani Rupee Holds Steady Near 279 to the Dollar, Defying Depreciation Forecasts',
                'excerpt' => 'The rupee has stayed resilient against the dollar this year, supported by strong remittance flows and exporter dollar sales, even as earlier forecasts pointed toward faster depreciation.',
                'meta_title' => 'Pakistani Rupee Holds Steady Near 279/USD in 2026',
                'meta_description' => 'The Pakistani rupee has held relatively steady near 279 to the US dollar through 2026, supported by remittances and exporter dollar sales, despite earlier depreciation forecasts.',
                'department' => 'global-economy',
                'source_name' => 'Dawn',
                'source_url' => 'https://www.dawn.com/news/1983612',
                'tags' => ['Pakistani Rupee', 'USD/PKR', 'Currency Market'],
                'body' => <<<'HTML'
<p class="lead">The Pakistani rupee has traded in a relatively narrow, stable range against the US dollar through much of 2026, holding broadly steady even during periods of regional uncertainty — a contrast to earlier forecasts that had pointed toward faster depreciation over the same period.</p>
<h2>What earlier forecasts expected</h2>
<p>Fitch had projected the rupee weakening toward 295 to the dollar by the end of the current fiscal year, with some analysts flagging a risk of breaching 290, driven by rising import demand and external debt repayment pressure. The rupee's actual path through 2026 has come in noticeably more stable than those projections suggested.</p>
<h2>What's supporting the rupee</h2>
<p>Market analysts attribute the currency's resilience largely to robust remittance inflows from overseas Pakistanis and consistent exporter dollar sales above the 279.50 level, both of which add steady dollar supply into the local market. The rupee has also maintained stability through episodes of regional tension that some had expected to trigger destabilization.</p>
<h2>Why "stable" doesn't mean "fixed"</h2>
<p>Even a currency described as stable continues trading within a range day to day — recent sessions have shown the rupee moving by small margins (a few paisa at a time) around the 279 level, rather than staying completely static. Pakistan's currency operates under a broadly market-determined system, so continued small fluctuations, rather than a perfectly flat line, are the expected normal pattern.</p>
HTML,
            ],
            [
                'slug' => 'social-security-2027-cola-estimate',
                'title' => 'Social Security\'s 2027 COLA Estimated at 3.6-3.8%, Official Number Due in October',
                'excerpt' => 'Early estimates put next year\'s Social Security cost-of-living adjustment between 3.6% and 3.8%, with the official figure to be confirmed 14 October based on inflation data.',
                'meta_title' => 'Social Security 2027 COLA Estimated at 3.6-3.8%',
                'meta_description' => 'Early estimates put the 2027 Social Security COLA between 3.6% and 3.8%, with the official announcement due 14 October 2026 based on third-quarter inflation data.',
                'department' => 'tax-policy',
                'source_name' => 'AARP',
                'source_url' => 'https://www.aarp.org/social-security/cola-2027-increase-estimate/',
                'tags' => ['United States', 'Retirement'],
                'body' => <<<'HTML'
<p class="lead">Early estimates for Social Security's 2027 cost-of-living adjustment (COLA) range from 3.6% to 3.8%, with the official figure to be confirmed by the Social Security Administration on 14 October based on third-quarter inflation data.</p>
<h2>What the estimates currently show</h2>
<p>AARP estimates a 3.6% increase, which would raise the average retired worker's monthly benefit from roughly $2,026 to about $2,101. The Senior Citizens League projects a slightly higher 3.8%, while independent analyst Mary Johnson revised her forecast to 3.7% this month, citing cooling inflation trends.</p>
<h2>Why the estimate keeps shifting</h2>
<p>The COLA is calculated from a specific measure of inflation (CPI-W) averaged over the third quarter, so the final figure depends on inflation data that hadn't fully arrived at the time these estimates were made. Each new month of data can nudge projections up or down until the calculation window closes and the official number is set.</p>
<h2>When the increase actually takes effect</h2>
<p>Whatever the final percentage, the 2027 COLA takes effect with payments beneficiaries receive starting in January 2027 — the announcement in October gives recipients and financial planners a few months' notice before the higher payments actually begin.</p>
HTML,
            ],
            [
                'slug' => 'harvard-report-rental-affordability-crisis-deepens-2026',
                'title' => 'Harvard Report: Rental Affordability Crisis Deepens Even as Rent Growth Cools',
                'excerpt' => 'Nearly half of US renter households are cost-burdened, spending more than 30% of income on housing, even as national rent growth has slowed toward zero, a new Harvard report finds.',
                'meta_title' => 'Harvard Report: US Rental Affordability Crisis Deepens',
                'meta_description' => 'A Harvard Joint Center for Housing Studies report finds 49% of US renter households are cost-burdened despite cooling national rent growth in 2026.',
                'department' => 'global-economy',
                'source_name' => 'Harvard Joint Center for Housing Studies',
                'source_url' => 'https://www.jchs.harvard.edu/press-releases/new-report-finds-cooling-rental-markets-affordability-crisis-deepens-renters',
                'tags' => ['United States', 'Cost of Living'],
                'body' => <<<'HTML'
<p class="lead">National rent growth has cooled toward zero, but a rental affordability crisis has deepened rather than eased, according to Harvard's Joint Center for Housing Studies "America's Rental Housing 2026" report — a reminder that slower rent growth is not the same as rent becoming affordable.</p>
<h2>The scale of the affordability problem</h2>
<p>The report found 22.7 million renter households spent more than 30% of income on rent and utilities in 2024 — 49% of all renters — with 12.1 million of those households severely cost-burdened, paying more than half their income toward housing.</p>
<h2>Why cooling rent growth hasn't fixed the underlying problem</h2>
<p>Asking rents for professionally managed apartments actually declined slightly, by 0.6%, year over year by the fourth quarter of 2025, as new multifamily supply came online in several markets. But rents remain historically high relative to income even where growth has slowed, meaning a pause in rent increases hasn't undone years of accumulated affordability pressure.</p>
<h2>A shrinking supply of lower-cost units</h2>
<p>Between 2014 and 2024, the number of units renting for under $1,400 fell by 9.3 million, while units renting for $1,400 or more increased by 11.8 million — a structural shift toward higher-cost rental stock that a temporary cooling in rent growth doesn't reverse, and that disproportionately affects lower-income renters searching for affordable options.</p>
HTML,
            ],
            [
                'slug' => 'us-credit-card-debt-near-record-1-25-trillion',
                'title' => 'US Credit Card Debt Sits Near Record $1.25 Trillion as Average APR Tops 22%',
                'excerpt' => 'Americans owed $1.252 trillion on credit cards in Q1 2026, just below the all-time record set the previous quarter, as average interest rates on card balances climbed to 22.15%.',
                'meta_title' => 'US Credit Card Debt Near Record $1.25 Trillion',
                'meta_description' => 'US credit card debt stood at $1.252 trillion in Q1 2026, near the record $1.277 trillion set in Q4 2025, with average APRs on accruing balances rising to 22.15%.',
                'department' => 'finance-markets',
                'source_name' => 'Federal Reserve Bank of New York',
                'source_url' => 'https://www.thestreet.com/personal-finance/us-credit-card-debt-1-25-trillion-record-payoff',
                'tags' => ['United States', 'Debt'],
                'body' => <<<'HTML'
<p class="lead">Americans owed $1.252 trillion in credit card debt in the first quarter of 2026, just below the all-time record of $1.277 trillion set the previous quarter, according to the Federal Reserve Bank of New York's Quarterly Report on Household Debt and Credit.</p>
<h2>The numbers behind the record</h2>
<p>The average individual cardholder balance reached $6,519 in the first quarter, up 2.3% from $6,371 a year earlier — a 5.9% year-over-year increase in total debt even with the slight quarterly dip from the Q4 2025 peak.</p>
<h2>Interest rates are making the debt more expensive to carry</h2>
<p>Average APRs on credit card accounts actually accruing interest rose to 22.15% in the second quarter of 2026, up from 21.52% in the first quarter. At an average 21% APR across all accounts, Americans are paying an estimated $253 billion a year in interest and fees combined — described by the report as the most expensive consumer debt cycle in modern US history.</p>
<h2>Why this matters beyond the headline total</h2>
<p>Record debt combined with rising average rates means a growing share of minimum payments goes toward interest rather than reducing principal — exactly the dynamic behind the "minimum payment trap," where balances can take years to clear even with regular payments. Anyone carrying a revolving balance at anywhere near the current average rate has a strong mathematical case for prioritizing payoff above most other financial goals.</p>
HTML,
            ],
            [
                'slug' => 'china-gdp-growth-slows-4-7-percent-h1-2026',
                'title' => 'China\'s Economy Grows 4.7% in First Half of 2026 as Quarterly Momentum Slows',
                'excerpt' => 'China\'s GDP expanded 4.7% in the first six months of 2026, but growth eased to 4.3% year-on-year in the second quarter, the slowest pace since mid-2024, as investment weakened.',
                'meta_title' => 'China GDP Growth Slows to 4.7% in H1 2026',
                'meta_description' => 'China\'s economy grew 4.7% in the first half of 2026, but second-quarter growth eased to 4.3% year-on-year — the slowest pace since Q2 2024 — as fixed-asset investment declined.',
                'department' => 'global-economy',
                'source_name' => 'CGTN',
                'source_url' => 'https://news.cgtn.com/news/2026-07-15/China-s-economy-grows-4-7-in-first-half-of-2026-1ONgqNSZZqE/share_amp.html',
                'tags' => ['China'],
                'body' => <<<'HTML'
<p class="lead">China's economy grew 4.7% year-on-year in the first half of 2026, reaching 69.57 trillion yuan (about $10.28 trillion) — but the pace of growth eased notably between the first and second quarters, pointing to softening momentum heading into the second half of the year.</p>
<h2>A quarter-by-quarter slowdown</h2>
<p>Q1 2026 GDP grew 1.3% quarter-on-quarter, the strongest quarterly expansion since Q4 2024, but Q2 growth slowed to 0.9% quarter-on-quarter — matching market expectations but easing from Q1's pace. On a year-on-year basis, growth slowed from 5.0% in Q1 to 4.3% in Q2, the weakest quarterly reading since Q2 2024.</p>
<h2>What's driving the slowdown</h2>
<p>Fixed-asset investment fell 5.7% year-on-year over the first half, a significant drag on overall growth, while retail sales grew a more modest 2.7%, suggesting softer domestic demand alongside the investment weakness. The CNBC report attributed part of the softness to oil price shocks compounding already-soft domestic demand.</p>
<h2>Where the growth is coming from</h2>
<p>Not every sector slowed — industrial output rose 5.4% over the first half, with equipment manufacturing up 9.3% and high-tech manufacturing up a stronger 13.3%, showing growth concentrated in higher-value manufacturing even as broader investment activity cooled. Foreign trade also grew robustly, with imports and exports combined up 16.9% year-on-year to 25.47 trillion yuan.</p>
<h2>Why this matters globally</h2>
<p>As the world's second-largest economy, a meaningful slowdown in China's growth trajectory has ripple effects on global trade, commodity demand, and supply chains well beyond its own borders — making Chinese quarterly GDP data one of the more closely watched economic releases worldwide, not just a domestic statistic.</p>
HTML,
            ],
        ];
    }
}
