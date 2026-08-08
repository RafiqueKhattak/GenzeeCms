<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolFaq;
use Illuminate\Database\Seeder;

/**
 * Second batch of post-launch tool additions (Aug 2026 growth push):
 * amortization schedule, markup, emergency fund, freelancer hourly rate,
 * inflation impact, overtime pay — chosen to avoid overlap with both the
 * original 47 tools and NewToolsBatch1Seeder. Idempotent (updateOrCreate).
 */
class NewToolsBatch2Seeder extends Seeder
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
                    'order' => 53 + $i,
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
                'slug' => 'amortization-schedule-calculator',
                'title' => 'Loan Amortization Schedule Calculator',
                'icon' => 'AMT',
                'component' => 'AmortizationScheduleCalculator',
                'short_description' => 'See your monthly loan payment and exactly how the first year splits between principal and interest.',
                'keywords' => ['amortization calculator', 'amortization schedule', 'loan payment breakdown', 'principal vs interest calculator'],
                'meta_title' => 'Amortization Schedule Calculator: Monthly Payment Breakdown',
                'meta_description' => 'Calculate your monthly loan payment and see a first-year amortization schedule showing exactly how much of each payment goes to principal versus interest.',
                'guide_content' => <<<'HTML'
<h2>What amortization means</h2>
<p>Amortization is the process of paying off a loan through regular, fixed payments over time, where each payment covers that period's interest first and puts the remainder toward the principal balance. An amortization schedule lists exactly how much of each individual payment goes to each, month by month.</p>
<h2>Why the split changes over time</h2>
<p>Early in a loan, the balance is at its highest, so the interest portion of each payment is largest and the principal portion is smallest — even though the total payment stays fixed. As the balance shrinks with each payment, less interest accrues, so a growing share of each identical payment goes toward principal instead. This is why the first year of a long loan can feel like slow progress on the balance, even with consistent payments.</p>
<h2>Using this to make smarter decisions</h2>
<p>Seeing the actual split makes two things concrete: how much interest a loan costs in its early years specifically, and why extra payments made early in a loan's life save more total interest than the same extra payment made later — early extra payments skip more of the high-interest months.</p>
HTML,
                'faqs' => [
                    ['Why does my payment stay the same but the interest portion change?', 'The fixed monthly payment is calculated so that after all planned payments, the balance reaches zero. Because interest is charged on the remaining balance, the interest portion has to shrink over time even though the payment itself does not.'],
                    ['Does making one extra payment save much?', 'Yes — an extra payment applied directly to principal reduces the balance that all future interest calculations are based on, which compounds over the remaining life of the loan. Extra payments made earlier in the schedule generally save more total interest than the same amount paid later.'],
                    ['Why does the calculator only show the first year?', 'A full schedule for a 20 or 30-year loan would run to hundreds of rows, which is more detail than most people need to understand the pattern. The first year shows the mechanism clearly — the same principal-vs-interest shift continues in the same direction for the rest of the loan.'],
                ],
            ],
            [
                'slug' => 'markup-calculator',
                'title' => 'Markup Calculator',
                'icon' => 'MKP',
                'component' => 'MarkupCalculator',
                'short_description' => 'Work out a selling price from your cost and a target markup percentage — and see the resulting profit margin, which is not the same number.',
                'keywords' => ['markup calculator', 'markup vs margin', 'selling price calculator', 'cost plus pricing calculator'],
                'meta_title' => 'Markup Calculator: Cost Plus Markup to Selling Price',
                'meta_description' => 'Calculate a selling price from cost price and markup percentage, and see the resulting profit margin — markup and margin are different numbers on the same sale.',
                'guide_content' => <<<'HTML'
<h2>Markup vs. margin — the confusion this tool solves</h2>
<p>Markup is calculated as a percentage of <strong>cost</strong>: how much you add on top of what something cost you. Margin is calculated as a percentage of <strong>selling price</strong>: how much of the final sale price is profit. The same sale has two different-looking percentages depending on which base you measure from, which is why a "50% markup" and a "50% margin" produce very different selling prices from the same cost.</p>
<h2>A worked example</h2>
<p>Take a product costing 700. A 50% markup means adding 50% of 700 (which is 350) to get a selling price of 1,050. But that sale's profit margin is only 33.3% (350 profit ÷ 1,050 selling price) — not 50%. To achieve an actual 50% margin from the same 700 cost, the selling price would need to be 1,400 instead.</p>
<h2>Why this matters for pricing decisions</h2>
<p>Businesses that mix up the two can systematically under-price their products — assuming a target margin when they have actually only hit that percentage as markup, which is always a smaller number for the same input. This calculator starts from markup (the more intuitive "add X% to cost" framing) and always shows the resulting margin alongside it, so the gap between the two is visible rather than hidden.</p>
HTML,
                'faqs' => [
                    ['Is a 100% markup the same as doubling the price?', 'Yes — a 100% markup means adding 100% of the cost on top of itself, which doubles the selling price. That specific case is the one point where markup and a simple doubling coincide.'],
                    ['Which should I use for pricing — markup or margin?', 'Margin is generally more useful for tracking overall business profitability, since it directly tells you what percentage of revenue is profit. Markup is often more convenient at the point of pricing an individual item, since it starts from a known cost. Many businesses use markup to set prices and margin to evaluate results.'],
                    ['Does this include taxes or other selling costs?', 'No — this calculates a straightforward cost-to-price markup. Taxes, payment processing fees, shipping, and other selling costs would need to be added to the cost base first if you want the final price to account for them.'],
                ],
            ],
            [
                'slug' => 'emergency-fund-calculator',
                'title' => 'Emergency Fund Calculator',
                'icon' => 'EMF',
                'component' => 'EmergencyFundCalculator',
                'short_description' => 'Find your emergency fund target based on essential monthly expenses, and see how long it will take to reach it at your current saving rate.',
                'keywords' => ['emergency fund calculator', 'how much emergency fund', '6 months expenses calculator', 'emergency savings calculator'],
                'meta_title' => 'Emergency Fund Calculator: How Much You Need & When You Will Get There',
                'meta_description' => 'Calculate your emergency fund target from essential monthly expenses and a target number of months, then see how long it will take to reach it.',
                'guide_content' => <<<'HTML'
<h2>What counts as "essential" expenses</h2>
<p>An emergency fund is sized around what you would need to survive a loss of income, not your full normal spending. That means rent or mortgage, groceries, utilities, transport, insurance, and minimum debt payments — not subscriptions, dining out, or discretionary purchases, which could be cut immediately in a genuine emergency.</p>
<h2>Why 3-6 months is the common range</h2>
<p>Three months is often cited as a baseline that covers a typical short job search or a temporary income disruption. Six months (or more) is generally recommended for less predictable income — freelancers, commission-based roles, or a single-income household — where the time needed to replace lost income is harder to estimate confidently.</p>
<h2>Where to actually keep it</h2>
<p>An emergency fund's job is to be available immediately without loss of value, which rules out anything that can drop in value when you need it most — most investment accounts are the wrong home for this money. A separate savings account, ideally one that is slightly inconvenient to transfer out of quickly (to prevent casual dipping into it), is the standard choice.</p>
HTML,
                'faqs' => [
                    ['Should my emergency fund include investments?', 'Generally no — the point of the fund is guaranteed availability at full value exactly when you need it, and investments can be down in value at the worst possible time. Keep it in cash or a cash-equivalent savings account instead.'],
                    ['What if I cannot save the full target right away?', 'Any amount is better than none — even a small starter fund of one month\'s expenses meaningfully reduces the chance of going into debt over a minor emergency. Build toward the full target gradually rather than waiting to start until you can fund it all at once.'],
                    ['Does this replace insurance?', 'No — insurance and an emergency fund cover different kinds of risk. Insurance protects against specific large, unlikely losses (a major illness, a totaled car); the emergency fund covers the everyday disruptions insurance does not, like a temporary job loss or an unexpected repair bill.'],
                ],
            ],
            [
                'slug' => 'freelancer-hourly-rate-calculator',
                'title' => 'Freelancer Hourly Rate Calculator',
                'icon' => 'FRL',
                'component' => 'FreelancerHourlyRateCalculator',
                'short_description' => 'Work out what to actually charge per hour, accounting for tax, business expenses, and the reality that not every hour is billable.',
                'keywords' => ['freelancer hourly rate calculator', 'how much should i charge freelance', 'billable rate calculator', 'freelance rate calculator'],
                'meta_title' => 'Freelancer Hourly Rate Calculator: What to Actually Charge',
                'meta_description' => 'Calculate the hourly rate you need to charge as a freelancer to hit a target take-home income, accounting for tax, business expenses and non-billable time.',
                'guide_content' => <<<'HTML'
<h2>Why "salary divided by hours" gets it wrong</h2>
<p>A common freelancer mistake is taking a target salary and dividing it by 2,080 (a standard work year of 40-hour weeks). That figure assumes every single hour is billable, ignores tax, and ignores that running a freelance business comes with its own costs — none of which apply to a salaried employee's paycheck math.</p>
<h2>What actually eats into billable time</h2>
<p>Admin, invoicing, marketing, finding new clients, and ongoing learning all take real time but are not directly billable to any client. Most freelancers can realistically bill somewhere between 50-70% of their total working hours — the rest is the unavoidable overhead of running an independent business, and it still needs to be paid for by the hours that are billable.</p>
<h2>Working backward from take-home pay</h2>
<p>This calculator starts from the number that actually matters — desired take-home income — and grosses it up twice: once for business expenses (software, equipment, a portion of workspace costs), then again for tax, before dividing by realistic billable hours rather than total working hours. The result is a rate that, if consistently charged and billed, should actually deliver the target income after real costs.</p>
HTML,
                'faqs' => [
                    ['What percentage of hours are usually billable?', 'It varies by field, but 50-70% billable is a common realistic range once admin, marketing and non-billable client communication are accounted for. Full-time employees with dedicated sales and admin support are a very different comparison and should not be used as a benchmark.'],
                    ['Should I charge different rates for different clients?', 'Many freelancers do — this calculator gives a baseline minimum rate; charging more for rush work, narrow specializations, or particularly demanding clients is a separate, valid pricing decision layered on top of the baseline.'],
                    ['How do I know my tax rate as a freelancer?', 'This depends on your income level and country\'s tax rules for self-employed income, which often differs from salaried tax treatment. Check your specific bracket with a local tax calculator or advisor rather than assuming employee tax rates apply.'],
                ],
            ],
            [
                'slug' => 'inflation-impact-calculator',
                'title' => 'Inflation Impact Calculator',
                'icon' => 'INF',
                'component' => 'InflationImpactCalculator',
                'short_description' => 'See what a sum of money today will actually be able to buy in the future, after a chosen average inflation rate erodes its purchasing power.',
                'keywords' => ['inflation calculator', 'purchasing power calculator', 'future value of money calculator', 'what will money be worth calculator'],
                'meta_title' => 'Inflation Impact Calculator: Future Purchasing Power',
                'meta_description' => 'Calculate how much a sum of money today will be worth, in real purchasing power, after a chosen number of years at a given average inflation rate.',
                'guide_content' => <<<'HTML'
<h2>What "purchasing power" means here</h2>
<p>This is not a currency conversion — it is the same currency, measured against itself over time. Inflation means prices rise, so the same amount of money buys a shrinking basket of goods and services each year. This calculator shows what today's amount would be able to buy in the future, expressed in today's terms, after a chosen average inflation rate.</p>
<h2>Why this matters for savings held in cash</h2>
<p>Money sitting in a low-interest or zero-interest account is not "safe" from this effect — it can still lose real value every year inflation runs ahead of whatever interest it earns. A rate of return has to at least match inflation just to preserve purchasing power; anything below that is a real (inflation-adjusted) loss, even while the nominal number on the account statement keeps growing or stays flat.</p>
<h2>Why this matters for long-term planning</h2>
<p>A retirement or savings target set only in today's rupees, without accounting for inflation over a multi-decade horizon, will fall dramatically short by the time it is actually needed — the calculator's "future equivalent" figure shows how much a future target actually needs to be to match today's purchasing power, which is the number long-term plans should really be built around.</p>
HTML,
                'faqs' => [
                    ['What inflation rate should I use?', 'A country\'s recent average annual inflation rate is a reasonable starting point, though it fluctuates year to year. For long-term planning, many people use a conservative long-run average rather than the most recent single year\'s figure, which can be unusually high or low.'],
                    ['Does this account for investment returns?', 'No — this isolates the effect of inflation alone on a static sum. To see the combined effect of both investment growth and inflation, use a savings or retirement calculator with a real (inflation-adjusted) return rate instead.'],
                    ['Why does money "lose value" even if I never spend it?', 'Because the prices of everything it could buy keep rising. The number on your account statement does not shrink, but what that number can purchase does — that gap is what this calculator measures.'],
                ],
            ],
            [
                'slug' => 'overtime-pay-calculator',
                'title' => 'Overtime Pay Calculator',
                'icon' => 'OT',
                'component' => 'OvertimePayCalculator',
                'short_description' => 'Calculate total pay for a period from your regular hourly rate, hours worked, and an overtime multiplier like 1.5x or 2x.',
                'keywords' => ['overtime pay calculator', 'time and a half calculator', 'overtime calculator', 'how much is overtime pay'],
                'meta_title' => 'Overtime Pay Calculator: Regular + Overtime Pay Total',
                'meta_description' => 'Calculate total pay for a pay period from your hourly rate, regular hours, overtime hours and overtime multiplier (like 1.5x "time and a half").',
                'guide_content' => <<<'HTML'
<h2>How overtime pay is typically calculated</h2>
<p>Overtime pay is usually the regular hourly rate multiplied by an overtime factor — most commonly 1.5x, known as "time and a half" — applied only to hours worked beyond a standard threshold (often 40 hours a week, though this varies by country and employer policy). Regular hours are paid at the normal rate; only the hours above the threshold get the multiplier.</p>
<h2>Why the multiplier varies</h2>
<p>1.5x is the most common overtime rate in many jurisdictions' labor law, but some employers or contracts specify 2x ("double time") for hours worked on public holidays, on a designated rest day, or beyond a second, higher threshold in the same week. Always check your specific employment contract or local labor law rather than assuming 1.5x applies universally.</p>
<h2>Checking your payslip</h2>
<p>This calculator is a useful way to independently verify a payslip: enter the same regular hours, overtime hours and rate your employer used, and confirm the total matches what was actually paid. A mismatch is worth raising with payroll — overtime miscalculations are a common, usually unintentional payroll error, especially in periods with unusual schedules like public holidays.</p>
HTML,
                'faqs' => [
                    ['Is overtime always 1.5x?', 'No — 1.5x ("time and a half") is common but not universal. Some contracts specify 2x for holidays or after a second threshold. Check your employment contract or local labor law for the exact rate that applies to you.'],
                    ['Does this account for tax on overtime?', 'No — this calculates gross pay before tax. Overtime pay is typically taxed the same way as regular income, though in some payroll systems it can push a paycheck into a higher withholding bracket for that period specifically, even if annual tax owed is unaffected.'],
                    ['What counts as "overtime hours"?', 'Hours worked beyond your standard threshold for the pay period — commonly 40 hours a week, but this depends on your contract, industry, and local labor law. Confirm your specific threshold before relying on the result for a payslip dispute.'],
                ],
            ],
        ];
    }
}
