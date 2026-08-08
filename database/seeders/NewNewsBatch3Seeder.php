<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Third batch of post-launch news additions (Aug 2026 growth push): six
 * short news items covering real, sourced events — checked against all 53
 * existing news titles first to avoid duplicate coverage, and chosen to
 * add geographic diversity (Australia, EU, Japan not previously covered).
 * Idempotent (updateOrCreate) so it's safe to re-run on every deploy.
 */
class NewNewsBatch3Seeder extends Seeder
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
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 2) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'source_name' => $post['source_name'],
                    'source_url' => $post['source_url'],
                    'status' => 'published',
                    'published_at' => now()->subHours(($i + 13) * 4),
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
                'slug' => 'australia-payday-super-employers-2026',
                'title' => 'Australia\'s "Payday Super" Now Requires Employers to Pay Retirement Contributions With Every Paycheck',
                'excerpt' => 'From 1 July 2026, Australian employers must pay superannuation at the same time as wages, instead of quarterly — closing a gap that let unpaid super go unnoticed for months.',
                'meta_title' => 'Australia Payday Super: Contributions Now Due Every Payday',
                'meta_description' => 'From 1 July 2026, Australian employers must pay superannuation guarantee contributions at the same time as wages under the new "Payday Super" rule, instead of quarterly.',
                'department' => 'tax-policy',
                'source_name' => 'Australian Taxation Office',
                'source_url' => 'https://www.ato.gov.au/about-ato/new-legislation/in-detail/superannuation',
                'tags' => ['Retirement'],
                'body' => <<<'HTML'
<p class="lead">Australian employers are now required to pay employees' superannuation guarantee contributions at the same time as salary and wages, under a "Payday Super" reform that took effect 1 July 2026 — replacing a system that previously only required quarterly payment.</p>
<h2>Why the change matters</h2>
<p>Under the old quarterly system, an employer that failed to pay super could go unnoticed by an employee for up to three months, by which point unpaid contributions had already accumulated. Tying payment directly to each payday closes that gap, giving employees a far shorter window in which unpaid super can build up unnoticed.</p>
<h2>What else changed alongside it</h2>
<p>The Australian Taxation Office also introduced a new voluntary disclosure statement in August 2026 for employers reporting late superannuation payments, replacing individual line-item reporting with aggregated totals — a compliance simplification running alongside the core payday requirement.</p>
<h2>A separate change for high balances</h2>
<p>Separately, from 1 July 2026, tax concessions were reduced for very large superannuation balances: an additional 15% tax now applies to earnings on balances above $3 million, rising to an additional 10% on top of that for balances above $10 million — a change affecting a small minority of account holders, distinct from the payday timing reform that affects everyone.</p>
HTML,
            ],
            [
                'slug' => 'ecb-raises-rates-first-hike-three-years',
                'title' => 'ECB Raises Rates to 2.25% — Its First Hike in Three Years',
                'excerpt' => 'The European Central Bank lifted its deposit rate by 25 basis points in June, driven by rising energy prices, with markets now pricing in a further move in September.',
                'meta_title' => 'ECB Raises Interest Rates to 2.25%, First Hike in 3 Years',
                'meta_description' => 'The European Central Bank raised its deposit rate to 2.25% in June 2026, its first increase in three years, driven by rising energy prices.',
                'department' => 'global-economy',
                'source_name' => 'European Central Bank',
                'source_url' => 'https://www.ecb.europa.eu/press/pr/date/2026/html/ecb.mp260611~4d41bd5e83.en.html',
                'tags' => ['Interest Rates'],
                'body' => <<<'HTML'
<p class="lead">The European Central Bank raised its three key interest rates by 25 basis points in June 2026 — its first increase in three years — lifting the deposit facility rate to 2.25%, the main refinancing rate to 2.40%, and the marginal lending rate to 2.65%.</p>
<h2>Why the ECB moved after years of holding</h2>
<p>The hike was largely prompted by rising energy prices pushing inflation pressures back up across the eurozone, reversing a multi-year period in which the ECB had held or cut rates. It marks a meaningful shift in direction after an extended pause.</p>
<h2>What happened next</h2>
<p>At its following meeting in July, the ECB held rates steady, widely expected after the June move. Markets are now pricing in a strong likelihood of a further rate increase at the ECB's September meeting, suggesting June's hike may be the start of a new tightening cycle rather than a one-off adjustment.</p>
<h2>Why this matters beyond the eurozone</h2>
<p>ECB rate moves influence borrowing costs and currency values well beyond the eurozone itself, affecting anyone dealing in euros, holding euro-denominated debt or investments, or doing business with European partners. A shift from years of low rates to renewed tightening is a meaningful signal for global markets, not just European ones.</p>
HTML,
            ],
            [
                'slug' => 'us-401k-contribution-limit-2026-24500',
                'title' => 'IRS Confirms 401(k) Contribution Limit Rises to $24,500 for 2026',
                'excerpt' => 'The IRS has confirmed the annual 401(k) employee deferral limit for 2026, along with a higher IRA contribution limit — both adjusted for inflation from the prior year.',
                'meta_title' => 'IRS 401(k) Limit 2026: $24,500 Confirmed',
                'meta_description' => 'The IRS has confirmed the 2026 401(k) employee contribution limit rises to $24,500, with the IRA contribution limit increasing to $7,500.',
                'department' => 'tax-policy',
                'source_name' => 'Internal Revenue Service',
                'source_url' => 'https://www.irs.gov/newsroom/401k-limit-increases-to-24500-for-2026-ira-limit-increases-to-7500',
                'tags' => ['United States', 'Retirement'],
                'body' => <<<'HTML'
<p class="lead">The IRS has confirmed the 2026 annual contribution limit for 401(k) plans rises to $24,500, up from the prior year, with the IRA contribution limit increasing to $7,500 — both adjustments tied to inflation.</p>
<h2>What actually changed</h2>
<p>The employee elective deferral limit for 401(k), 403(b) and most 457 plans is now $24,500 for 2026. Catch-up contribution limits for those aged 50 and over, and the enhanced "super catch-up" for ages 60-63 introduced under recent legislation, apply on top of this base limit, letting older savers set aside meaningfully more each year.</p>
<h2>Why the limit rises most years</h2>
<p>Contribution limits are adjusted periodically based on inflation data, specifically the Consumer Price Index. In years with higher inflation, the limit tends to rise by a larger amount; in lower-inflation years, it can stay flat, as has happened with some other retirement account limits in recent years.</p>
<h2>What this means for retirement planning</h2>
<p>A higher contribution limit is only useful to the extent someone can actually afford to contribute more — but for anyone already maxing out their prior year's limit, the increase is an automatic opportunity to shelter additional income from current-year tax, compounding over a longer horizon toward retirement.</p>
HTML,
            ],
            [
                'slug' => 'bank-of-japan-holds-rate-1-percent-inflation-warning',
                'title' => 'Bank of Japan Holds Rate at 1%, Warns Inflation May Exceed Target',
                'excerpt' => 'Japan\'s central bank kept its policy rate at a three-decade high of 1% in July, cautioning that core inflation could run above its 2% target.',
                'meta_title' => 'Bank of Japan Holds Rate at 1%, Flags Inflation Risk',
                'meta_description' => 'The Bank of Japan held its policy rate at 1% in July 2026 — the highest since 1995 — while warning core inflation could exceed its 2% target.',
                'department' => 'global-economy',
                'source_name' => 'CNBC',
                'source_url' => 'https://www.cnbc.com/2026/07/31/boj-rates-yen-intervention-inflation-japan.html',
                'tags' => ['Interest Rates', 'Inflation'],
                'body' => <<<'HTML'
<p class="lead">The Bank of Japan held its policy rate steady at 1% in July, the highest level since 1995, while warning that core inflation could run above its 2% target — a notable statement from a central bank long associated with near-zero rates and persistently low inflation.</p>
<h2>How Japan got here</h2>
<p>The BOJ raised rates to 1% in June, its first hike since a December increase to 0.75%, driven by a weakening yen and inflation that had started climbing. The June decision passed on a split 7-1 vote, reflecting genuine disagreement within the board about the pace of tightening.</p>
<h2>Why the July hold, despite the inflation warning</h2>
<p>Even while flagging inflation risk, the BOJ opted to hold rather than hike again immediately — an 8-1 decision, with one board member pushing for a further increase to 1.25%. Central banks often pause briefly between hikes to assess how prior increases are filtering through the economy before moving again.</p>
<h2>Why this is a bigger deal than it might sound</h2>
<p>Japan has operated with near-zero or negative interest rates for most of the past three decades, making even modest hikes a significant policy shift. A Japanese central bank actively raising rates and warning about inflation exceeding target is a meaningfully different economic backdrop than markets have grown used to, with knock-on effects for global currency and bond markets given Japan's size as a global lender.</p>
HTML,
            ],
            [
                'slug' => 'ethereum-etf-inflows-outpace-bitcoin-july-2026',
                'title' => 'Ethereum ETFs Outpaced Bitcoin ETFs in July Inflows',
                'excerpt' => 'Ethereum-linked exchange-traded funds pulled in $365 million in July, more than double Bitcoin ETF inflows over the same month, led by BlackRock\'s ETHA.',
                'meta_title' => 'Ethereum ETF Inflows Beat Bitcoin ETFs in July 2026',
                'meta_description' => 'Ethereum ETFs attracted $365 million in inflows in July 2026, more than double Bitcoin ETF inflows of $172 million, led by BlackRock\'s ETHA fund.',
                'department' => 'technology',
                'source_name' => 'Cryptonomist',
                'source_url' => 'https://en.cryptonomist.ch/2026/08/02/ethereum-etf-inflows-july/',
                'tags' => ['Crypto', 'Ethereum'],
                'body' => <<<'HTML'
<p class="lead">Ethereum exchange-traded funds attracted $365 million in net inflows in July, more than double the $172 million that flowed into Bitcoin ETFs over the same month — a notable reversal after Bitcoin ETFs had dominated inflows for most of the year.</p>
<h2>Where the money went</h2>
<p>BlackRock's ETHA led inflows among Ethereum funds, pulling in a single-day inflow of over $50 million as recently as 5 August, with Fidelity's FETH, Bitwise's ETHW and 21Shares' TETH also recording positive flows the same day — spread across several issuers rather than concentrated in just one.</p>
<h2>Why this shift is notable</h2>
<p>Bitcoin ETFs have generally attracted the larger share of institutional crypto ETF investment since their approval, so a month where Ethereum inflows more than double Bitcoin's marks a genuine, if possibly temporary, rotation in institutional sentiment toward Ethereum specifically, rather than crypto exposure broadly.</p>
<h2>Why this matters beyond one month's flows</h2>
<p>ETF inflows are a reasonable proxy for institutional demand, since they represent real capital being allocated rather than speculative retail trading volume. A sustained shift toward Ethereum-specific products, if it continues beyond July, would suggest institutions are increasingly treating Ethereum as a distinct investment case rather than simply a smaller, more volatile version of Bitcoin exposure.</p>
HTML,
            ],
            [
                'slug' => 'uk-national-insurance-thresholds-frozen-2030',
                'title' => 'UK Freezes National Insurance and Income Tax Thresholds Until 2030-31',
                'excerpt' => 'Employer and employee National Insurance thresholds, along with income tax bands, stay frozen for years to come — a "stealth tax" that raises more revenue as wages rise.',
                'meta_title' => 'UK National Insurance and Tax Thresholds Frozen to 2030-31',
                'meta_description' => 'UK National Insurance and income tax thresholds remain frozen until 2030-31, a fiscal drag policy that raises effective tax as wages rise even without a rate change.',
                'department' => 'tax-policy',
                'source_name' => 'House of Commons Library',
                'source_url' => 'https://commonslibrary.parliament.uk/research-briefings/cbp-10618/',
                'tags' => ['UK', 'Salary'],
                'body' => <<<'HTML'
<p class="lead">UK National Insurance thresholds and income tax bands remain frozen until 2030-31, extending a policy that raises effective tax revenue over time without any headline rate ever changing — commonly referred to as "fiscal drag."</p>
<h2>What's actually frozen</h2>
<p>The employer National Insurance secondary threshold stays at £5,000 a year, the employee NI rate holds at 8%, and income tax thresholds — the £12,750 personal allowance, £50,270 higher-rate band, and £125,140 additional-rate band — are all frozen through 2030-31. The Lower Earnings Limit is one of the few figures still rising, moving to £6,708 annually under standard inflation indexation.</p>
<h2>How a frozen threshold quietly raises tax</h2>
<p>When wages rise with inflation but tax thresholds don't move, more income each year crosses into higher tax bands or becomes newly taxable — without any government ever announcing a tax increase. Over a multi-year freeze, this "fiscal drag" effect can add up to a substantial real increase in the average tax burden, even though no rate has technically changed.</p>
<h2>Why this matters for pay rise planning</h2>
<p>Anyone receiving a pay rise during a threshold freeze should expect to keep a smaller share of that raise than the headline percentage suggests, since more of it lands in a bracket taxed at the same or a higher rate than before. Understanding this ahead of a raise or when negotiating pay helps set realistic expectations for the actual take-home impact rather than assuming the full raise translates directly to spendable income.</p>
HTML,
            ],
        ];
    }
}
