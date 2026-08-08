<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sixth batch of news additions, sourced from the admin Keyword Ideas panel
 * (news-api headlines fetched 2 Aug 2026, exported to Excel by the user).
 * The panel surfaced 21 news-suggested headlines and 73 blog-suggested
 * ones; most were off-topic for this site (gadget releases, single-stock
 * trading tips, gaming) per the KeywordRelevance filter's own low fit
 * scores. These six are the ones that genuinely matched the site's beat
 * (wages/cost of living, Fed/inflation, mortgages, crypto, Gen Z culture)
 * and were rewritten entirely in this site's own words from the underlying
 * facts — not copied from the source articles — to avoid any duplicate-
 * content or copyright issue. Idempotent (updateOrCreate).
 */
class NewNewsBatch6Seeder extends Seeder
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
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 33) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'source_name' => $post['source_name'],
                    'source_url' => $post['source_url'],
                    'status' => 'published',
                    'published_at' => now()->subHours($i * 5),
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
                'slug' => 'california-minimum-wage-2027-increase',
                'title' => "California's Minimum Wage Is Going Up Again in 2027 — Here's the New Number",
                'excerpt' => "Governor Newsom confirmed California's statewide minimum wage will rise to \$17.40 an hour on 1 January 2027, a CPI-linked increase under the state's automatic annual adjustment law.",
                'meta_title' => 'California Minimum Wage Rises to $17.40 in 2027',
                'meta_description' => "California's statewide minimum wage will increase to \$17.40 an hour from 1 January 2027, a 50-cent CPI-linked rise confirmed by Governor Newsom's office.",
                'department' => 'global-economy',
                'source_name' => 'KTLA',
                'source_url' => 'https://ktla.com/news/california/california-minimum-wage-will-rise-again-in-2027-heres-the-new-rate/',
                'tags' => ['United States', 'Salary', 'Cost of Living', 'Inflation'],
                'body' => <<<'HTML'
<p class="lead">California's statewide minimum wage will climb to $17.40 an hour from 1 January 2027, Governor Gavin Newsom's office confirmed on 31 July 2026 — already the highest state-level minimum wage in the country, and about to go higher still.</p>
<h2>Where the number comes from</h2>
<p>The increase isn't a one-off political decision. A California law in effect since 2017 requires the state's minimum wage to rise automatically each year in line with the national Consumer Price Index for urban wage earners (CPI-W), capped at 3.5% a year. The Department of Finance certified a 2.99% CPI-W increase for the relevant period, which rounds to a 50-cent bump over the current $16.90 rate.</p>
<h2>Who actually gets more, and who doesn't</h2>
<p>The statewide figure is a floor, not a ceiling — many California cities and counties, and specific industries such as fast food and healthcare, already mandate higher minimums that this increase doesn't disturb. For workers outside those carve-outs, $17.40 becomes the legal minimum hourly rate everywhere in the state starting the new year.</p>
<h2>Why this matters beyond California</h2>
<p>California's CPI-linked model is one of several state minimum-wage escalators now running on autopilot across the US, decoupling wage floors from election-cycle politics. It's also a reminder that "minimum wage" isn't one fixed national number — it varies enormously by state, city and even employer size, well above the $7.25 federal floor in many places, and is worth checking locally rather than assumed.</p>
HTML,
            ],
            [
                'slug' => 'us-jobless-claims-rise-199000-august-2026',
                'title' => 'US Jobless Claims Edged Up to 199,000 — But Layoffs Are Still Historically Low',
                'excerpt' => 'New unemployment benefit filings rose by 1,000 to 199,000 for the week ending 1 August 2026, the Labor Department said, with layoffs still running well below their long-run average.',
                'meta_title' => 'US Jobless Claims Rise to 199,000',
                'meta_description' => 'US initial unemployment claims rose to 199,000 for the week ending 1 August 2026, the Labor Department reported, though layoffs remain historically low.',
                'department' => 'global-economy',
                'source_name' => 'AP News',
                'source_url' => 'https://apnews.com/article/employment-jobs-layoffs-pay-economy-87aecb5c134660d71e91ac57763fec26',
                'tags' => ['United States', 'Cost of Living'],
                'body' => <<<'HTML'
<p class="lead">First-time claims for US unemployment benefits rose to 199,000 in the week ending 1 August 2026, up 1,000 from the prior week's revised total of 198,000, the Labor Department reported. It's a small move, but it comes from one of the most closely watched real-time gauges of the American job market.</p>
<h2>Why weekly claims matter</h2>
<p>Initial jobless claims count how many people filed for unemployment benefits for the first time in a given week, which makes the series one of the fastest signals of layoff activity — far faster than the monthly jobs report, which takes weeks to compile. A rising trend over several weeks typically signals a cooling labor market; a single week's uptick, on its own, usually doesn't.</p>
<h2>The bigger picture: still a healthy labor market</h2>
<p>Despite the uptick, claims remain in the range economists consider consistent with a historically strong job market, well below levels seen during past downturns. Employers, in other words, are largely holding onto the workers they already have, even as hiring for new roles has slowed in several sectors.</p>
<h2>What it means if you're job hunting</h2>
<p>A low layoff rate is good news if you already have a job, but it says nothing about how hard it is to find a new one — those are two different measures. If you're searching, the more relevant signals are the monthly jobs report's hiring numbers and sector-specific postings data, not the weekly claims count alone.</p>
HTML,
            ],
            [
                'slug' => 'fed-alternative-inflation-indicators-lowest-in-years',
                'title' => 'Alternative Inflation Gauges the Fed Watches Are at Multi-Year Lows',
                'excerpt' => "Several inflation measures the Federal Reserve tracks alongside its official target have fallen to their lowest levels in years, even as Fed Chair Kevin Warsh faces pressure to act.",
                'meta_title' => 'Alternative Fed Inflation Gauges Hit Multi-Year Lows',
                'meta_description' => "Alternative inflation indicators the Federal Reserve monitors, including the Dallas Fed's trimmed mean measure, have dropped to some of their lowest readings in years.",
                'department' => 'finance-markets',
                'source_name' => 'CNBC',
                'source_url' => 'https://www.cnbc.com/2026/07/31/these-fed-alternative-indicators-show-inflation-is-at-lowest-in-years.html',
                'tags' => ['Inflation', 'Interest Rates', 'United States', 'Federal Reserve'],
                'body' => <<<'HTML'
<p class="lead">Behind the Federal Reserve's headline inflation number, a set of alternative measures the central bank also watches has quietly dropped to some of the lowest readings in years — a striking contrast at a moment when Fed Chair Kevin Warsh is under pressure to prove the Fed is serious about controlling prices.</p>
<h2>The trimmed-mean signal</h2>
<p>The Dallas Fed's "trimmed mean" measure — which strips out the most extreme price movements in either direction to isolate the underlying trend — put one-month annualized inflation for June at just 1.4%, its lowest reading since November 2020, with the 12-month trimmed-mean rate down to 2.2%. Dallas Fed President Lorie Logan has cautioned the measure may currently be excluding too many genuine price increases, making it read lower than the true trend.</p>
<h2>What bond markets are pricing in</h2>
<p>Market-based inflation expectations tell a similar story: the Treasury market's five-year breakeven inflation rate has fallen sharply since May to around 2.26%, and the one-year breakeven has dropped nearly half a percentage point over the same period, though it remains elevated near 3%.</p>
<h2>Why the Fed's own preferred gauge is under scrutiny</h2>
<p>Complicating the picture, Warsh has publicly dismissed the Fed's traditional PCE inflation gauge — its long-standing preferred measure — as unreliable, and has set up internal task forces to reconsider how the central bank measures inflation. For anyone trying to read the Fed's next move on interest rates, the takeaway is that "inflation" isn't one number the Fed watches — it's a basket of sometimes-contradictory signals, and which one gets emphasized can shape the outlook people build their financial plans around.</p>
HTML,
            ],
            [
                'slug' => 'mortgage-rates-highest-level-in-a-year',
                'title' => 'Mortgage Rates Just Hit Their Highest Level in Over a Year',
                'excerpt' => 'The average 30-year fixed mortgage rate climbed to 6.69% this week, the highest since mid-2025, as bond yields rose on inflation and oil-price concerns.',
                'meta_title' => 'US Mortgage Rates Hit Highest Level in a Year',
                'meta_description' => 'The average 30-year fixed mortgage rate rose to 6.69%, the highest level since July 2025, as rising bond yields push borrowing costs up.',
                'department' => 'finance-markets',
                'source_name' => 'NPR (WFDD)',
                'source_url' => 'https://www.wfdd.org/economy/2026-08-03/mortgage-rates-climb-to-highest-level-in-a-year',
                'tags' => ['Mortgage', 'Interest Rates', 'Inflation', 'United States'],
                'body' => <<<'HTML'
<p class="lead">The average rate on a 30-year fixed-rate US mortgage climbed to 6.69% this week, according to Freddie Mac data, up from 6.66% the week before and the highest level seen since late July 2025 — right in the middle of the traditionally busy summer home-buying season.</p>
<h2>What's pushing rates up</h2>
<p>Mortgage rates broadly track the yield on long-term government bonds, and those yields have risen sharply in recent weeks as investors weigh two separate pressures: rising oil prices tied to prolonged instability affecting the Strait of Hormuz, and renewed doubts about whether the Federal Reserve will hold the line on inflation. Both push bond yields, and with them mortgage rates, higher.</p>
<h2>What it actually costs a buyer</h2>
<p>The gap between a 6.66% and a 6.69% rate looks small on paper but compounds over a 30-year loan: on a $400,000 mortgage, even that narrow move adds roughly $8 to the monthly payment, and the swing from this cycle's lows to today's rate adds hundreds of dollars a month for a typical buyer. It's one of the reasons affordability, not just home prices, has become the dominant story in the US housing market.</p>
<h2>Should you wait it out?</h2>
<p>Timing mortgage rates precisely is notoriously difficult even for professionals, since rates move on economic data and geopolitical events that are inherently hard to predict. Buyers who need a home now are generally better served comparing lenders and loan terms in the present than trying to guess where rates go next; refinancing later remains an option if rates do eventually fall.</p>
HTML,
            ],
            [
                'slug' => 'gen-z-dumb-phones-social-media-detox',
                'title' => "Why Some Gen Zers Are Trading Smartphones for 'Dumb Phones'",
                'excerpt' => "A small but growing number of Gen Z users are swapping smartphones for stripped-down 'dumb phones' with no social media apps, part of a broader digital-minimalism trend.",
                'meta_title' => "Gen Z's 'Dumb Phone' Trend, Explained",
                'meta_description' => "A growing niche of Gen Z is trading smartphones for stripped-down 'dumb phones' with no social media apps, to break habits formed since childhood.",
                'department' => 'general-news',
                'source_name' => 'CBS News',
                'source_url' => 'https://www.cbsnews.com/news/social-media-smartphones-gen-z-dumb-phones/',
                'tags' => ['Gen Z', 'Technology', 'Online Safety'],
                'body' => <<<'HTML'
<p class="lead">A small but growing slice of Gen Z is deliberately downgrading — trading smartphones for "dumb phones" that strip out social media apps entirely, in a bid to break habits many say they never consciously chose to form in the first place.</p>
<h2>How big is the trend, really?</h2>
<p>It's still a niche behavior rather than a mass migration. One startup selling minimalist devices, Dumb.co, reports more than 5,000 active users, with its typical customer a 24-year-old woman — practical tools like maps, ride-hailing and music are kept, while social apps are simply left off the device. Other companies, like Light Phone, sell dedicated no-app hardware, while products such as Brick take a lighter-touch approach: a companion accessory that blocks selected apps on a phone someone already owns.</p>
<h2>Why Gen Z specifically</h2>
<p>The people driving this trend are the first generation to have grown up with social media as a constant rather than something adopted in adulthood, which several of them cite as exactly the problem — it's harder to recognize a habit as optional when it's been the default your entire life. Research cited alongside the trend puts the average US teen's social media use at close to five hours a day, well above general adult usage figures.</p>
<h2>A trend, not a rejection of technology</h2>
<p>Importantly, this isn't Gen Z rejecting phones or connectivity — most dumb-phone switchers keep a smartphone at home for tasks that need it, or use companion tools that block rather than remove functionality. It reads less as anti-technology and more as an attempt to separate "phone as tool" from "phone as attention-capture device," a distinction more software and hardware makers are starting to build products around.</p>
HTML,
            ],
            [
                'slug' => 'michael-saylor-ai-spending-bitcoin-headwind',
                'title' => 'Michael Saylor Says the AI Spending Boom Is Pulling Money Away From Bitcoin',
                'excerpt' => "Strategy's Michael Saylor says the roughly \$1 trillion AI infrastructure build-out by SpaceX, Google and Meta is temporarily diverting investor capital away from Bitcoin.",
                'meta_title' => 'Saylor: AI Spending Is a Headwind for Bitcoin',
                'meta_description' => "Michael Saylor says massive AI infrastructure spending is one of several factors temporarily diverting investor capital away from Bitcoin.",
                'department' => 'finance-markets',
                'source_name' => 'Benzinga',
                'source_url' => 'https://www.benzinga.com/trading-ideas/long-ideas/26/07/60841683/michael-saylor-says-spacex-google-and-metas-ai-spending-is-a-headwind-for-bitcoin',
                'tags' => ['Bitcoin', 'Crypto', 'AI', 'Investing'],
                'body' => <<<'HTML'
<p class="lead">Michael Saylor, the Bitcoin-buying executive chairman of Strategy (formerly MicroStrategy), says the massive wave of capital pouring into AI infrastructure is one of the biggest reasons Bitcoin has struggled to keep pace this summer — not because investors have turned bearish on crypto, he argues, but because the money that might otherwise flow into it is busy funding data centers instead.</p>
<h2>The argument: one pool of capital, two competing bets</h2>
<p>Speaking on Strategy's second-quarter earnings call, Saylor pointed to roughly $1 trillion or more in capital flowing into AI data-center build-outs from companies including SpaceX, Google, Meta, Anthropic and OpenAI. His reasoning: institutional investors and lenders have a finite amount of capital to deploy, and when that much money is absorbed by AI infrastructure projects, less is left over for other bets, Bitcoin included.</p>
<h2>Not the only headwind he named</h2>
<p>AI spending was just one of five factors Saylor listed as currently weighing on Bitcoin sentiment, alongside global trade tensions, disruption linked to the ongoing Gulf conflict, a Federal Reserve holding a relatively restrictive policy stance, and delays in passing US crypto market-structure legislation.</p>
<h2>Temporary headwind, not a bearish call</h2>
<p>Saylor framed this as a cyclical, temporary drag rather than a change in his long-term view — he remains constructive on Bitcoin and has argued each of the current headwinds could eventually flip into a tailwind once AI investment cools or crypto legislation moves forward. Worth noting: Saylor's company holds a large Bitcoin position, so his commentary, however data-grounded, comes from someone with a direct financial stake in Bitcoin's price recovering — read it as one insider's argument, not neutral analysis.</p>
HTML,
            ],
        ];
    }
}
