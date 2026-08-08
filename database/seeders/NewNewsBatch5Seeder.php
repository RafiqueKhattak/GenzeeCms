<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fifth (final) batch of post-launch news additions: six real, sourced
 * stories, checked against all 65 existing news titles first to avoid
 * duplicate coverage. Brings total new news items to 30 (top of the
 * requested 20-30 range). Idempotent (updateOrCreate).
 */
class NewNewsBatch5Seeder extends Seeder
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
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 9) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'source_name' => $post['source_name'],
                    'source_url' => $post['source_url'],
                    'status' => 'published',
                    'published_at' => now()->subHours(($i + 25) * 4),
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
                'slug' => 'imf-completes-pakistan-third-review-eff',
                'title' => 'IMF Completes Third Review of Pakistan\'s Bailout, Cites Strong Fiscal Performance',
                'excerpt' => 'The IMF Executive Board completed Pakistan\'s third EFF review in May, noting a primary surplus of 1.6% of GDP expected for FY26, with reform targets due by end-August.',
                'meta_title' => 'IMF Completes Pakistan\'s Third EFF Review',
                'meta_description' => 'The IMF completed the third review of Pakistan\'s Extended Fund Facility programme, citing a projected 1.6% of GDP primary surplus for FY26 and reforms due by end-August 2026.',
                'department' => 'tax-policy',
                'source_name' => 'International Monetary Fund',
                'source_url' => 'https://www.imf.org/en/news/articles/2026/05/08/pr-26147-pakistan-imf-completes-3rd-rev-of-extended-arrangement-under-eff-and-2nd-rev-arrang-rsf',
                'tags' => ['Pakistan', 'IMF'],
                'body' => <<<'HTML'
<p class="lead">The IMF's Executive Board completed the third review of Pakistan's 37-month Extended Fund Facility programme in May, alongside the second review of a related climate-resilience arrangement, citing strong fiscal performance and reserve rebuilding that exceeded earlier projections.</p>
<h2>The headline fiscal number</h2>
<p>Pakistan is expected to post a primary surplus — government revenue exceeding non-interest spending — of 1.6% of GDP for the current fiscal year, a figure the IMF pointed to as evidence of genuine fiscal discipline rather than one-off improvement.</p>
<h2>What else the review found</h2>
<p>GDP growth accelerated in the first half of the fiscal year, inflation remained contained, the current account was broadly balanced, and foreign exchange reserve rebuilding outpaced what had been projected at the programme's outset — a combination the IMF described as meaningful progress in economic stabilization.</p>
<h2>What's due by the end of August</h2>
<p>Several specific reform commitments are targeted for completion by end-August 2026, including revising Pakistan's public investment methodology to weight climate considerations more heavily, publishing corrected import statistics after discrepancies were identified in trade data, and finalizing a new centralized tax audit selection system.</p>
<h2>Why continued IMF review matters for ordinary Pakistanis</h2>
<p>Programme reviews aren't just a formality — passing them unlocks continued IMF disbursements and signals to other lenders and investors that Pakistan's reform commitments are being met, which affects borrowing costs and investor confidence well beyond the IMF relationship itself.</p>
HTML,
            ],
            [
                'slug' => 'uk-state-pension-triple-lock-tax-threshold-2027',
                'title' => 'UK State Pension Set to Rise Above Tax-Free Threshold for First Time Under Triple Lock',
                'excerpt' => 'Early wage data points to a 4.2% state pension increase in April 2027 — enough to push the full new state pension above the frozen £12,570 personal tax allowance.',
                'meta_title' => 'UK State Pension to Exceed Tax Threshold in 2027',
                'meta_description' => 'The UK state pension is projected to rise to £241.90 a week in April 2027 under the triple lock, pushing it above the frozen £12,570 income tax personal allowance.',
                'department' => 'tax-policy',
                'source_name' => 'LCP',
                'source_url' => 'https://www.lcp.com/en/media-centre/press-releases/new-state-pension-guaranteed-to-exceed-tax-threshold-in-2027-under-triple-lock-policy',
                'tags' => ['UK', 'Retirement'],
                'body' => <<<'HTML'
<p class="lead">Early wage growth data points to a roughly 4.2% increase in the UK's state pension from April 2027 under the triple lock policy — a rise large enough that, combined with a long-frozen tax threshold, full-rate pensioners will pay income tax on their state pension for the first time.</p>
<h2>How the triple lock works</h2>
<p>The triple lock guarantees the state pension rises each year by whichever is highest of three measures: average wage growth (May-July of the prior year), CPI inflation (measured to September), or a flat 2.5%. Current wage growth data points to 4.2% being the applicable figure for April 2027, which would take the full new state pension to at least £241.90 a week.</p>
<h2>Why that collides with the tax threshold</h2>
<p>The income tax personal allowance — the amount anyone can earn before paying any income tax — has been frozen at £12,570 and is set to stay frozen until at least April 2031. A rising state pension and a frozen tax threshold were always going to meet eventually; 2027 is projected to be the year the full state pension itself exceeds that threshold, meaning pensioners with no other income will owe tax purely on their state pension for the first time.</p>
<h2>Why this is a genuinely new situation</h2>
<p>Historically, the state pension alone sat comfortably below the tax-free threshold, so most pensioners with no other significant income paid no income tax at all. This shift means many pensioners will need to engage with the tax system for the first time purely because of a policy interaction — a rising, protected pension meeting a deliberately frozen allowance — rather than any change in their actual financial circumstances.</p>
HTML,
            ],
            [
                'slug' => 'us-mortgage-rates-rise-6-69-percent-fifth-week',
                'title' => 'US Mortgage Rates Climb to 6.69% for a Fifth Straight Weekly Increase',
                'excerpt' => 'The average 30-year fixed mortgage rate rose to 6.69% as of 8 August, the highest level since late July 2025, as inflation concerns push borrowing costs higher.',
                'meta_title' => 'US Mortgage Rates Rise to 6.69%, Highest Since 2025',
                'meta_description' => 'The average US 30-year fixed mortgage rate climbed to 6.69% on 8 August 2026, a fifth consecutive weekly increase and the highest level since late July 2025.',
                'department' => 'finance-markets',
                'source_name' => 'US News',
                'source_url' => 'https://money.usnews.com/loans/mortgages/articles/mortgage-rates-today-august-3-2026',
                'tags' => ['United States', 'Loans'],
                'body' => <<<'HTML'
<p class="lead">The average rate on a 30-year fixed mortgage in the US rose to 6.69% as of 8 August, up from 6.66% the previous week — a fifth consecutive weekly increase and the highest level recorded since late July 2025.</p>
<h2>Why rates have been climbing</h2>
<p>Continued inflation concerns, alongside the Federal Reserve's decision to hold its policy rate unchanged rather than cut, have pushed mortgage rates upward over recent weeks. Mortgage rates track broader bond market expectations for future Fed policy more closely than the Fed's current rate itself, so persistent inflation worries can push mortgage rates higher even without an actual Fed rate hike.</p>
<h2>What this means for buyers</h2>
<p>A rise from roughly 6.5% to 6.7% might look small, but on a large loan amount it meaningfully raises the monthly payment and reduces how much home a given budget can afford — a reminder that mortgage affordability depends heavily on the specific rate available at the time of borrowing, not just on home prices or income.</p>
<h2>Why rates vary slightly across sources</h2>
<p>Different data providers reported slightly different figures for the same period — Bankrate showed 6.76%, other sources 6.93% for purchase mortgages specifically — reflecting differences in methodology, lender surveys, and loan type. The consistent signal across all of them is the same: rates have been trending upward over recent weeks, regardless of the exact reported figure.</p>
HTML,
            ],
            [
                'slug' => 'tether-usdt-market-cap-falls-5-billion',
                'title' => 'Tether\'s USDT Market Cap Falls $5 Billion in 60 Days, Raising Liquidity Questions',
                'excerpt' => 'USDT\'s market capitalization dropped from a peak near $190 billion in May to roughly $184 billion, even as the broader stablecoin market stays well above $300 billion.',
                'meta_title' => 'Tether USDT Market Cap Falls $5 Billion in 60 Days',
                'meta_description' => 'Tether\'s USDT market cap fell from a peak near $190 billion in May 2026 to roughly $184 billion, a $5 billion decline even as the broader stablecoin market tops $300 billion.',
                'department' => 'technology',
                'source_name' => 'Crypto Briefing',
                'source_url' => 'https://cryptobriefing.com/tether-usdt-market-cap-falls-5-billion/',
                'tags' => ['Crypto', 'Stablecoins'],
                'body' => <<<'HTML'
<p class="lead">Tether's USDT, the largest stablecoin by market capitalization, has seen its market cap fall by roughly $5.4 billion over 60 days — from a peak near $190 billion in May to around $184 billion by late July — a decline drawing attention to liquidity conditions across the broader crypto market.</p>
<h2>The scale in context</h2>
<p>Despite the decline, USDT still holds a commanding 63.9% share of the total stablecoin market and remains the third-largest cryptocurrency by market cap overall. The broader stablecoin market has continued growing through 2026, with total market cap reported well above $300 billion across all major stablecoins combined.</p>
<h2>Why a falling stablecoin market cap matters</h2>
<p>Stablecoins are typically used as a cash-equivalent within crypto markets — a place to hold value between trades without converting back to traditional currency. A falling market cap can signal capital genuinely leaving the crypto ecosystem (converted back to fiat), rather than simply moving between different crypto assets, which is why analysts watch stablecoin flows as a liquidity indicator.</p>
<h2>A parallel development</h2>
<p>Separately, Tether signed a memorandum of understanding with the Nairobi Securities Exchange to explore using USDT and Tether's Hadron platform for instant settlement of securities in Kenya's capital markets — a reminder that even amid a market cap decline, stablecoin issuers continue pursuing new institutional use cases beyond crypto trading itself.</p>
HTML,
            ],
            [
                'slug' => 'rbi-raises-gdp-forecast-holds-rate-5-25',
                'title' => 'RBI Holds Rate at 5.25%, Raises FY27 GDP Growth Forecast to 6.7%',
                'excerpt' => 'India\'s central bank left its repo rate unchanged in a unanimous vote on 5 August, while raising its growth forecast on strong domestic demand and export recovery.',
                'meta_title' => 'RBI Holds Rate at 5.25%, Raises India GDP Forecast to 6.7%',
                'meta_description' => 'The Reserve Bank of India held its repo rate at 5.25% on 5 August 2026 and raised its FY27 GDP growth forecast to 6.7%, citing strong domestic demand and exports.',
                'department' => 'global-economy',
                'source_name' => 'India Infoline',
                'source_url' => 'https://www.indiainfoline.com/news/economy/rbi-policy-update-august-2026-repo-rate-unchanged-at-5-25-fy27-gdp-growth-raised-to-6-7-inflation-forecast-cut',
                'tags' => ['India', 'Interest Rates'],
                'body' => <<<'HTML'
<p class="lead">India's Monetary Policy Committee unanimously voted to hold the repo rate at 5.25% on 5 August, while raising its FY27 GDP growth forecast to 6.7%, up from an earlier 6.6% estimate, citing resilient domestic demand and a recovery in exports.</p>
<h2>A rare combination: holding rates while raising growth expectations</h2>
<p>Central banks more commonly raise growth forecasts alongside rate cuts (to support growth further) or hold rates specifically because growth already looks strong enough without help. The RBI's decision to hold rather than cut, even while turning more optimistic on growth, suggests confidence that current policy is already well-calibrated rather than needing further stimulus.</p>
<h2>What's driving the improved outlook</h2>
<p>The RBI pointed to strong domestic demand, healthy manufacturing output, and a solid recovery in merchandise exports during the first quarter as the basis for the upgraded forecast — broad-based strength across multiple parts of the economy rather than a single standout sector.</p>
<h2>Why the RBI stayed cautious despite the upgrade</h2>
<p>The unanimous "neutral" stance reflects continued caution around global geopolitical uncertainty, with officials indicating they want better visibility on retail price trends before making the next move in either direction — a reminder that a stronger growth forecast doesn't automatically mean a shift toward rate cuts, if inflation risk remains a live concern.</p>
HTML,
            ],
            [
                'slug' => 'canada-inflation-eases-2-8-percent-june-2026',
                'title' => 'Canada\'s Inflation Eases to 2.8% in June as Gasoline Price Growth Slows',
                'excerpt' => 'Canada\'s annual inflation rate cooled to 2.8% in June from 3.2% in May, with core inflation measures both dipping below the Bank of Canada\'s 2% target.',
                'meta_title' => 'Canada Inflation Eases to 2.8% in June 2026',
                'meta_description' => 'Statistics Canada reported inflation cooling to 2.8% year-over-year in June 2026, down from 3.2% in May, driven by slower gasoline price growth.',
                'department' => 'global-economy',
                'source_name' => 'Statistics Canada',
                'source_url' => 'https://www150.statcan.gc.ca/n1/daily-quotidien/260720/dq260720a-eng.htm',
                'tags' => ['Canada', 'Inflation'],
                'body' => <<<'HTML'
<p class="lead">Canada's annual inflation rate eased to 2.8% in June, down from 3.2% in May, according to Statistics Canada — a second straight month of cooling that brought core inflation measures below the Bank of Canada's 2% target.</p>
<h2>What eased, and why</h2>
<p>The deceleration was driven largely by slower growth in gasoline prices, which rose 20.5% year-over-year in June compared with a much sharper 33.2% in May, as diplomatic talks and an interim ceasefire arrangement helped ease global oil prices. Energy costs are volatile enough that a single month's shift in oil prices can move the headline inflation number meaningfully on its own.</p>
<h2>What the core measures show</h2>
<p>CPI-trim came in at 1.8% and CPI-median at 1.9% — both measures the Bank of Canada watches specifically because they strip out volatile components like energy and are considered a better read on underlying inflation trends. Both sitting below the 2% target is a more reassuring signal than the headline 2.8% figure alone.</p>
<h2>Why this matters for the Bank of Canada's next move</h2>
<p>With the Bank of Canada having held its policy rate at 2.25% for six consecutive decisions, cooling headline and core inflation both give the central bank more room to consider a rate cut without immediate inflation risk — though a single month of improved data is rarely enough on its own to trigger an immediate policy shift.</p>
HTML,
            ],
        ];
    }
}
