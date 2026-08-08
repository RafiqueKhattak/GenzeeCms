<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Second batch of post-launch blog additions (Aug 2026 growth push): six
 * evergreen posts tied to the six NewToolsBatch2Seeder calculators.
 * Idempotent (updateOrCreate) so it's safe to re-run on every deploy.
 */
class NewBlogPostsBatch2Seeder extends Seeder
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
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 5) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'status' => 'published',
                    'published_at' => now()->subDays(12 - $i),
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
                'slug' => 'how-loan-amortization-actually-works',
                'title' => 'How Loan Amortization Actually Works (And Why Early Extra Payments Matter More)',
                'excerpt' => 'Every fixed loan payment splits between interest and principal — and that split shifts dramatically over the life of the loan. Here is why it matters for how you pay extra.',
                'meta_title' => 'How Loan Amortization Works: Principal vs Interest Explained',
                'meta_description' => 'Amortization splits every loan payment between interest and principal, and that split shifts over time. Here is why extra payments made early save more than the same amount later.',
                'tags' => ['Loans', 'Debt'],
                'body' => <<<'HTML'
<p class="lead">A loan payment looks like one fixed number every month, but underneath it is doing two different jobs at once: covering that period's interest, and reducing the actual balance owed. How much goes to each changes dramatically over the life of the loan — and understanding that shift changes how you should think about extra payments.</p>
<h2>Why the split isn't fixed</h2>
<p>Interest is calculated on the <em>remaining balance</em>, not on the original loan amount. Early in a loan, when the balance is at its highest, interest eats a larger share of each payment, leaving less to reduce the principal. As payments continue and the balance shrinks, less interest accrues each period, so a growing share of the same fixed payment goes toward principal instead.</p>
<h2>A concrete example</h2>
<p>On a 3,000,000 loan at 12% over 5 years, the monthly payment works out to roughly 66,700. In month one, a large chunk of that payment is interest on the full 3,000,000 balance — only a modest slice actually reduces the principal. By the final year, the balance is much smaller, so almost the entire payment goes toward principal, with only a small interest charge left. The total payment never changes; only its internal split does.</p>
<h2>Why this makes early extra payments more powerful</h2>
<p>An extra payment made in month one immediately reduces the balance that every future month's interest is calculated against — for the entire remaining life of the loan. The same extra amount paid in the final year has almost no remaining interest left to save, since the balance is already nearly paid off. This is the mathematical reason financial advice consistently favors extra payments as early as possible over waiting.</p>
<h2>What this means practically</h2>
<p>If you have any flexibility in when to make extra payments on a loan, front-loading them — even a modest amount in the first year or two — captures more total interest savings than spreading the same extra money evenly across the loan's full term. Checking an amortization schedule for your specific loan makes this concrete instead of theoretical, showing exactly how much interest a given extra payment would save based on when it's made.</p>
<h2>Refinancing resets the clock</h2>
<p>Refinancing a loan partway through its term restarts amortization from the beginning of a new schedule — even though the outstanding balance may be similar to where the original loan currently sits. This is why refinancing purely to chase a slightly lower rate late in a loan's life can sometimes cost more in total interest than staying the course, once the reset to an early, interest-heavy amortization curve is factored in. Comparing total interest remaining on the current loan against total interest on the proposed new one, not just the interest rate, is the only reliable way to judge whether a refinance is actually worth it.</p>
<h2>Fixed-rate versus adjustable-rate amortization</h2>
<p>Everything above assumes a fixed interest rate for the life of the loan. Adjustable-rate loans complicate the picture because the amortization schedule itself changes whenever the rate resets — a rate increase part-way through can mean a larger share of upcoming payments reverts to interest for a period, even after years of steady principal progress. Anyone comparing a fixed-rate and adjustable-rate loan side by side should model amortization under both a stable-rate and a rising-rate scenario, not just today's rate.</p>
HTML,
            ],
            [
                'slug' => 'markup-vs-margin-pricing-mistake',
                'title' => 'Markup vs. Margin: The Pricing Mistake That Costs Small Businesses Money',
                'excerpt' => 'A 50% markup and a 50% margin are not the same thing — and confusing them can leave real money on the table. Here is the difference, with numbers.',
                'meta_title' => 'Markup vs Margin: What the Difference Actually Costs You',
                'meta_description' => 'Markup is a percentage of cost; margin is a percentage of selling price. Confusing the two is a common small-business pricing mistake — here is the actual math.',
                'tags' => ['Investing'],
                'body' => <<<'HTML'
<p class="lead">Ask someone to price a product with "50% profit" and you'll get two different answers depending on whether they're thinking in markup or margin — and the gap between those answers is real money, not a rounding difference.</p>
<h2>The core difference</h2>
<p>Markup is calculated as a percentage of <strong>cost</strong>. Margin is calculated as a percentage of <strong>selling price</strong>. They sound similar and are often used interchangeably in casual conversation, but they describe the same sale from two different reference points, and the resulting percentages are never equal except at 0%.</p>
<h2>The numbers, side by side</h2>
<p>Take a product that costs 700 to produce or acquire. A 50% <em>markup</em> adds 350 (50% of 700) on top, giving a selling price of 1,050. Check the resulting margin on that same sale: 350 profit divided by 1,050 selling price is only 33.3%, not 50%. To actually achieve a 50% <em>margin</em> from the same 700 cost, the selling price would need to be 1,400 — a full 350 more than the markup-based price.</p>
<h2>Where the mistake actually costs money</h2>
<p>A business owner who wants a 50% margin but prices using a 50% markup formula will consistently under-price every product, without realizing it, because the two calculations were never the same to begin with. Over hundreds or thousands of transactions, that gap compounds into a meaningfully smaller bottom line than intended — not because of poor sales, but because the pricing formula itself targeted the wrong number from the start.</p>
<h2>Which one should you actually use?</h2>
<p>Neither is universally "correct" — they answer different questions. Markup is convenient at the point of pricing an individual item, since it starts from a cost you already know. Margin is more useful for evaluating overall business profitability, since it directly represents what share of revenue is profit. Many businesses use markup to set prices day to day, then check margin at the end of a period to see how the business is actually performing — as long as everyone involved knows which one is being discussed.</p>
<h2>A quick way to convert between the two</h2>
<p>If a target margin is known and the equivalent markup is needed, the conversion is straightforward: markup percentage equals margin divided by (1 minus margin), both expressed as decimals. A 50% margin target (0.50) becomes a required markup of 0.50 ÷ 0.50 = 1.00, or 100% markup — meaning the selling price must be double the cost, not 1.5 times it, to actually hit a 50% margin. This formula is worth keeping on hand for anyone who sets prices using markup but reports results using margin.</p>
<h2>Why the confusion persists</h2>
<p>Part of the problem is language: both terms get shortened to "margin" or "markup" in casual conversation without the base being stated explicitly, and both are commonly discussed simply as "profit percentage." Standardizing on one term internally — and always stating explicitly which base a percentage is measured against — removes the ambiguity that causes real pricing errors to slip through unnoticed until a margin review reveals the business is earning less than assumed.</p>
HTML,
            ],
            [
                'slug' => 'how-big-should-emergency-fund-be',
                'title' => 'How Big Should Your Emergency Fund Actually Be?',
                'excerpt' => 'The standard advice is "3 to 6 months of expenses" — but that range hides a lot of nuance depending on how stable your income actually is.',
                'meta_title' => 'How Much Should Be in Your Emergency Fund?',
                'meta_description' => 'The standard 3-6 months of expenses rule for emergency funds depends heavily on income stability. Here is how to pick the right target for your situation.',
                'tags' => ['Savings'],
                'body' => <<<'HTML'
<p class="lead">"Save three to six months of expenses" is one of the most repeated pieces of financial advice, and also one of the vaguest — it treats a stable salaried employee and a commission-based freelancer as if they face identical risk, which they clearly don't.</p>
<h2>What actually goes in the target</h2>
<p>An emergency fund is sized around essential expenses, not full normal spending: rent or mortgage, groceries, utilities, transport, insurance, and minimum debt payments. It deliberately excludes discretionary spending — dining out, subscriptions, entertainment — because in a genuine emergency, those would be the first things cut, not funded from savings.</p>
<h2>Why the range spans 3 to 6 months</h2>
<p>Three months is generally considered a reasonable floor for someone with stable, predictable income and no dependents relying solely on them — enough to cover a typical short job search. Six months or more is more commonly recommended for less predictable situations: freelance or commission-based income, a household relying on a single income, or a field where finding comparable work historically takes longer.</p>
<h2>Where it should actually be kept</h2>
<p>The entire value of an emergency fund is guaranteed availability at full value, exactly when it's needed — which rules out most investment accounts, since they can be down in value at precisely the wrong moment. A standard savings account, ideally one with a small amount of friction to withdraw from (discouraging casual dipping into it for non-emergencies), is the conventional choice over anything market-linked.</p>
<h2>Building it without stalling everything else</h2>
<p>A common mistake is treating the emergency fund as a prerequisite that must be fully funded before any other financial goal — debt payoff, retirement contributions — can start. In practice, many financial planners suggest building a smaller starter fund (one month's expenses) quickly, then working on high-interest debt and the full emergency fund target in parallel, since both are working toward the same underlying goal: financial resilience against the unexpected.</p>
<h2>Adjusting the target as life changes</h2>
<p>An emergency fund target is not a one-time calculation — it should move when essential expenses or income stability change. Taking on a mortgage, having a dependent, or shifting from salaried to freelance income are all events that typically raise the target, either because monthly essentials increased or because income became less predictable. Revisiting the target roughly once a year, or after any major life change, keeps it aligned with actual current risk rather than a figure calculated years earlier under different circumstances.</p>
<h2>What "using" the fund actually looks like</h2>
<p>The hardest part for many people is not building the fund — it's using it without guilt when a genuine emergency arrives. A fund that's never touched despite a real qualifying event (job loss, an unavoidable major repair, an unexpected medical cost) isn't serving its purpose; it's just idle cash. Treating a withdrawal for a genuine emergency as the fund working exactly as intended, then rebuilding it afterward, is a healthier mental model than treating the balance itself as the goal.</p>
HTML,
            ],
            [
                'slug' => 'how-to-price-freelance-rate-properly',
                'title' => 'How to Actually Price Your Freelance Rate (Not Just Salary ÷ Hours)',
                'excerpt' => 'Dividing a target salary by 2,080 working hours is the single most common freelance pricing mistake — and it undercharges by a wide margin. Here is the real math.',
                'meta_title' => 'How to Calculate Your Freelance Hourly Rate Properly',
                'meta_description' => 'Dividing salary by total work hours undercharges freelancers badly. Here is how to price a freelance hourly rate accounting for tax, expenses and non-billable time.',
                'tags' => ['Freelancing'],
                'body' => <<<'HTML'
<p class="lead">A common way new freelancers set their rate: take a target annual salary, divide by 2,080 (a standard 40-hour work year), and charge that. It feels logical. It is also reliably too low, sometimes by more than half.</p>
<h2>Where the simple math breaks down</h2>
<p>Salary-divided-by-hours assumes every working hour is billable, that no tax is owed beyond what a salaried employee pays, and that running the business itself costs nothing. None of those assumptions hold for an independent freelancer, and each one pushes the real required rate meaningfully higher than the naive calculation suggests.</p>
<h2>Non-billable time is real time</h2>
<p>Admin, invoicing, marketing, pitching for new work, and ongoing skill development all take genuine hours but generate no direct revenue. Most freelancers can realistically bill somewhere between 50% and 70% of their total working hours — the rest is the unavoidable overhead of running an independent business, and unlike a salaried job, nobody else is covering that time.</p>
<h2>Business expenses and tax both come out of revenue</h2>
<p>Software subscriptions, equipment, a share of workspace costs, and other business expenses reduce what a given rate actually nets before tax is even considered. Freelance income is also frequently taxed differently — and often less favorably in terms of withholding — than salaried income, and self-employed individuals typically don't get an employer covering part of their tax burden the way salaried employees implicitly do.</p>
<h2>The corrected approach</h2>
<p>Start from the number that actually matters: desired take-home pay after tax and expenses. Gross that figure up for business expenses, then gross it up again for tax, and only then divide by <em>realistic billable hours</em> — not total working hours. On a 2,400,000 target income with 15% expenses and 20% tax, spread across 1,200 realistic billable hours a year, the rate that actually delivers the target works out meaningfully higher than a naive salary-divided-by-hours calculation would suggest — often the gap that explains why many new freelancers feel constantly busy but still underpaid.</p>
<h2>Revisiting the rate as the business changes</h2>
<p>A rate calculated once at the start of a freelance career should not stay fixed indefinitely. As billable-hour ratios improve with better systems and fewer wasted admin hours, or as expenses and tax obligations shift, the underlying numbers feeding the calculation change too — recalculating annually, or whenever a major cost changes, keeps the rate aligned with reality rather than anchored to assumptions made years earlier.</p>
<h2>Rate versus what the market will actually pay</h2>
<p>This calculation produces the rate needed to hit a specific income target — it does not guarantee clients will pay it. If the calculated rate sits well above what comparable freelancers in a given market or niche charge, the more sustainable fix is usually addressing the inputs (working more efficiently to raise the billable-hour ratio, targeting higher-value clients, specializing) rather than accepting a rate that structurally cannot deliver the intended income no matter how much work comes in.</p>
HTML,
            ],
            [
                'slug' => 'what-inflation-does-to-uninvested-money',
                'title' => 'What Inflation Really Does to Money You Are Not Investing',
                'excerpt' => 'Cash sitting in a low-interest account feels safe, but it can quietly lose real value every year. Here is the actual math behind purchasing-power erosion.',
                'meta_title' => 'How Inflation Erodes Cash Savings: The Real Math',
                'meta_description' => 'Cash earning less than the inflation rate loses real purchasing power every year, even while the number on the statement stays the same or grows. Here is what that actually costs.',
                'tags' => ['Inflation', 'Savings'],
                'body' => <<<'HTML'
<p class="lead">Cash in a savings account feels like the safest possible place for money — nothing can make the number go down. But "the number" and "what the number can actually buy" are two different things, and inflation quietly separates them every single year.</p>
<h2>The mechanism, in plain terms</h2>
<p>Inflation means the average price of goods and services rises over time. If prices rise 8% in a year and your savings earned nothing, the same amount of money buys roughly 8% less than it did twelve months earlier. The account balance is unchanged — the purchasing power behind it has shrunk.</p>
<h2>What this looks like over a longer horizon</h2>
<p>At 8% average annual inflation, 100,000 today would only have the purchasing power of roughly 46,300 in ten years' time, measured in today's terms. That is not a hypothetical worst case — it is simple compounding working in reverse against a static sum of money, the same math that makes investment growth powerful, just running the opposite direction.</p>
<h2>Why "safe" cash still needs a strategy</h2>
<p>The point isn't that holding cash is a mistake — an emergency fund, for instance, correctly prioritizes guaranteed availability over growth. The point is that any money held in cash for the long term needs to at least earn a return matching inflation just to tread water in real terms; anything less is a quiet, guaranteed real loss, even though it never shows up as a negative number on a statement.</p>
<h2>The practical takeaway</h2>
<p>Short-term savings and emergency funds belong in cash regardless of inflation, because their job is availability, not growth. Money that won't be needed for years is a different case — leaving it in a zero or low-interest account isn't the "safe" choice it feels like; it is a specific bet that inflation will stay low, which historically has not always been a safe bet to make.</p>
<h2>Nominal returns versus real returns</h2>
<p>An investment or savings account advertising a "10% return" sounds identical whether inflation that year is 2% or 12% — but the real, inflation-adjusted return is wildly different in each case: roughly 8% in the first scenario, close to flat in the second. Any time a return is quoted, it's worth asking whether it's a nominal figure (before inflation) or a real one (after inflation), since headline "growth" that barely outpaces inflation is closer to standing still than it appears.</p>
<h2>Inflation does not hit every price equally</h2>
<p>A single average inflation rate is useful as a planning shortcut, but it hides real variation — historically, costs like education, healthcare and housing have often risen faster than the broad average in many economies, while some consumer electronics have gotten cheaper over the same period. Anyone planning specifically for a big future cost tied to one of the faster-rising categories should treat the general inflation rate as a floor for their planning assumption, not a precise estimate for that specific expense.</p>
HTML,
            ],
            [
                'slug' => 'time-and-a-half-overtime-explained',
                'title' => 'Time and a Half, Explained: How Overtime Pay Actually Gets Calculated',
                'excerpt' => 'Overtime math looks simple until your payslip does not match what you expected. Here is exactly how the calculation works, and where mistakes happen.',
                'meta_title' => 'How Overtime Pay Is Calculated: Time and a Half Explained',
                'meta_description' => 'Overtime pay is your regular rate multiplied by an overtime factor, applied to hours beyond a threshold. Here is how the calculation works and how to check your payslip.',
                'tags' => ['Salary'],
                'body' => <<<'HTML'
<p class="lead">"Time and a half" is a phrase most working people have heard, but far fewer could actually calculate correctly from a payslip — and that gap is exactly where payroll mistakes, usually unintentional, tend to hide.</p>
<h2>The basic formula</h2>
<p>Overtime pay is your regular hourly rate multiplied by an overtime factor — most commonly 1.5x — applied only to the hours worked beyond a standard threshold, typically 40 hours in a week (though this varies by country and employer policy). Hours up to the threshold are paid at the normal rate; only hours above it get the multiplier.</p>
<h2>A worked example</h2>
<p>At a regular rate of 500 per hour, 160 regular hours in a pay period pays 80,000 as expected. Add 12 hours of overtime at a 1.5x multiplier: the overtime rate becomes 750 per hour, so those 12 hours add 9,000. Total pay for the period comes to 89,000 — not simply 172 hours multiplied by 500, which would understate what's actually owed.</p>
<h2>Where the multiplier changes</h2>
<p>1.5x is common but not universal. Some employers or labor laws specify 2x ("double time") for hours worked on public holidays, on a designated rest day, or beyond a second, higher threshold within the same week. The multiplier that applies depends entirely on the specific employment contract and local labor law — never assume 1.5x is automatic without checking.</p>
<h2>Using this to check a payslip</h2>
<p>Because overtime miscalculation is a genuinely common (usually unintentional) payroll error — particularly around holiday weeks or when multiple pay rates apply — independently recalculating a payslip using the same regular hours, overtime hours and rate is a legitimate way to verify it. A mismatch is worth raising directly with payroll rather than assuming it will self-correct.</p>
<h2>Salaried versus hourly overtime eligibility</h2>
<p>Not every role is eligible for overtime pay in every jurisdiction — many labor laws distinguish between "exempt" salaried positions (often management or specific professional categories) and "non-exempt" roles that are legally entitled to overtime compensation once they cross the weekly hours threshold. Whether a specific position qualifies depends on local labor law and the actual nature of the role, not simply whether someone is paid a salary versus an hourly wage — job titles alone are not always a reliable guide to which category applies.</p>
<h2>Averaging hours across a period</h2>
<p>Some workplaces calculate overtime based on hours averaged across a longer period (say, a two-week pay cycle) rather than strictly week by week, which can produce a different result than treating each week in isolation — working 50 hours one week and 30 the next might not trigger any overtime under an averaged system, even though it clearly would under a strict weekly threshold. Knowing which method a specific employer or jurisdiction uses is necessary before treating any overtime calculation, including this one, as the final word on what is legally owed.</p>
HTML,
            ],
        ];
    }
}
