<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolFaq;
use Illuminate\Database\Seeder;

/**
 * First batch of post-launch tool additions (Aug 2026 growth push): six
 * calculators covering search-demand gaps identified in keyword research —
 * retirement, debt payoff, FIRE, net worth, budgeting, rental yield — none
 * of which overlap the existing 47 tools. Idempotent (updateOrCreate) so
 * it's safe to re-run on every deploy, same pattern as GenZContentSeeder.
 */
class NewToolsBatch1Seeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('type', 'tool')->where('slug', 'money-tax-business')->first();

        foreach ($this->tools() as $i => $tool) {
            $record = Tool::updateOrCreate(
                ['slug' => $tool['slug']],
                [
                    'category_id' => $category?->id,
                    'title' => $tool['title'],
                    'icon' => $tool['icon'],
                    'component' => $tool['component'],
                    'short_description' => $tool['short_description'],
                    'guide_content' => $tool['guide_content'],
                    'keywords' => $tool['keywords'],
                    'meta_title' => $tool['meta_title'],
                    'meta_description' => $tool['meta_description'],
                    'status' => 'published',
                    'order' => 47 + $i,
                    'published_at' => now(),
                ]
            );

            $record->faqs()->delete();
            foreach ($tool['faqs'] as $j => [$question, $answer]) {
                ToolFaq::create([
                    'tool_id' => $record->id,
                    'question' => $question,
                    'answer' => $answer,
                    'order' => $j,
                ]);
            }
        }
    }

    protected function tools(): array
    {
        return [
            [
                'slug' => 'retirement-savings-calculator',
                'title' => 'Retirement Savings Calculator',
                'icon' => 'RET',
                'component' => 'RetirementSavingsCalculator',
                'short_description' => 'See what your current savings and monthly contributions will actually grow to by retirement, based on your expected rate of return.',
                'keywords' => ['retirement calculator', 'retirement savings calculator', 'how much do i need to retire', 'compound growth retirement', 'monthly retirement contribution calculator'],
                'meta_title' => 'Retirement Savings Calculator: Project Your Balance at Retirement',
                'meta_description' => 'Enter your age, current savings, monthly contribution and expected return to see your projected retirement balance, split between what you contributed and what growth added.',
                'guide_content' => <<<'HTML'
<h2>How this calculator works</h2>
<p>It projects two things forward to your target retirement age: the lump sum you already have, and the stream of monthly contributions you plan to keep making. Both grow at your expected annual return, compounded monthly, using the standard future-value-of-an-annuity formula.</p>
<h2>Why the "growth" number is usually bigger than you expect</h2>
<p>Over a long enough horizon, investment growth compounds on itself — the returns you earn in year five also earn returns in year twenty. That's why starting even ten years earlier, even with a smaller contribution, tends to beat starting later with a much larger one. The calculator splits your projected balance into "what you contributed" versus "growth from returns" specifically so this effect is visible, not hidden inside one lump number.</p>
<h2>Choosing a realistic return rate</h2>
<p>7-12% a year is a common range for a diversified equity-heavy portfolio over long periods, before inflation. A more conservative mix of bonds and cash will sit lower. Whatever you pick, treat it as one estimate among several — try the calculator at two or three different return assumptions to see how sensitive your outcome is.</p>
HTML,
                'faqs' => [
                    ['How much should I save for retirement?', 'A common rule of thumb is that your total retirement savings should eventually support roughly 25 times your annual expenses, so you can withdraw a sustainable percentage each year without running out. This calculator works from the other direction — starting with what you can actually contribute — so you can see where a given monthly amount gets you and adjust from there.'],
                    ['Does this account for inflation?', 'No — the projected balance is in today\'s money terms only if your return rate is already an "after-inflation" (real) rate. If you use a nominal return rate (the type usually quoted for stock market averages), the projected balance is in future, inflated rupees, which will buy less than the same number does today.'],
                    ['What counts as a monthly contribution?', 'Any amount you consistently set aside for retirement — pension contributions, an investment account, a recurring savings transfer. If your contribution varies, use a realistic average rather than your best month.'],
                ],
            ],
            [
                'slug' => 'debt-payoff-calculator',
                'title' => 'Debt Payoff Calculator',
                'icon' => 'DBT',
                'component' => 'DebtPayoffCalculator',
                'short_description' => 'Enter your balance, interest rate and fixed monthly payment to see exactly how long payoff will take and how much interest you\'ll pay in total.',
                'keywords' => ['debt payoff calculator', 'how long to pay off debt', 'credit card payoff calculator', 'loan payoff time calculator', 'debt interest calculator'],
                'meta_title' => 'Debt Payoff Calculator: How Long Until You\'re Debt-Free?',
                'meta_description' => 'See how many months it takes to pay off a debt at a fixed monthly payment, plus the total interest you\'ll pay — enter balance, APR and payment to find out.',
                'guide_content' => <<<'HTML'
<h2>How this calculator works</h2>
<p>Each month, part of your payment covers that month's interest and the rest reduces the balance. As the balance shrinks, less of each future payment goes to interest and more goes to principal — which is why payoff often accelerates near the end. This calculator runs that month-by-month math for you and totals the interest paid along the way.</p>
<h2>Why your payment has to clear a minimum</h2>
<p>If your monthly payment is smaller than the interest the balance generates that month, the balance never shrinks — it can even grow. The calculator flags this "minimum viable payment" so you know the floor for your specific balance and rate before you commit to a number.</p>
<h2>Snowball vs. avalanche, if you have more than one debt</h2>
<p>This tool handles one debt at a time. If you're juggling several, run each one through separately: the "avalanche" method pays extra toward whichever has the highest interest rate first (mathematically cheapest overall), while the "snowball" method clears the smallest balance first (often easier to stick with, since you see a debt disappear sooner).</p>
HTML,
                'faqs' => [
                    ['Should I pay off debt or invest first?', 'As a rough guide, debt charging more than your realistic investment return is usually worth clearing first — a 24% credit card rate is very hard to beat by investing. Lower-rate debt (some student or mortgage loans) is more of a judgment call, and many people do both at once.'],
                    ['What\'s the difference between APR and interest rate?', 'APR (annual percentage rate) includes the base interest rate plus most fees, expressed as one yearly figure — it\'s the more complete number to use here if your lender quotes both.'],
                    ['Why does my total interest look so high?', 'On high-rate debt like credit cards, interest can end up costing more than the original balance if payments stay near the minimum for a long time. Increasing the monthly payment amount, even slightly, cuts both the time and total interest substantially — try a few different payment amounts to see the effect.'],
                ],
            ],
            [
                'slug' => 'fire-number-calculator',
                'title' => 'FIRE Number Calculator',
                'icon' => 'FIRE',
                'component' => 'FireNumberCalculator',
                'short_description' => 'Calculate your FIRE number — the portfolio size you\'d need to cover your expenses indefinitely — and roughly how long it will take to get there.',
                'keywords' => ['fire number calculator', 'financial independence calculator', 'fire calculator', 'how much to retire early', '4 percent rule calculator'],
                'meta_title' => 'FIRE Number Calculator: Financial Independence Target & Timeline',
                'meta_description' => 'Work out your FIRE (Financial Independence, Retire Early) number from your annual expenses and a safe withdrawal rate, plus an estimated timeline to reach it.',
                'guide_content' => <<<'HTML'
<h2>What a "FIRE number" actually is</h2>
<p>It's the portfolio size that, at a chosen withdrawal rate, could theoretically cover your annual expenses indefinitely without depleting the principal over a long retirement. At the traditional 4% withdrawal rate, that works out to 25 times your annual expenses — because withdrawing 4% a year is the inverse of multiplying by 25.</p>
<h2>Where the 4% rule comes from</h2>
<p>It's based on historical research (the "Trinity Study" and its successors) into how a diversified portfolio has performed across rolling 30-year retirement periods. It's a rule of thumb, not a guarantee — some later research suggests a slightly more conservative 3.5% is safer for retirements expected to run longer than 30 years, which the calculator lets you dial in directly.</p>
<h2>The timeline estimate</h2>
<p>Given your current savings, monthly contribution and expected return, the calculator simulates your balance growing month by month until it crosses your FIRE number. Small changes to the withdrawal rate or expected return can move this estimate by years, so it's worth checking a couple of scenarios rather than treating one number as fixed.</p>
HTML,
                'faqs' => [
                    ['What does FIRE stand for?', 'Financial Independence, Retire Early — a movement built around aggressive saving and investing to reach a self-sustaining portfolio well before typical retirement age, giving the option (not necessarily the obligation) to stop working for pay.'],
                    ['Is 4% actually safe?', 'It held up in most historical 30-year periods in the US market data it was studied on, but it isn\'t a mathematical guarantee for future markets, other countries, or retirements longer than 30 years. Many FIRE planners use 3-3.5% for extra safety margin, which is why this calculator lets you adjust it.'],
                    ['Does my FIRE number include a paid-off house?', 'Only if you count it as one of your "investable savings" in this calculator — most FIRE planners exclude a primary home, since you can\'t spend it down for living expenses without selling or borrowing against it.'],
                ],
            ],
            [
                'slug' => 'net-worth-calculator',
                'title' => 'Net Worth Calculator',
                'icon' => 'NET',
                'component' => 'NetWorthCalculator',
                'short_description' => 'Add up everything you own and subtract everything you owe to get a clear snapshot of your net worth.',
                'keywords' => ['net worth calculator', 'how to calculate net worth', 'personal net worth', 'assets minus liabilities calculator'],
                'meta_title' => 'Net Worth Calculator: Assets Minus Liabilities',
                'meta_description' => 'Calculate your personal net worth by listing your assets — cash, investments, property — against your liabilities — loans, credit card debt — for a clear financial snapshot.',
                'guide_content' => <<<'HTML'
<h2>How net worth is calculated</h2>
<p>Net worth is simply total assets minus total liabilities: everything you own (cash, investments, property, vehicles) minus everything you owe (loans, credit card balances, other debts). It's a snapshot, not a judgment — a negative number just means liabilities currently outweigh assets, which is common early in a career or after a big purchase like education or a first home.</p>
<h2>Why it's worth tracking over time</h2>
<p>A single net worth figure tells you less than the trend does. Recalculating every few months shows whether your financial position is genuinely improving — which can be true even while a specific debt (like a mortgage) is still large, as long as it's shrinking and your assets are growing faster than any remaining liabilities.</p>
<h2>What to leave out</h2>
<p>Most people exclude the resale value of everyday possessions (furniture, electronics, clothing) since they're not realistically going to be sold to fund anything — including them tends to inflate the number without changing your actual financial flexibility.</p>
HTML,
                'faqs' => [
                    ['Should I include my car in net worth?', 'Most people do, using its current resale value (not what it cost new) as an asset, and any remaining car loan as a liability. It\'s optional — some people leave depreciating assets like cars out entirely to keep the number focused on financial assets.'],
                    ['Is a negative net worth bad?', 'Not necessarily — it\'s very common early on, especially with student loans or a recent mortgage. What matters more is the direction it\'s moving: is it improving release over release, not whether it\'s positive on any one snapshot.'],
                    ['How often should I recalculate?', 'Quarterly or twice a year is enough for most people — net worth moves slowly, and checking too often (especially with volatile investments) can be more stressful than useful.'],
                ],
            ],
            [
                'slug' => 'budget-calculator',
                'title' => '50/30/20 Budget Calculator',
                'icon' => 'BGT',
                'component' => 'BudgetCalculator',
                'short_description' => 'Split your monthly income into needs, wants and savings using the popular 50/30/20 rule — or adjust the percentages to fit your own situation.',
                'keywords' => ['budget calculator', '50 30 20 rule calculator', 'monthly budget calculator', 'how to budget income'],
                'meta_title' => '50/30/20 Budget Calculator: Split Your Income by Category',
                'meta_description' => 'Enter your monthly take-home income and see a 50/30/20 budget split — needs, wants, and savings/debt — with adjustable percentages to match your own priorities.',
                'guide_content' => <<<'HTML'
<h2>What the 50/30/20 rule is</h2>
<p>It's a simple budgeting framework, popularised by Senator Elizabeth Warren's book on family finances: roughly 50% of take-home income to needs (rent, groceries, utilities, minimum debt payments), 30% to wants (eating out, entertainment, subscriptions), and 20% to savings and extra debt payoff. It's a starting point, not a rule enforced anywhere — the calculator lets you adjust the needs and wants sliders and see what's left for savings.</p>
<h2>Why "needs" is smaller than people expect</h2>
<p>The category is meant to cover only genuine non-negotiables. A streaming subscription, eating out, or a gym membership are wants, however routine they feel — the test is whether you'd face real consequences (eviction, no food, no transport to work) by cutting it, not whether you'd be inconvenienced.</p>
<h2>When to deviate from 50/30/20</h2>
<p>In high cost-of-living areas, needs can easily exceed 50% no matter how careful the spending is — in that case, the ratio matters less than making sure something is going to savings and debt payoff every month, even if it starts smaller than 20%.</p>
HTML,
                'faqs' => [
                    ['Is take-home pay before or after tax?', 'After tax and any mandatory deductions — the actual amount that lands in your account each month, since that\'s what you can genuinely budget with.'],
                    ['What if my needs are more than 50% of income?', 'It\'s common in expensive cities, and it doesn\'t mean you\'re doing anything wrong — treat 50/30/20 as a direction to work toward rather than a hard requirement, and prioritise getting at least something into the savings category consistently.'],
                    ['Does debt repayment count as a need or savings?', 'Minimum required payments count as a need (they\'re non-negotiable); any extra you put toward debt beyond the minimum counts as part of the savings/debt category, since it\'s a choice rather than an obligation.'],
                ],
            ],
            [
                'slug' => 'rental-yield-calculator',
                'title' => 'Rental Yield Calculator',
                'icon' => 'RNT',
                'component' => 'RentalYieldCalculator',
                'short_description' => 'Work out the gross and net rental yield on a property from its price, monthly rent and annual running costs.',
                'keywords' => ['rental yield calculator', 'rental roi calculator', 'property investment calculator', 'gross yield vs net yield', 'rental income calculator'],
                'meta_title' => 'Rental Yield Calculator: Gross & Net Yield on a Property',
                'meta_description' => 'Calculate gross and net rental yield from a property\'s price, monthly rent and annual expenses — plus estimated monthly cash flow after costs.',
                'guide_content' => <<<'HTML'
<h2>Gross yield vs. net yield</h2>
<p>Gross yield is annual rent divided by the property's price — a quick, rough comparison figure. Net yield subtracts annual running costs (maintenance, property tax, insurance, management fees) first, which gives a more realistic picture of the actual return, since two properties with identical gross yields can have very different net returns depending on their upkeep costs.</p>
<h2>What counts as an "annual expense" here</h2>
<p>Ongoing running costs only — maintenance and repairs, property tax, insurance, and management fees if you use an agent. It deliberately excludes the purchase price itself and one-off costs like stamp duty or renovation, since those are part of the initial investment decision rather than the ongoing yield.</p>
<h2>Yield isn't the whole picture</h2>
<p>A high yield doesn't automatically mean a good investment — it can also signal a higher-risk area, more tenant turnover, or a property needing more maintenance than average. Yield is one input among several (alongside expected capital appreciation, financing costs, and vacancy risk) that go into a full property investment decision.</p>
HTML,
                'faqs' => [
                    ['What\'s a "good" rental yield?', 'It varies a lot by city and property type, but as a very rough guide, many investors look for net yields above 5-6% as a starting benchmark — though this depends heavily on local market norms and what return alternative investments could offer.'],
                    ['Should I include mortgage payments in expenses?', 'This calculator treats yield as if the property were bought outright, so mortgage/financing costs are deliberately excluded — that lets you compare properties on the property\'s own merits, separate from how any one buyer chooses to finance it.'],
                    ['Does yield account for vacancy periods?', 'Not directly — the monthly rent figure assumes the property is occupied. For a more conservative estimate, reduce the monthly rent input by your expected vacancy rate (e.g. 5-10% for a typical vacancy allowance) before calculating.'],
                ],
            ],
        ];
    }
}
