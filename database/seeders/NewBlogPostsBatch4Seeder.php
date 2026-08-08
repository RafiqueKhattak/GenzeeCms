<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fourth batch of post-launch blog additions (Aug 2026 growth push): four
 * posts targeting the PKR/SBP/monetary-policy keyword cluster explicitly
 * requested (usd/pkr, state bank of pakistan, sbp governor, interest rate,
 * inflation, monetary policy, currency market, pkr exchange rate), plus
 * two tied to the batch-4 tools. Idempotent (updateOrCreate).
 */
class NewBlogPostsBatch4Seeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('type', 'blog')->where('slug', 'money-investing')->first();
        $editors = User::where('role', 'editor')->pluck('id');

        foreach ($this->posts() as $i => $post) {
            $record = Post::updateOrCreate(
                ['type' => 'blog', 'slug' => $post['slug']],
                [
                    'category_id' => $category?->id,
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 8) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'status' => 'published',
                    'published_at' => now()->subDays(24 - $i),
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
                'slug' => 'what-moves-usd-to-pkr-exchange-rate',
                'title' => 'What Actually Moves the USD to PKR Exchange Rate?',
                'excerpt' => 'The dollar to rupee rate changes every day — here are the actual forces behind it: remittances, imports, SBP reserves, and interest rate differentials.',
                'meta_title' => 'What Moves the USD to PKR Exchange Rate? Explained',
                'meta_description' => 'The USD/PKR exchange rate moves on remittance flows, import demand, SBP reserves and interest rate differentials. Here is how each factor actually works.',
                'tags' => ['USD/PKR', 'Pakistani Rupee', 'Currency Market'],
                'body' => <<<'HTML'
<p class="lead">Open any Pakistani news app and the USD to PKR rate is somewhere on the front page — it is one of the most closely watched numbers in the country's economy. But what actually pushes it up or down on any given day?</p>
<h2>Remittances: a major source of dollar supply</h2>
<p>Overseas Pakistanis sending money home convert dollars into rupees, which is one of the largest steady sources of dollar supply into Pakistan's currency market. A strong month of remittance inflows tends to support the rupee; a weak one adds pressure toward depreciation, all else being equal — which is part of why remittance data is watched almost as closely as the exchange rate itself.</p>
<h2>Import demand: the main source of dollar demand</h2>
<p>Pakistan imports a significant share of its energy, machinery and raw materials, all typically paid for in dollars. Rising import demand — whether from economic growth, oil price spikes, or seasonal patterns — increases demand for dollars in the local market, pushing the exchange rate in the opposite direction from remittances.</p>
<h2>SBP reserves and intervention</h2>
<p>The State Bank of Pakistan holds foreign exchange reserves and can buy or sell dollars in the open market to manage volatility, though it does not fix the rate outright under the current market-based regime. Rising SBP reserves generally signal more capacity to smooth out sharp currency swings; falling reserves can make the rupee more vulnerable to sudden pressure.</p>
<h2>Interest rate differentials</h2>
<p>When Pakistan's interest rates are meaningfully higher than rates in the US or other major economies, rupee-denominated assets become relatively more attractive to yield-seeking investors, which can support the currency. When that gap narrows — say, if the Fed raises rates while the SBP holds — some of that support fades, a dynamic that plays out across most emerging-market currencies, not just the rupee.</p>
<h2>Why the rate can move even with no single "big" news event</h2>
<p>All of these factors interact continuously, not just on days with major headlines — a quieter-than-usual week for remittances, a routine import payment cycle, or minor shifts in global dollar strength can all nudge the rate without any single dramatic cause. Trying to explain every daily wiggle with one specific event usually oversimplifies what is really a constant balance of several forces at once.</p>
<h2>What this means for planning around the rate</h2>
<p>Anyone budgeting for a dollar-denominated expense — tuition abroad, an import order, a foreign trip — is better served by watching the underlying trend across these factors than trying to time a single "best" day to convert. Checking a live rate before any actual transaction remains essential, since the underlying drivers shift the number continuously.</p>
HTML,
            ],
            [
                'slug' => 'what-does-state-bank-of-pakistan-actually-do',
                'title' => 'What Does the State Bank of Pakistan Actually Do? A Plain-English Guide',
                'excerpt' => 'The SBP sets interest rates, manages the currency, and regulates banks — but what does that actually mean in practice, and how does it affect your everyday money?',
                'meta_title' => 'What Does the State Bank of Pakistan (SBP) Do?',
                'meta_description' => 'A plain-English guide to what the State Bank of Pakistan actually does: monetary policy, currency management, bank regulation, and how it affects your money.',
                'tags' => ['State Bank of Pakistan', 'Monetary Policy', 'SBP'],
                'body' => <<<'HTML'
<p class="lead">The State Bank of Pakistan appears in the news constantly — holding rates, managing reserves, issuing new rules for banks — but what does the country's central bank actually do, and why does it matter to someone who has never set foot inside a bank branch?</p>
<h2>Setting monetary policy</h2>
<p>The SBP's Monetary Policy Committee meets on a scheduled basis to set the policy interest rate — the rate that influences borrowing costs throughout the economy, from mortgages to business loans to credit cards. Raising the rate generally cools inflation by making borrowing more expensive and saving more attractive; cutting it generally stimulates borrowing and spending. This single number cascades through the entire financial system.</p>
<h2>Managing the currency and reserves</h2>
<p>The SBP holds Pakistan's foreign exchange reserves and can intervene in the currency market to smooth excessive volatility, though the rupee trades under a broadly market-determined system rather than a fixed peg. Reserve levels, reported weekly, are watched as a signal of the country's capacity to handle external payment obligations and currency pressure.</p>
<h2>Regulating banks</h2>
<p>Every commercial bank operating in Pakistan is regulated and supervised by the SBP, which sets capital requirements, conducts inspections, and can intervene when a bank faces serious problems — the layer of oversight that exists specifically to protect depositors and maintain confidence in the banking system as a whole.</p>
<h2>The Governor's role</h2>
<p>The SBP Governor leads the institution and its Monetary Policy Committee, and is often the public face of major announcements — rate decisions, reserve updates, and broader economic commentary. Governor statements are watched closely by markets and media because they can signal the central bank's thinking ahead of a formal decision, not just report on the past.</p>
<h2>Why all of this affects ordinary people</h2>
<p>SBP decisions ripple outward in concrete ways: a rate hike raises the cost of a car loan or mortgage but can improve returns on savings accounts and government securities; currency management affects the price of imported goods, from fuel to electronics; bank regulation is what makes it reasonably safe to keep money in a Pakistani bank account in the first place. None of this requires financial expertise to follow — understanding the basic mechanism makes SBP headlines meaningfully more useful than background noise.</p>
<h2>Independence from day-to-day government control</h2>
<p>Central banks, including the SBP, are generally structured with a degree of operational independence from the government's fiscal decisions specifically so monetary policy can respond to inflation and currency conditions without being driven by short-term political pressure. This separation is a deliberate design choice found in most modern economies, intended to keep interest rate decisions grounded in economic data rather than election cycles.</p>
<h2>Where to actually follow SBP announcements</h2>
<p>The SBP publishes monetary policy decisions, reserve data, and Governor statements directly on its official website, alongside regular economic bulletins — a more reliable primary source than secondhand summaries, especially around a scheduled policy rate announcement where the exact wording of the accompanying statement often matters as much as the rate decision itself.</p>
HTML,
            ],
            [
                'slug' => 'interest-rates-inflation-pakistan-monetary-policy',
                'title' => 'How Interest Rates and Inflation Are Connected: Pakistan\'s Monetary Policy Explained',
                'excerpt' => 'When inflation rises, interest rates usually follow — but why? Here is the actual mechanism connecting the SBP\'s policy rate to the prices you pay.',
                'meta_title' => 'Interest Rates and Inflation: How Monetary Policy Works',
                'meta_description' => 'How does raising interest rates actually control inflation? Here is the mechanism connecting SBP monetary policy decisions to everyday prices in Pakistan.',
                'tags' => ['Interest Rates', 'Inflation', 'Monetary Policy'],
                'body' => <<<'HTML'
<p class="lead">"The central bank raised rates to fight inflation" is a headline most people have read dozens of times without necessarily knowing the actual mechanism connecting the two. Here is how raising or cutting a single interest rate genuinely influences prices across an entire economy.</p>
<h2>The basic chain of cause and effect</h2>
<p>When the SBP raises its policy rate, borrowing becomes more expensive throughout the economy — banks raise the rates they charge on loans and mortgages, and often raise the rates they pay on savings accounts too. More expensive borrowing discourages businesses from taking loans to expand, and discourages consumers from financing large purchases, which cools overall spending in the economy.</p>
<h2>Why cooling spending fights inflation</h2>
<p>Inflation is, at its core, too much money chasing too few goods — when demand outpaces supply, prices rise. Cooling demand by making borrowing more expensive reduces that pressure, giving supply a chance to catch up without prices needing to climb as fast. It is a deliberately blunt tool: it slows spending broadly, not just in the specific sectors driving inflation.</p>
<h2>Why the effect takes time</h2>
<p>Interest rate changes do not affect prices immediately — the full effect typically takes many months to work through the economy, as existing loans reprice, new borrowing decisions get made, and spending patterns gradually shift. This is why central banks, including the SBP, often hold rates steady for a period after a change to assess how it's filtering through before adjusting again.</p>
<h2>The trade-off central banks are managing</h2>
<p>Raising rates to fight inflation also makes borrowing more expensive for genuinely productive purposes — a business wanting to expand, a family wanting to buy a home — which can slow economic growth and job creation. Central banks are constantly balancing this trade-off: move too aggressively and risk unnecessary economic pain; move too cautiously and risk inflation becoming entrenched and harder to reverse later.</p>
<h2>Why this matters for your own financial decisions</h2>
<p>Understanding this connection makes interest rate announcements genuinely useful rather than just background news: a rate hold after a period of high inflation often signals the central bank sees some progress; a fresh hike signals continued concern. Either way, the direction of policy rates is a reasonable signal for whether borrowing costs are likely to rise or ease in the near term — worth factoring into the timing of a major loan or mortgage decision if there's flexibility in when to commit.</p>
<h2>Why rates and inflation don't always move together perfectly</h2>
<p>Interest rates aren't the only thing affecting inflation — supply shocks (a bad harvest, an oil price spike, a currency depreciation raising import costs) can push prices up regardless of how tight monetary policy is, since raising rates does nothing to directly fix a supply-side problem. This is why inflation can stay elevated even after a series of rate hikes, and why central banks sometimes explicitly separate "supply-driven" inflation from "demand-driven" inflation when explaining a policy decision.</p>
<h2>What a rate cut signals, by contrast</h2>
<p>When inflation has cooled and economic growth looks weak, a central bank may cut rates to encourage borrowing and spending again — the same mechanism running in reverse. A cutting cycle generally signals confidence that inflation is under control, freeing the central bank to shift focus toward supporting growth and employment instead.</p>
HTML,
            ],
            [
                'slug' => 'what-weaker-rupee-actually-costs-you',
                'title' => 'What a Weaker Rupee Actually Costs You',
                'excerpt' => 'Currency depreciation gets reported as a percentage, which makes it easy to ignore. Translated into real costs on fuel, electronics and imports, it is not abstract at all.',
                'meta_title' => 'What Rupee Depreciation Actually Costs You',
                'meta_description' => 'A weaker rupee is usually reported as a percentage move, but it translates directly into higher costs for fuel, electronics and other imports. Here is how.',
                'tags' => ['Pakistani Rupee', 'Inflation', 'PKR Exchange Rate'],
                'body' => <<<'HTML'
<p class="lead">"The rupee fell 2% against the dollar this month" is the kind of headline that's easy to read past — a small-sounding percentage, no obvious connection to daily life. Translated into actual prices, currency depreciation is one of the more direct ways a macroeconomic shift reaches into an ordinary household budget.</p>
<h2>Why so much of what Pakistan buys is priced in dollars</h2>
<p>Pakistan imports the large majority of its crude oil, along with significant machinery, raw materials for local manufacturing, and many finished goods — nearly all priced internationally in US dollars regardless of where they're ultimately sold. When the rupee weakens against the dollar, the rupee cost of every one of those imports rises directly, with no other factor needing to change.</p>
<h2>The transmission from currency to your bill</h2>
<p>A weaker rupee raises the landed cost of imported fuel, which raises transport and production costs across the economy, which raises the price of many locally produced goods that depend on that fuel or on imported inputs — a chain reaction that spreads well beyond obviously imported items. This is one of the primary channels through which currency depreciation shows up as broad inflation, not just higher prices on a narrow list of imported goods.</p>
<h2>A concrete way to see the cost</h2>
<p>Take an imported item priced at $1,000. At a rate of 250 PKR per dollar, that's 250,000 rupees. At 280 PKR per dollar — a roughly 12% depreciation — the same item costs 280,000 rupees: an extra 30,000 rupees for an item whose actual foreign price never changed at all. Running real numbers through a depreciation-impact calculation turns an abstract percentage into a number that's much easier to actually reason about.</p>
<h2>Who feels this most</h2>
<p>Rupee depreciation affects everyone who buys imported or import-dependent goods, but it hits hardest for anyone with dollar-denominated obligations — overseas tuition fees, import-dependent small businesses, and anyone repaying foreign-currency debt — where the rupee cost of a fixed dollar obligation rises directly with the exchange rate, regardless of whether their own income rose at all.</p>
<h2>The other side: who benefits</h2>
<p>A weaker rupee isn't purely bad news for everyone — exporters earning dollars and converting them to rupees receive more rupees for the same dollar sale, and overseas Pakistanis sending remittances home see those dollars convert into more rupees too. Currency depreciation redistributes real costs and benefits across different groups rather than uniformly making the country poorer, even though the immediate headline usually frames it as a straightforward negative.</p>
HTML,
            ],
            [
                'slug' => 'how-much-house-can-you-actually-afford',
                'title' => 'How Much House Can You Actually Afford?',
                'excerpt' => 'Lenders qualify you for a certain mortgage amount using a debt-to-income formula — but the maximum they will lend and what you can comfortably afford are not the same number.',
                'meta_title' => 'How Much House Can You Actually Afford? A Practical Guide',
                'meta_description' => 'Mortgage lenders qualify buyers using a debt-to-income ratio, but the maximum loan they will offer and what is comfortable to actually pay are different numbers.',
                'tags' => ['Loans', 'Savings'],
                'body' => <<<'HTML'
<p class="lead">A lender will often approve a mortgage for more than most people should comfortably take on — qualification and genuine affordability are calculated differently, and confusing the two is one of the more common home-buying mistakes.</p>
<h2>How lenders calculate what you qualify for</h2>
<p>Most lenders cap total monthly debt payments — including the new mortgage — at a percentage of income, commonly somewhere between 36% and 43%. Existing debt payments (car loans, other loans, credit card minimums) get subtracted from that allowance first, and whatever remains determines the maximum mortgage payment, and therefore the maximum loan amount, at a given rate and term.</p>
<h2>Why the maximum isn't the same as the comfortable amount</h2>
<p>That debt-to-income formula doesn't account for savings goals, retirement contributions, an emergency fund, or simply the flexibility to handle a bad month without stress — it's calculated purely around whether you can technically make the payments without defaulting. Many financial planners suggest targeting a mortgage payment meaningfully below the lender's calculated maximum specifically to preserve room for everything else a budget needs to cover.</p>
<h2>What existing debt does to your number</h2>
<p>Two buyers with identical income can qualify for very different mortgage amounts if one carries meaningful existing debt. Paying down a car loan or clearing credit card balances before a mortgage application can increase what you qualify for — sometimes more effectively, and more within your direct control, than trying to save a larger down payment in the same timeframe.</p>
<h2>Costs beyond the mortgage payment itself</h2>
<p>The loan payment is only part of homeownership's real monthly cost — property tax, insurance, maintenance, and utilities (often higher for a larger home than a rental) all add up on top. Any affordability calculation based on loan payment alone understates the true cost of owning a specific home, which is worth building in as a buffer before committing to a number near the top of what a lender will approve.</p>
<h2>A practical way to set your own ceiling</h2>
<p>Rather than defaulting to the maximum a lender's formula allows, work backward from a monthly housing budget you're genuinely comfortable committing to for years, factoring in the other costs above, and use that as your actual ceiling — treating the lender's maximum as an upper bound to stay well under, not a target to reach.</p>
<h2>How interest rate changes shift the whole calculation</h2>
<p>Because affordability is calculated backward from a maximum monthly payment, the interest rate at the time of borrowing has an outsized effect on how much home that payment can actually finance — a few percentage points of rate difference can change the affordable loan amount by a significant margin, even with income and existing debt held constant. Rechecking affordability at the actual rate available when ready to borrow, rather than relying on an estimate made months earlier under different rate conditions, avoids planning around a number that's already gone stale.</p>
<h2>The down payment trade-off</h2>
<p>A larger down payment directly increases the affordable home price for a given monthly payment budget, but it also means committing more cash upfront to a single illiquid asset. Whether it's worth stretching the down payment further is a genuinely personal trade-off — weighed against maintaining an adequate emergency fund and not depleting savings needed for other near-term goals, not just against getting the biggest home price the math allows.</p>
HTML,
            ],
            [
                'slug' => 'understanding-gratuity-what-youre-owed',
                'title' => 'Understanding Gratuity: What You\'re Actually Owed When You Leave a Job',
                'excerpt' => 'Gratuity is a real, often significant benefit that gets overlooked until someone is actually leaving a job. Here is what it is, and what it commonly depends on.',
                'meta_title' => 'What Is Gratuity and What Determines How Much You Get?',
                'meta_description' => 'Gratuity is an end-of-service benefit based on salary and years worked. Here is what it typically depends on and why the exact formula varies by employer.',
                'tags' => ['Salary'],
                'body' => <<<'HTML'
<p class="lead">Gratuity is one of those employment benefits that stays abstract until the moment someone is actually leaving a job — at which point it can turn out to be a genuinely significant sum, and one worth understanding well before that moment arrives.</p>
<h2>What gratuity actually is</h2>
<p>Gratuity is a lump-sum benefit paid to an employee at the end of their service, typically calculated from their length of employment and their salary at the time of leaving. It's distinct from any provident fund savings (which accumulate from regular contributions over time) and distinct from final salary owed for time already worked — a separate benefit specifically tied to tenure.</p>
<h2>The two things that usually drive the amount</h2>
<p>Almost every gratuity formula depends on the same two core inputs: years of completed service, and salary (usually the "last drawn" salary at the point of leaving, though the exact definition — basic salary only, or a broader figure — varies by scheme). Longer tenure and a higher final salary both increase the payout, which is part of why gratuity tends to become a much larger consideration later in someone's career than early on.</p>
<h2>Why the exact formula varies so much</h2>
<p>Unlike a flat tax rate that applies uniformly, gratuity calculation methods differ meaningfully by country and even by individual employer policy within the same country. Some schemes use a full month's salary per year of service; others use a fraction of a month (commonly 15 or 26 days) per year. There is no single universal formula, which makes checking your specific employment contract or company policy essential rather than assuming a "standard" rate applies.</p>
<h2>Minimum service requirements</h2>
<p>Many gratuity schemes require a minimum period of continuous service — often a specific number of years — before any gratuity becomes payable at all. Leaving before that threshold, even after a substantial period of employment, can mean forfeiting the benefit entirely under some schemes, which makes the minimum threshold worth knowing well ahead of any decision to change jobs.</p>
<h2>Why it's worth checking early, not at exit</h2>
<p>Understanding your specific gratuity terms — the formula, the minimum service period, what "last drawn salary" actually includes — is far more useful before a job change decision than after, since it can meaningfully affect the financial trade-off of leaving at a particular point versus waiting. Reviewing your employment contract or asking HR directly, well before any planned transition, avoids an unwelcome surprise at the exact moment gratuity actually matters most.</p>
HTML,
            ],
        ];
    }
}
