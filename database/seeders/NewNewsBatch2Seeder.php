<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Second batch of post-launch news additions (Aug 2026 growth push): six
 * short news items covering real, sourced early-August 2026 events —
 * checked against all existing post titles first to avoid duplicate
 * coverage. Idempotent (updateOrCreate) so it's safe to re-run on deploy.
 */
class NewNewsBatch2Seeder extends Seeder
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
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 7) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'source_name' => $post['source_name'],
                    'source_url' => $post['source_url'],
                    'status' => 'published',
                    'published_at' => now()->subHours(($i + 7) * 4),
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
                'slug' => '23-billion-student-loan-settlement-450000-borrowers',
                'title' => '$23 Billion Settlement Could Forgive Debt for 450,000 Student Loan Borrowers',
                'excerpt' => 'A class-action settlement with the Department of Education could see hundreds of thousands of borrowers have their loans forgiven over claims of institutional misconduct.',
                'meta_title' => '$23B Settlement: 450,000 Student Loan Borrowers May Get Debt Forgiven',
                'meta_description' => 'A $23 billion class-action settlement with the US Department of Education could forgive debt for 450,000 student loan borrowers over institutional misconduct claims.',
                'department' => 'general-news',
                'source_name' => 'KOLD News 13',
                'source_url' => 'https://www.kold.com/2026/08/04/450000-student-loan-borrowers-may-get-debt-forgiven-23b-settlement/',
                'tags' => ['Loans', 'United States'],
                'body' => <<<'HTML'
<p class="lead">A $23 billion class-action settlement with the US Department of Education could result in debt forgiveness for as many as 450,000 student loan borrowers, following claims that dozens of schools engaged in significant institutional misconduct.</p>
<h2>What borrowers are alleging</h2>
<p>The advocacy group behind the original 2019 lawsuit argues the implicated schools misled students with false promises — about expected post-graduation earnings, the transferability of academic credits, and career stability — that did not hold up in practice, leaving borrowers with debt for an education that failed to deliver what was promised.</p>
<h2>Where this sits among other 2026 loan changes</h2>
<p>This settlement is separate from the broader repayment-plan overhaul already reshaping federal student loans this year, including the new Repayment Assistance Plan (RAP) and the wind-down of SAVE, PAYE and ICR plans. It targets a specific, narrower group — borrowers at named schools with alleged misconduct — rather than the general borrower population affected by the repayment-plan changes.</p>
<h2>A tax complication worth knowing about</h2>
<p>A federal tax exemption that kept forgiven student debt from counting as taxable income expired at the end of 2025 and is not expected to be extended. That means eligible borrowers may need to plan for a potential tax bill on any amount forgiven through this settlement in 2026, even as the debt itself is wiped out.</p>
HTML,
            ],
            [
                'slug' => 'us-july-2026-jobs-report-unemployment-falls-payrolls-shrink',
                'title' => 'US Jobs Report: Unemployment Falls, But Only Because People Left the Workforce',
                'excerpt' => 'Employers unexpectedly cut jobs in July while the unemployment rate fell — a combination that signals a weakening labour market rather than an improving one.',
                'meta_title' => 'US July 2026 Jobs Report: Payrolls Shrink, Unemployment Rate Falls',
                'meta_description' => 'US employers cut 23,000 jobs in July 2026 while the unemployment rate fell to 4.1% — driven by people leaving the labour force, not new hiring.',
                'department' => 'global-economy',
                'source_name' => 'Bloomberg',
                'source_url' => 'https://www.bloomberg.com/news/articles/2026-08-07/us-employers-unexpectedly-shed-jobs-unemployment-rate-falls',
                'tags' => ['United States', 'Gen Z'],
                'body' => <<<'HTML'
<p class="lead">US nonfarm payrolls fell by 23,000 in July, alongside a combined 103,000 downward revision to May and June — even as the headline unemployment rate dropped to 4.1%. The two figures moving in opposite directions is the story: the improvement is not what it looks like on the surface.</p>
<h2>Why the unemployment rate fell despite job losses</h2>
<p>The unemployment rate only counts people actively looking for work. The labour force shrank by 264,000 in July, pushing the participation rate down to 61.4% — its lowest since early 2021. When people stop looking for work altogether, they no longer count as "unemployed" in the official figure, which can push the headline rate down even while the underlying job market weakens.</p>
<h2>Where the losses concentrated</h2>
<p>Employment declined in local government education and retail trade, while healthcare continued its steady upward trend — a sector-specific pattern that has held for much of 2026, with healthcare acting as one of the few consistent sources of job growth.</p>
<h2>Why this matters beyond one month's data</h2>
<p>A falling unemployment rate driven by people leaving the workforce, rather than by hiring, is generally read by economists as a warning sign rather than good news — it can mask real softening in demand for workers. Combined with slowing wage growth noted in the same report, July's data adds to a picture of a labour market cooling faster than headline numbers alone would suggest.</p>
HTML,
            ],
            [
                'slug' => 'uk-cash-isa-allowance-cut-2027',
                'title' => 'UK to Cut Cash ISA Allowance to £12,000 From April 2027',
                'excerpt' => 'The government has confirmed the tax-free Cash ISA allowance will drop from £20,000 to £12,000 in 2027, while the full £20,000 stays for Stocks and Shares ISAs.',
                'meta_title' => 'UK Cash ISA Allowance Cut to £12,000 From April 2027',
                'meta_description' => 'From April 2027, the UK\'s tax-free Cash ISA allowance drops to £12,000 while Stocks and Shares ISA allowance stays at £20,000 — confirmed in the Autumn Budget factsheet.',
                'department' => 'tax-policy',
                'source_name' => 'GOV.UK',
                'source_url' => 'https://www.gov.uk/government/publications/fiscal-events-2026-factsheets/isa-reform-2027-anti-circumvention-rules-factsheet',
                'tags' => ['UK', 'Savings'],
                'body' => <<<'HTML'
<p class="lead">The UK government has confirmed that from 6 April 2027, the tax-free Cash ISA contribution allowance will fall from £20,000 to £12,000 a year, while Stocks and Shares and Innovative Finance ISAs keep the full £20,000 limit.</p>
<h2>What stays the same until then</h2>
<p>The overall £20,000 ISA allowance is unchanged for both the 2025/26 and 2026/27 tax years — meaning the current tax year is effectively the last full opportunity for younger savers to put the maximum £20,000 into a Cash ISA specifically before the lower cap applies. Savers aged 65 and over keep the full £20,000 Cash ISA allowance even after the 2027 change.</p>
<h2>Why the government is making the change</h2>
<p>The stated goal is encouraging a shift from cash savings toward stocks and shares investment, which is unaffected by the new lower cap. From 2027/28 onward, transfers from Stocks and Shares ISAs into Cash ISAs will also no longer be permitted, reinforcing the same direction.</p>
<h2>What savers can do before the change</h2>
<p>Anyone who prefers cash savings and wants to preserve the ability to shelter more of it from tax has a defined window — this tax year and next — to make full use of the current £20,000 Cash ISA allowance before it drops. Cash ISA rules also now allow opening and contributing to multiple Cash ISAs with different providers in the same tax year, adding some flexibility ahead of the reduced cap.</p>
HTML,
            ],
            [
                'slug' => 'gold-price-near-record-high-august-2026',
                'title' => 'Gold Climbs Toward Record Territory After Weak US Jobs Data',
                'excerpt' => 'Gold rose to around $4,350 an ounce in early August, pulled higher by a weaker-than-expected US jobs report that raised expectations of lower interest rates.',
                'meta_title' => 'Gold Price Nears Record High on Weak US Jobs Report',
                'meta_description' => 'Gold rose to roughly $4,350 per ounce in early August 2026, driven by a weak US jobs report reinforcing expectations of lower interest rates ahead.',
                'department' => 'finance-markets',
                'source_name' => 'Yahoo Finance',
                'source_url' => 'https://finance.yahoo.com/personal-finance/investing/article/gold-prices-today-thursday-august-6-2026-gold-prices-surge-as-hormuz-inches-closer-to-reopening-130743281.html',
                'tags' => ['United States'],
                'body' => <<<'HTML'
<p class="lead">Gold traded near $4,350 per troy ounce in early August, close to record territory, after a weaker-than-expected US jobs report strengthened the case for the Federal Reserve to cut interest rates.</p>
<h2>Why weak jobs data lifts gold</h2>
<p>Gold pays no interest or dividend, so it becomes relatively more attractive to investors when interest rates are expected to fall — the opportunity cost of holding it, versus interest-bearing assets, shrinks. July's soft US employment data, released 7 August, fed directly into expectations that the Fed will move toward cutting rates, which is the main driver behind gold's latest push higher.</p>
<h2>Not quite a new record</h2>
<p>Despite trading near record levels, August's price sits below 2026's actual peak — gold futures hit an all-time high of $5,542.40 per ounce on 29 January 2026. The current move is a strong rally within an already historic year for gold, rather than uncharted territory.</p>
<h2>Why this matters beyond investors</h2>
<p>Gold's price directly affects the value of jewellery and gold-based savings common across South Asia and the Gulf — a rising gold price increases the cost of traditional purchases like wedding jewellery, while benefiting anyone already holding gold as a savings vehicle. Both effects are worth factoring in before any near-term gold purchase or sale.</p>
HTML,
            ],
            [
                'slug' => 'pakistan-inflation-eases-9-2-percent-july-2026',
                'title' => 'Pakistan\'s Inflation Eases to 9.2% in July, Down From 11.1% in June',
                'excerpt' => 'Headline inflation cooled for a second straight month, though it remains more than double the same month last year — and prices still rose 1.2% from June to July alone.',
                'meta_title' => 'Pakistan Inflation Eases to 9.2% in July 2026',
                'meta_description' => 'Pakistan\'s headline CPI inflation eased to 9.2% year-on-year in July 2026, down from 11.1% in June, according to Pakistan Bureau of Statistics data.',
                'department' => 'global-economy',
                'source_name' => 'Business Recorder',
                'source_url' => 'https://www.brecorder.com/news/40432859/pakistan-inflation-clocks-in-at-92-in-july-2026',
                'tags' => ['Pakistan', 'Inflation'],
                'body' => <<<'HTML'
<p class="lead">Pakistan's headline inflation eased to 9.2% year-on-year in July, according to Pakistan Bureau of Statistics data — down from 11.1% in June, though still more than double the 4.1% recorded in July of the previous year.</p>
<h2>The regional breakdown</h2>
<p>Urban inflation slowed to 8.7% year-on-year, down from 11.2% in June, while rural inflation eased more modestly to 9.9% from 10.9%. Rural areas are running a noticeably higher inflation rate than urban ones, a gap worth watching as it can reflect different exposure to food and fuel price swings between the two.</p>
<h2>Why "easing" doesn't mean prices are falling</h2>
<p>A lower year-on-year inflation rate means prices are still rising, just more slowly than a year earlier — it is not the same as prices coming down. On a month-on-month basis, prices actually rose 1.2% from June to July, reversing a small 0.3% monthly decline the month before, a reminder that the annual trend and the immediate monthly trend can move in different directions.</p>
<h2>Why this matters for interest rate expectations</h2>
<p>Slowing inflation typically gives a central bank more room to consider cutting interest rates without risking a fresh price spiral. With the State Bank of Pakistan already holding its policy rate steady at recent meetings, a second consecutive month of easing inflation adds to the case analysts will be watching for at the next scheduled rate decision.</p>
HTML,
            ],
            [
                'slug' => 'canada-tfsa-limit-unchanged-7000-2026',
                'title' => 'Canada\'s TFSA Limit Stays at $7,000 for a Third Straight Year',
                'excerpt' => 'The CRA has confirmed the 2026 Tax-Free Savings Account contribution limit remains unchanged at $7,000, keeping cumulative lifetime room at $109,000.',
                'meta_title' => 'Canada TFSA Limit 2026: Confirmed Unchanged at $7,000',
                'meta_description' => 'The CRA has confirmed Canada\'s 2026 TFSA contribution limit stays at $7,000 for a third consecutive year, bringing total lifetime contribution room to $109,000.',
                'department' => 'tax-policy',
                'source_name' => 'Yahoo Finance Canada',
                'source_url' => 'https://ca.finance.yahoo.com/news/tfsa-contribution-limit-2026-203000957.html',
                'tags' => ['Canada', 'Savings'],
                'body' => <<<'HTML'
<p class="lead">The Canada Revenue Agency has confirmed the 2026 Tax-Free Savings Account (TFSA) contribution limit will stay at $7,000 — unchanged for a third consecutive year, following the same limit in 2024 and 2025.</p>
<h2>Why it's staying flat</h2>
<p>The annual TFSA limit is indexed to inflation and only rises once cumulative inflation since the last increase crosses a set threshold, rounded to the nearest $500. Inflation over the relevant period did not clear that threshold, so the limit holds at $7,000 for a third year running rather than increasing.</p>
<h2>What this means for total contribution room</h2>
<p>For anyone who has been a Canadian resident and at least 18 years old since the TFSA's 2009 launch, and has never contributed, cumulative lifetime contribution room now stands at $109,000. Contribution room that goes unused in a given year carries forward indefinitely, so the flat annual limit doesn't reduce anyone's ability to catch up later.</p>
<h2>Why the TFSA is worth using regardless of the limit staying flat</h2>
<p>Investment growth and withdrawals inside a TFSA are entirely tax-free — unlike a regular investment account, where gains are taxed — making it one of the more efficient places to hold long-term savings for Canadian residents. An unchanged annual limit doesn't reduce that underlying tax advantage; it only affects how quickly new contribution room becomes available each year.</p>
HTML,
            ],
        ];
    }
}
