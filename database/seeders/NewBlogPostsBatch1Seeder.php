<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * First batch of post-launch blog additions (Aug 2026 growth push): six
 * evergreen posts tied to the six NewToolsBatch1Seeder calculators and to
 * trends identified in keyword research (side hustles, debt payoff, FIRE,
 * net worth, budgeting, rental investing). Idempotent (updateOrCreate) so
 * it's safe to re-run on every deploy.
 */
class NewBlogPostsBatch1Seeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['type' => 'blog', 'slug' => 'money-investing'],
            ['name' => 'Money & Investing', 'tagline' => 'Saving, debt, budgeting and investing basics, explained without jargon.', 'order' => 2]
        );

        $editors = User::where('role', 'editor')->pluck('id');

        foreach ($this->posts() as $i => $post) {
            $record = Post::updateOrCreate(
                ['type' => 'blog', 'slug' => $post['slug']],
                [
                    'category_id' => $category->id,
                    'author_id' => $editors->isNotEmpty() ? $editors[$i % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'status' => 'published',
                    'published_at' => now()->subDays(6 - $i),
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
                'slug' => 'gen-z-side-hustle-boom',
                'title' => 'Gen Z and the Side Hustle Boom: Why 58% Have a Second Income',
                'excerpt' => 'More than half of Gen Z now earns money outside a main job. Here is what "income stacking" actually looks like, and what it means for how you budget.',
                'meta_title' => 'Gen Z Side Hustles: Why 58% Have a Second Income Stream',
                'meta_description' => 'Side hustles are now the norm, not the exception, for Gen Z — 58% report having one. Here is why, what a "stacked" income actually looks like, and how to budget for it.',
                'tags' => ['Gen Z', 'Freelancing', 'Side Hustle'],
                'body' => <<<'HTML'
<p class="lead">Having one job used to be the default. For Gen Z, it increasingly isn't: 58% report having some form of side hustle, more than double the rate among Baby Boomers. This isn't a story about hustle-culture ambition alone — it's mostly about arithmetic.</p>
<h2>Why "income stacking" became normal</h2>
<p>Surveys consistently find the same top reason people give for a side hustle: keeping up with inflation and the rising cost of living, cited by over half of respondents, ahead of building savings, paying off debt, or pursuing a passion project. When one income doesn't comfortably cover rent, groceries and the rest, adding a second, smaller income stream is often a faster fix than waiting for a raise.</p>
<p>The gig economy and remote-friendly freelance platforms lowered the barrier to entry considerably compared to a generation ago — tutoring, freelance design, content creation, delivery driving and reselling are all a phone and a few hours away, without the overhead of starting a traditional small business.</p>
<h2>What a stacked income does to your budgeting</h2>
<p>Two things change when you have more than one income source. First, your total monthly income becomes less predictable — a freelance gig might pay well one month and nothing the next, unlike a fixed salary. Second, tax treatment usually differs: side income is frequently not withheld at source the way a salary is, so an unplanned tax bill is a common surprise for first-time side hustlers.</p>
<p>The practical fix for both is the same: budget against your <em>lowest realistic month</em>, not your average or best month, and set aside a fixed percentage of every side-hustle payment for tax before you spend any of it — treat it as already gone, not as extra spending money.</p>
<h2>The other side of the number</h2>
<p>It's worth being honest about the trade-off too. Gen Z is also the generation most likely to describe itself as exhausted by the pressure to constantly earn more, and a side hustle taken on purely out of necessity is a different experience from one chosen for passion or flexibility. A second income stream is a tool for closing a gap between income and cost of living — it isn't, by itself, a fix for costs that are structurally too high relative to income.</p>
<h2>The motivations, ranked</h2>
<p>When people are asked why they picked up a side hustle, the order is fairly consistent across surveys: keeping up with rising costs comes first, followed by building savings, paying off debt, pursuing something they actually enjoy, and — for a smaller group — transitioning toward full-time self-employment. Notice that "getting rich" barely features. Most side hustles are closing a specific, near-term gap rather than chasing a long-shot business idea.</p>
<h2>What to check before you commit to one</h2>
<p>A few questions are worth answering honestly before taking on a side hustle, not after: how many hours a week can you sustain without it eating into sleep, your main job's performance, or your health? Does the platform or client pay reliably and on a predictable schedule? And critically — have you checked whether the income is taxable, and at what rate, in your specific situation? Side income surprising someone with an unplanned tax bill months later is one of the most common and avoidable side-hustle mistakes.</p>
<h2>Making the numbers work together</h2>
<p>The cleanest way to manage a stacked income is to keep it in a genuinely separate account from your main salary, and to treat that account as "gross," not "spendable" — move the tax-reserve percentage out first, then treat only the remainder as real income. This one habit avoids the two most common side-hustle problems: spending money that was actually owed to the tax authority, and losing track of whether the hustle is genuinely profitable once time and any expenses are properly accounted for.</p>
HTML,
            ],
            [
                'slug' => 'debt-snowball-vs-avalanche',
                'title' => 'Debt Snowball vs. Avalanche: Which Payoff Method Actually Saves More',
                'excerpt' => 'Two popular strategies for paying off multiple debts give very different results — one saves more money, the other is easier to stick with. Here is the actual math.',
                'meta_title' => 'Debt Snowball vs Avalanche: Which Saves More Money?',
                'meta_description' => 'Snowball pays off the smallest balance first; avalanche targets the highest interest rate first. Here is how the two methods compare in total interest paid and time to debt-free.',
                'tags' => ['Debt', 'Savings'],
                'body' => <<<'HTML'
<p class="lead">If you have more than one debt — a credit card, a personal loan, maybe a student loan — the order you pay them off in changes both how much interest you pay overall and how it feels along the way. Two methods dominate the advice: snowball and avalanche.</p>
<h2>The avalanche method</h2>
<p>Avalanche puts every spare rupee toward whichever debt has the <strong>highest interest rate</strong>, while paying only the minimum on everything else. Once the highest-rate debt is cleared, you roll its entire payment into the next-highest-rate debt, and so on. Mathematically, this is the cheapest way to clear multiple debts — every rupee of extra payment goes toward the debt currently costing you the most.</p>
<h2>The snowball method</h2>
<p>Snowball instead targets the <strong>smallest balance</strong> first, regardless of its interest rate, again paying only minimums elsewhere. The appeal isn't the math — it's momentum. Clearing an entire debt, even a small one, produces a visible win faster, which behavioral research on debt repayment has found meaningfully improves the odds that people stick with a payoff plan through to the end.</p>
<h2>Which one actually saves more?</h2>
<p>Avalanche wins on total interest paid, every time, by definition — it's mathematically optimal. The gap between the two methods grows with the difference in interest rates across your debts: if a high-interest credit card sits alongside a low-interest loan, avalanche's advantage is significant. If all your debts carry similar rates, the two methods end up close to identical in total cost, and the choice becomes mostly about which keeps you motivated.</p>
<p>Try running your own debt through a payoff calculator at its actual rate and payment — the time-to-payoff and total-interest figures make the trade-off concrete rather than theoretical.</p>
<h2>A practical middle ground</h2>
<p>Some people use a hybrid: snowball the smallest debt first for an early motivational win, then switch to avalanche ordering for everything after that. There's no rule that says the method has to be pure — the "best" method is ultimately whichever one you'll actually follow through on until every balance reaches zero.</p>
<h2>A worked comparison</h2>
<p>Picture three debts: a credit card at 300,000 and 24% APR, a personal loan at 150,000 and 15% APR, and a small store card at 40,000 and 30% APR. Under avalanche, every spare rupee goes to the store card first (highest rate), then the credit card, then the personal loan. Under snowball, the order flips to store card, then personal loan, then credit card, based purely on balance size rather than rate. In this example the two methods actually agree on which debt to tackle first, since the store card is both the smallest balance and the highest rate — a useful reminder that the two strategies often overlap more than the framing suggests.</p>
<h2>What actually derails a payoff plan</h2>
<p>The research behind the snowball method's popularity is not really about interest math; it is about behavior. Multi-debt payoff plans most commonly fail not because the wrong debt was targeted first, but because momentum stalls after months with no visible progress. A method that clears one full debt sooner, even at a small mathematical cost, can be worth it if it is the difference between finishing the plan and abandoning it halfway.</p>
<h2>The one thing that matters more than either method</h2>
<p>Whichever order is chosen, the total extra payment amount matters far more than the sequencing. Doubling the extra monthly payment cuts both time and total interest more than switching between snowball and avalanche ever will. If there is spare budget to find — cutting a subscription, a temporary side income — putting it toward whichever method is already chosen beats agonizing over which method is theoretically a few percent more efficient.</p>
HTML,
            ],
            [
                'slug' => 'what-is-your-fire-number',
                'title' => 'What Is Your FIRE Number? A Plain-English Guide to Early Retirement Math',
                'excerpt' => 'FIRE — Financial Independence, Retire Early — comes down to one number: how big a portfolio you would need to stop needing a paycheck. Here is how it is actually calculated.',
                'meta_title' => 'FIRE Number Explained: The Math Behind Early Retirement',
                'meta_description' => 'Your FIRE number is roughly 25 times your annual expenses, based on a 4% safe withdrawal rate. Here is where that math comes from and how to calculate your own.',
                'tags' => ['Investing', 'Savings'],
                'body' => <<<'HTML'
<p class="lead">FIRE — Financial Independence, Retire Early — sounds like a lifestyle movement, and it partly is, but underneath it is a specific, calculable number: the portfolio size that could theoretically support your spending indefinitely without you needing to earn a salary.</p>
<h2>The 25x rule</h2>
<p>The shortcut most FIRE planners use is 25 times your annual expenses. If you spend 1,200,000 a year, your FIRE number is roughly 30,000,000. This comes directly from a 4% withdrawal rate: withdrawing 4% a year is mathematically the same as needing a portfolio 25 times your annual spending (1 ÷ 0.04 = 25).</p>
<h2>Where the 4% figure comes from</h2>
<p>It traces back to research — most famously the "Trinity Study" — that tested how a diversified stock-and-bond portfolio would have historically performed across many rolling 30-year retirement periods. A 4% starting withdrawal rate, adjusted for inflation each year after, held up in the large majority of those historical periods without the portfolio running out. It's an evidence-based rule of thumb, not a mathematical guarantee for any specific future.</p>
<h2>Why some people use a lower number</h2>
<p>A retirement lasting longer than 30 years — which is exactly the point of retiring early — carries more risk of an unusually bad sequence of market returns early on depleting the portfolio faster than the historical average suggests. Many FIRE planners use 3.5% or even 3% instead of 4% specifically to build in that extra safety margin, which raises the target portfolio size but lowers the risk of running out.</p>
<h2>The number is a target, not a finish line</h2>
<p>Reaching your FIRE number doesn't require quitting work entirely — plenty of people treat it as the point where work becomes optional rather than necessary, which changes the leverage they have in salary negotiations, job choices, and how much financial stress a bad month at work causes. Calculating the number is useful even if "retiring early" isn't the specific goal — it reframes "how much do I need to save" into a concrete, checkable target instead of an open-ended anxiety.</p>
<h2>Working the calculation backward</h2>
<p>Start from annual expenses rather than income, since expenses are what the portfolio actually needs to cover. Track a few months of real spending, annualize it, then divide by the chosen withdrawal rate. A common mistake is using current income as a stand-in for future expenses — but retirement spending patterns often differ substantially from working-life spending, for better (no more commuting or work wardrobe costs) or worse (more healthcare spending, more travel).</p>
<h2>The savings rate does most of the work</h2>
<p>The single biggest lever in how fast someone reaches their FIRE number is not investment returns — it is savings rate, because a higher savings rate simultaneously grows the portfolio faster and shrinks the annual-expense figure the portfolio needs to cover. Someone saving 50% of income needs to fund a lifestyle that costs roughly the other 50%, which is a fundamentally smaller and faster target than someone saving 10% while spending the remaining 90%.</p>
<h2>Coast FIRE and Barista FIRE</h2>
<p>Not every version of financial independence means fully stopping paid work. "Coast FIRE" describes having enough invested early enough that compound growth alone, with no further contributions, would reach the full FIRE number by a normal retirement age — freeing up current income for other priorities. "Barista FIRE" describes having enough to cover most expenses, with a smaller, lower-stress part-time income covering the rest. Both are variations on the same underlying math, just with a different definition of the finish line.</p>
HTML,
            ],
            [
                'slug' => 'how-to-calculate-net-worth',
                'title' => 'How to Calculate Your Net Worth (And Why It Matters More Than Your Salary)',
                'excerpt' => 'Salary measures income. Net worth measures actual financial position — and the two can tell very different stories about the same person.',
                'meta_title' => 'How to Calculate Net Worth: Assets Minus Liabilities Explained',
                'meta_description' => 'Net worth — assets minus liabilities — is a better measure of financial health than salary alone. Here is how to calculate it and why the trend matters more than any single number.',
                'tags' => ['Savings', 'Debt'],
                'body' => <<<'HTML'
<p class="lead">A high salary and a strong financial position are not the same thing. Someone earning well but spending it all — or carrying significant debt — can have a lower net worth than someone earning less but saving consistently. Net worth is the number that actually captures financial position.</p>
<h2>The calculation</h2>
<p>Net worth is total assets minus total liabilities. Assets include cash and savings, investments, and the resale value of property or vehicles. Liabilities include everything you owe: loans, credit card balances, and any other outstanding debt. Subtract one from the other, and the result — positive or negative — is your net worth at that moment.</p>
<h2>Why a negative number isn't a crisis</h2>
<p>Fresh graduates with student loans, or new homeowners who just took on a mortgage, very commonly have a negative net worth, and it says almost nothing bad about their financial trajectory on its own. What matters far more than the number on any single day is the <em>direction</em> it's moving over months and years — is it climbing, even slowly, or stuck, or getting worse?</p>
<h2>Why it beats salary as a health check</h2>
<p>Salary measures inflow. It says nothing about what happens to that money after it arrives — how much gets saved, invested, or lost to high-interest debt. Two people earning identical salaries can end a year in completely different financial positions depending on spending habits, debt load, and whether they're investing the difference. Net worth captures the outcome of all of that, not just the input.</p>
<h2>How often to check it</h2>
<p>Quarterly or twice a year is enough for most people. Net worth moves slowly by nature — assets and debts don't shift dramatically week to week — and checking too frequently, especially with market-linked investments in the mix, tends to produce more anxiety than insight without adding useful information.</p>
<h2>A simple way to track it over time</h2>
<p>A basic spreadsheet with one row per check-in and columns for each asset and liability category is enough — there is no need for specialized software to get the core benefit. What matters is consistency: use the same valuation approach each time (the same method for estimating property or vehicle value, for example) so that changes reflect real financial movement rather than a change in how something was measured.</p>
<h2>What tends to move the number fastest</h2>
<p>Early in a working life, debt reduction usually moves net worth faster than investment growth, simply because the debt balances are often larger relative to income than the investment balances are. As investments grow over time, their contribution to net worth changes tends to overtake debt reduction — which is one practical argument for tackling high-interest debt aggressively early on, since it is both the highest guaranteed return available and the fastest lever on net worth in the early years.</p>
<h2>Net worth by age is a benchmark, not a verdict</h2>
<p>Various studies publish median net worth by age bracket, and it is tempting to compare against them. Treat these cautiously: they are pulled from national averages that may not reflect local cost of living, family financial support, or career stage, and a below-median number in your twenties is extremely common and not a useful predictor of where the trend goes from here.</p>
HTML,
            ],
            [
                'slug' => '50-30-20-budget-rule-explained',
                'title' => 'The 50/30/20 Budget Rule Explained: Does It Still Work in 2026?',
                'excerpt' => 'The 50/30/20 rule splits income into needs, wants and savings. It is simple to apply — but rising living costs have made the split harder to actually hit for many people.',
                'meta_title' => '50/30/20 Budget Rule: How It Works and Its 2026 Limits',
                'meta_description' => 'The 50/30/20 budgeting rule splits take-home income into 50% needs, 30% wants, 20% savings. Here is how it works, and why it does not fit everyone in 2026.',
                'tags' => ['Savings', 'Cost of Living'],
                'body' => <<<'HTML'
<p class="lead">The 50/30/20 rule is one of the most widely repeated budgeting frameworks, and for good reason — it's simple enough to apply without spreadsheets. Split take-home income into 50% needs, 30% wants, and 20% savings and debt repayment. But whether that split is realistic depends heavily on where you live.</p>
<h2>What counts as a "need"</h2>
<p>The category is meant to cover genuine non-negotiables only: rent or mortgage, groceries, utilities, transport to work, and minimum debt payments. The test isn't whether something feels routine — it's whether skipping it would cause a real consequence like eviction or losing your job. A streaming subscription or daily coffee, however habitual, belongs in "wants," not "needs."</p>
<h2>Where the rule breaks down</h2>
<p>In high cost-of-living cities, needs alone can exceed 50% of take-home income no matter how carefully spending is managed — rent by itself can consume a third or more of income in many major cities today. When that happens, treating 50/30/20 as a strict requirement just produces guilt over a target that was never realistic to begin with.</p>
<h2>A more useful way to use it</h2>
<p>Rather than a hard rule, treat 50/30/20 as a direction to work toward: if needs are currently at 65%, the goal becomes gradually shrinking that share — a cheaper apartment, a side income, refinancing debt — rather than forcing wants and savings into an impossible remainder. The one piece worth protecting no matter what: keep something, even a small percentage, flowing into savings and debt repayment every single month, rather than letting it drop to zero when needs run high.</p>
<h2>Adjusting the split to your situation</h2>
<p>Some budgeters use 60/20/20 or 70/20/10 in expensive cities, then shift back toward 50/30/20 as income grows or costs fall. The percentages are a tool for thinking clearly about trade-offs, not a test to pass or fail.</p>
<h2>The origin of the rule</h2>
<p>The 50/30/20 split was popularized by Senator Elizabeth Warren and Amelia Warren Tyagi in a widely read book on family finances, built around research into what distinguished households that stayed financially stable from those that fell into debt cycles. The core insight was not the exact percentages — it was the idea that separating "must-pay" from "flexible" spending, and protecting a savings category as non-negotiable, produces materially better outcomes than budgeting without categories at all.</p>
<h2>Making the wants category actually work</h2>
<p>The wants category is where most budgets quietly fail, not because people overspend deliberately, but because "wants" spending tends to happen in small, easy-to-miss amounts — a coffee here, a subscription there — that only become visible when totalled at the end of the month. Reviewing card and account statements against the wants category after the fact, rather than trying to track every purchase in the moment, tends to be more sustainable for most people.</p>
<h2>What replaces the rule when it does not fit</h2>
<p>For anyone whose needs genuinely exceed 50% with no realistic way to shrink them soon, a "pay yourself first" approach can work better than percentage splits: decide a fixed savings amount, however small, move it out automatically the day income arrives, and let needs and wants share whatever remains without a strict ratio between them. The guaranteed savings habit matters more than hitting any particular percentage.</p>
HTML,
            ],
            [
                'slug' => 'rental-property-investing-101',
                'title' => 'Rental Property Investing 101: What Rental Yield Actually Tells You',
                'excerpt' => 'Rental yield is the most-quoted number in property investing, but gross and net yield can tell very different stories about the same property. Here is how to read it properly.',
                'meta_title' => 'Rental Yield Explained: Gross vs Net Yield for Property Investors',
                'meta_description' => 'Rental yield measures a property\'s income return relative to its price. Learn the difference between gross and net yield, and what yield does not tell you.',
                'tags' => ['Investing'],
                'body' => <<<'HTML'
<p class="lead">Rental yield is the single most-quoted figure in property investing conversations, and also one of the most commonly misunderstood. It measures a property's rental income relative to its price — but which version of "yield" gets quoted changes the picture significantly.</p>
<h2>Gross yield: the quick, rough number</h2>
<p>Gross yield is simply annual rental income divided by the property's price. It's fast to calculate and useful for a first-pass comparison across multiple properties, but it ignores every ongoing cost of actually owning the property — maintenance, property tax, insurance, and management fees if you use an agent.</p>
<h2>Net yield: the more honest number</h2>
<p>Net yield subtracts those annual running costs from the rental income before dividing by the price. Two properties with an identical gross yield can have very different net yields depending on their upkeep costs — an older property needing frequent repairs will often look far less attractive once running costs are factored in, even if its headline gross yield looked competitive.</p>
<h2>What yield leaves out entirely</h2>
<p>Yield says nothing about capital appreciation — whether the property's value itself is likely to rise or fall over the holding period — nor does it factor in financing costs if the purchase is mortgaged rather than bought outright. A property with a modest yield in a rapidly appreciating area can still outperform a high-yield property in a stagnant one, once total return (yield plus appreciation) is considered.</p>
<h2>Yield also doesn't price in risk</h2>
<p>A high yield can be a genuine bargain — or a signal that the market is pricing in more risk than the yield figure alone reveals: a higher-crime area, an oversupplied rental market, or a property type prone to costly repairs. Rental yield is a useful first filter for comparing properties, not a complete investment case on its own.</p>
<h2>How yield compares across property types</h2>
<p>Smaller, lower-priced units — studios and one-bedroom apartments in particular — tend to post higher yields than larger family homes, mainly because rental demand per square foot is generally stronger at the smaller end of the market while purchase prices scale down faster than rents do. This is not a rule without exceptions, but it explains why yield-focused investors are disproportionately drawn to compact units over larger properties, even when the larger property might appreciate more in value over time.</p>
<h2>Financing changes the real return, even though yield ignores it</h2>
<p>Because yield calculations here assume a cash purchase, a mortgaged buyer's actual cash-on-cash return can look very different — often higher, through leverage, but also more exposed if rents dip or interest rates rise on a variable mortgage. Two investors buying the identical property at the identical yield can end up with very different real outcomes purely based on how the purchase was financed, which is exactly why yield is a comparison tool between properties, not a complete answer for any one buyer's situation.</p>
<h2>A practical way to use the number</h2>
<p>Calculate net yield for every property under serious consideration, using consistent expense assumptions across all of them, and treat the resulting ranking as a shortlist filter rather than a final decision. From there, the remaining questions — expected appreciation, local rental demand trends, and how the purchase will be financed — determine which property on the shortlist is actually the better investment.</p>
HTML,
            ],
        ];
    }
}
