<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolFaq;
use Illuminate\Database\Seeder;

/**
 * Fifth (and final, per the requested 20-30 tool range) batch of post-launch
 * tool additions: Rule of 72, debt-to-income ratio, salary increment,
 * Zakat al-Fitr, rent vs. buy, and a bill splitter. Checked against all 71
 * existing tools for overlap. Idempotent (updateOrCreate).
 */
class NewToolsBatch5Seeder extends Seeder
{
    public function run(): void
    {
        $moneyCategory = Category::where('type', 'tool')->where('slug', 'money-tax-business')->first();
        $everydayCategory = Category::where('type', 'tool')->where('slug', 'everyday-study')->first();

        foreach ($this->tools() as $i => $tool) {
            $categoryId = $tool['category'] === 'everyday' ? $everydayCategory?->id : $moneyCategory?->id;

            $record = Tool::updateOrCreate(
                ['slug' => $tool['slug']],
                [
                    'category_id' => $categoryId,
                    'title' => $tool['title'],
                    'icon' => $tool['icon'],
                    'component' => $tool['component'],
                    'short_description' => $tool['short_description'],
                    'guide_content' => $tool['guide_content'],
                    'keywords' => $tool['keywords'],
                    'meta_title' => $tool['meta_title'],
                    'meta_description' => $tool['meta_description'],
                    'status' => 'published',
                    'order' => 71 + $i,
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
                'slug' => 'rule-of-72-calculator',
                'title' => 'Rule of 72 Calculator',
                'icon' => '72',
                'component' => 'RuleOf72Calculator',
                'category' => 'money',
                'short_description' => 'Quickly estimate how many years it takes to double your money at a given annual return, using the classic Rule of 72 shortcut.',
                'keywords' => ['rule of 72 calculator', 'how long to double your money', 'doubling time investment calculator'],
                'meta_title' => 'Rule of 72 Calculator: Years to Double Your Money',
                'meta_description' => 'Use the Rule of 72 to quickly estimate how many years it takes an investment to double at a given annual return rate, compared against the exact answer.',
                'guide_content' => <<<'HTML'
<h2>What the Rule of 72 is</h2>
<p>The Rule of 72 is a mental-math shortcut for estimating how long it takes an investment to double in value: divide 72 by the annual return percentage. At a 12% annual return, money doubles in roughly 72 ÷ 12 = 6 years — no calculator or compound interest formula required for a quick estimate.</p>
<h2>Why 72 specifically</h2>
<p>The number 72 is chosen because it divides evenly by many common small numbers (2, 3, 4, 6, 8, 9, 12), making the mental division easy, while still closely approximating the true logarithmic doubling-time formula across the range of return rates most people actually encounter (roughly 6-15%). Accuracy drifts slightly at very low or very high rates, which is why this calculator also shows the exact answer for comparison.</p>
<h2>Why it's still useful in the calculator age</h2>
<p>Even with instant access to precise calculators, the Rule of 72 remains valuable specifically because it's fast enough to do in your head — comparing two investment options' rough doubling times during a conversation, without reaching for a device, is exactly the situation it was designed for. It's a tool for quick intuition, not a replacement for precise planning.</p>
HTML,
                'faqs' => [
                    ['How accurate is the Rule of 72?', 'Very close for typical return rates between roughly 6% and 15% — usually within a few weeks to months of the exact compound-growth answer. Accuracy decreases somewhat at very low or very high rates, which this calculator shows directly for comparison.'],
                    ['Does the Rule of 72 work for inflation too?', 'Yes — the same shortcut works for estimating how long it takes inflation to halve the purchasing power of money, just applied to an inflation rate instead of a return rate.'],
                    ['Can I use this for debt instead of investments?', 'Yes — the same math shows how long an unpaid debt balance would take to double at a given interest rate if no payments were made, which is a sobering way to see high-interest debt\'s growth rate.'],
                ],
            ],
            [
                'slug' => 'debt-to-income-ratio-calculator',
                'title' => 'Debt-to-Income Ratio Calculator',
                'icon' => 'DTI',
                'component' => 'DebtToIncomeRatioCalculator',
                'category' => 'money',
                'short_description' => 'Calculate your debt-to-income ratio — the number lenders use to judge how much more debt you can realistically take on.',
                'keywords' => ['debt to income ratio calculator', 'dti calculator', 'what is a good debt to income ratio'],
                'meta_title' => 'Debt-to-Income (DTI) Ratio Calculator',
                'meta_description' => 'Calculate your debt-to-income ratio from your monthly income and debt payments, and see how it compares to common lender thresholds.',
                'guide_content' => <<<'HTML'
<h2>What debt-to-income ratio measures</h2>
<p>Debt-to-income (DTI) ratio is total monthly debt payments divided by monthly gross income, expressed as a percentage. It's one of the primary numbers lenders use to assess whether someone can realistically take on additional debt — a new loan, mortgage, or credit line — without becoming over-extended.</p>
<h2>What counts as "debt" in this calculation</h2>
<p>Rent or mortgage, loan payments, credit card minimum payments, and any other recurring debt obligations all count. Everyday living expenses — groceries, utilities, subscriptions — do not count toward DTI, even though they're real monthly costs, because DTI specifically measures debt obligations, not total spending.</p>
<h2>Common DTI thresholds</h2>
<p>A DTI at or below 36% is commonly considered healthy and within most lenders' comfort zone. Between 36% and 43% is generally still workable but sits at the upper end of what many lenders will approve for additional credit. Above 43% often signals reduced borrowing capacity and can make new loan approval more difficult, since it suggests less room in the budget to absorb a new payment.</p>
HTML,
                'faqs' => [
                    ['Is DTI the same as credit utilization?', 'No — credit utilization measures how much of your available credit limit you\'re using; DTI measures your debt payments against your income. Both matter for creditworthiness, but they\'re calculated from completely different inputs.'],
                    ['Does DTI include my savings contributions?', 'No — DTI only counts debt obligations, not savings, investments, or other financial commitments that aren\'t technically debt.'],
                    ['How can I lower my DTI?', 'Either increase income or reduce debt payments — paying off a loan, consolidating high-payment debts into a lower one, or paying down a credit card balance to reduce its minimum payment all lower DTI directly.'],
                ],
            ],
            [
                'slug' => 'salary-increment-calculator',
                'title' => 'Salary Increment Calculator',
                'icon' => 'INC',
                'component' => 'SalaryIncrementCalculator',
                'category' => 'money',
                'short_description' => 'Calculate your new salary from a percentage increment, or work out what percentage raise a new salary actually represents.',
                'keywords' => ['salary increment calculator', 'salary hike calculator', 'salary increase percentage calculator', 'pay raise calculator'],
                'meta_title' => 'Salary Increment Calculator: Raise % and New Salary',
                'meta_description' => 'Calculate your new salary from a percentage increment, or find out exactly what percentage raise a given new salary represents.',
                'guide_content' => <<<'HTML'
<h2>Two ways to use this calculator</h2>
<p>If you know the percentage increment you're being offered, this calculates your new salary directly. If instead you already know your new salary figure and want to know what percentage raise it actually represents, switch modes and it works backward from the new number instead — useful for checking whether a stated "10% raise" actually matches the new figure on an offer letter.</p>
<h2>Why checking the actual percentage matters</h2>
<p>Salary increment percentages are sometimes quoted against a different base than expected — gross versus net, or including versus excluding a bonus already factored in — which can make a stated percentage not quite match the real increase in take-home pay. Calculating the percentage directly from old and new salary figures removes any ambiguity about what base was used.</p>
<h2>Increment percentage vs. real purchasing power</h2>
<p>A salary increment's real value depends on inflation during the same period — a 10% raise in a year when prices rose 9% represents only a small real (inflation-adjusted) improvement, even though the nominal percentage looks substantial. Comparing a raise percentage against the current inflation rate gives a more honest sense of how much better off a raise actually makes you.</p>
HTML,
                'faqs' => [
                    ['Should increment be calculated on gross or net salary?', 'Employers typically quote increment percentages against gross (before-tax) salary. Confirm which base your employer is using if the numbers on an offer letter don\'t seem to add up as expected.'],
                    ['What\'s a "good" annual increment?', 'It varies widely by industry, role, and economic conditions, but many argue a raise should at least outpace inflation to represent genuine growth in purchasing power, not just a nominal increase.'],
                    ['Does this account for a promotion vs. a standard annual raise?', 'No — this calculates the percentage or new salary from the numbers you enter regardless of the reason behind the change. Promotions, cost-of-living adjustments, and merit raises all work the same way mathematically.'],
                ],
            ],
            [
                'slug' => 'zakat-al-fitr-calculator',
                'title' => 'Zakat al-Fitr (Fitrana) Calculator',
                'icon' => 'FTR',
                'component' => 'ZakatAlFitrCalculator',
                'category' => 'money',
                'short_description' => 'Calculate your total Zakat al-Fitr (Fitrana) obligation for your household from the current year\'s announced rate per person.',
                'keywords' => ['zakat al fitr calculator', 'fitrana calculator', 'fitra calculator', 'zakat ul fitr amount'],
                'meta_title' => 'Zakat al-Fitr (Fitrana) Calculator',
                'meta_description' => 'Calculate your total Fitrana (Zakat al-Fitr) obligation by multiplying the current year\'s announced per-person rate by your number of household members.',
                'guide_content' => <<<'HTML'
<h2>What Zakat al-Fitr is</h2>
<p>Zakat al-Fitr, commonly called Fitrana, is a charitable obligation given before Eid al-Fitr prayers at the end of Ramadan, distinct from Zakat al-Mal (annual wealth zakat calculated as a percentage of savings). It's a fixed amount per person, traditionally based on the value of a staple food — historically wheat, dates, or barley — rather than a percentage of wealth.</p>
<h2>Why the rate isn't a fixed number here</h2>
<p>Because Zakat al-Fitr is tied to the price of a staple food, the appropriate rupee amount changes every year with food prices, and different religious bodies and regions sometimes announce slightly different figures based on which staple and quantity they use as the reference. This calculator deliberately requires entering the current year's rate rather than hard-coding one, since using a stale rate from a previous year would understate the actual obligation.</p>
<h2>Who it's owed for</h2>
<p>Zakat al-Fitr is typically due on behalf of every member of a household that the head of household is financially responsible for — commonly including children and dependents, not just the adult calculating it. This is different from Zakat al-Mal, which is owed individually based on each person's own qualifying wealth.</p>
HTML,
                'faqs' => [
                    ['Where do I find the current year\'s Fitrana rate?', 'Local religious authorities, mosques, or Islamic charity organizations typically announce the rate each year ahead of Eid, usually denominated in the local currency based on the current price of a staple food.'],
                    ['Is Zakat al-Fitr the same as Zakat al-Mal?', 'No — Zakat al-Mal is an annual percentage of qualifying wealth above a minimum threshold (nisab), calculated individually. Zakat al-Fitr is a small fixed amount per household member, due specifically around Eid al-Fitr, regardless of individual wealth level.'],
                    ['When is Zakat al-Fitr due?', 'It\'s traditionally due before the Eid al-Fitr prayer, though many choose to give it earlier during Ramadan to allow time for distribution to those in need before the holiday.'],
                ],
            ],
            [
                'slug' => 'rent-vs-buy-calculator',
                'title' => 'Rent vs. Buy Calculator',
                'icon' => 'RVB',
                'component' => 'RentVsBuyCalculator',
                'category' => 'money',
                'short_description' => 'Compare the true long-term cost of renting versus buying a home, accounting for mortgage payments, maintenance, appreciation and rising rent.',
                'keywords' => ['rent vs buy calculator', 'should i rent or buy a house', 'renting vs buying calculator'],
                'meta_title' => 'Rent vs. Buy Calculator: Which Costs Less Long-Term?',
                'meta_description' => 'Compare the true cost of renting versus buying over your expected time horizon, factoring in mortgage payments, maintenance, home appreciation and rent increases.',
                'guide_content' => <<<'HTML'
<h2>Why this comparison is more complex than "rent vs. mortgage payment"</h2>
<p>Comparing monthly rent to a monthly mortgage payment alone misses most of what actually determines which option is cheaper: buying involves a large upfront down payment, ongoing maintenance costs, and — critically — building equity in an asset that (usually) appreciates over time. Renting avoids all of that upfront commitment but builds no equity and is exposed to rent increases over the same period.</p>
<h2>How this calculator accounts for both sides</h2>
<p>For buying, it totals the down payment, mortgage payments made during your time horizon, and maintenance costs, then subtracts your net equity at the end — the home's appreciated value minus whatever mortgage balance is still outstanding if the horizon is shorter than the full loan term. For renting, it totals rent paid over the same horizon, accounting for annual rent increases rather than assuming rent stays flat.</p>
<h2>Why your time horizon changes the answer</h2>
<p>Buying typically involves large upfront costs (down payment, closing-type costs) that take years to "pay off" through avoided rent and building equity — a short time horizon often favors renting, while a longer horizon gives home appreciation and equity-building more time to work in the buyer's favor. Running the comparison at a few different horizons — not just one assumed number of years — gives a much clearer picture than a single answer.</p>
<h2>What this doesn't capture</h2>
<p>This models the core financial trade-off but leaves out some real factors: the flexibility renting offers to relocate easily, the transaction costs of eventually selling a home, and non-financial preferences around stability or customization that reasonably factor into a real housing decision alongside the pure cost comparison.</p>
HTML,
                'faqs' => [
                    ['What if I don\'t know the exact home appreciation rate?', 'Use a conservative, long-run average for your area rather than recent short-term trends, which can be unusually high or low. Running the calculator at a couple of different appreciation assumptions shows how sensitive the answer is to this uncertainty.'],
                    ['Does this include closing costs or selling costs?', 'No — this focuses on the core ongoing cost comparison (payments, maintenance, appreciation, rent). One-time transaction costs on either end of a purchase or sale would add to the buying side\'s real total cost.'],
                    ['Why does a longer time horizon usually favor buying?', 'Because the large upfront costs of buying are fixed regardless of how long you stay, while the ongoing benefits (avoided rent increases, accumulated equity, appreciation) keep compounding the longer you hold the property — spreading a fixed upfront cost over more years usually improves buying\'s relative economics.'],
                ],
            ],
            [
                'slug' => 'bill-splitter-calculator',
                'title' => 'Bill Splitter Calculator',
                'icon' => 'SPL',
                'component' => 'BillSplitterCalculator',
                'category' => 'everyday',
                'short_description' => 'Split a shared bill fairly when people paid different amounts upfront — enter what everyone paid and see exactly who owes whom.',
                'keywords' => ['bill splitter calculator', 'expense splitter', 'split expenses calculator', 'who owes who calculator'],
                'meta_title' => 'Bill Splitter Calculator: Who Owes Whom',
                'meta_description' => 'Split a shared bill or group expense fairly — enter what each person paid and get a simple list of who owes whom to settle up evenly.',
                'guide_content' => <<<'HTML'
<h2>What this solves that a simple "divide by people" doesn't</h2>
<p>Splitting a bill evenly is easy when everyone pays nothing upfront and the total simply divides by the group size. It gets more complicated when different people already paid different amounts — one person covered the hotel, another paid for dinner — and now the group needs to work out who still owes what to make everything fair.</p>
<h2>How the settlement is calculated</h2>
<p>Everyone's fair share is the total spent divided by the number of people. Comparing what each person actually paid against that fair share shows who's owed money (paid more than their share) and who owes money (paid less). The calculator then works out the smallest set of payments needed to settle everyone up — rather than everyone paying everyone else individually, it matches debtors with creditors directly to minimize the number of transactions required.</p>
<h2>Why minimizing transactions matters</h2>
<p>Without this step, a group of five people settling up could theoretically require up to ten separate payments between different pairs. The settlement approach used here typically reduces that to just a few payments — often one fewer than the number of people involved — which is both faster and less error-prone for an actual group to execute.</p>
HTML,
                'faqs' => [
                    ['What if everyone paid exactly their fair share already?', 'The calculator shows no settlements needed — everyone is already even, which happens whenever contributions already match the fair per-person split.'],
                    ['Can I use this for more than a handful of people?', 'Yes — add as many people as needed. The settlement logic works the same way regardless of group size, though very large groups will naturally need more settlement payments than small ones.'],
                    ['Does this account for people who consumed different amounts?', 'No — this assumes an equal split of the total is fair for everyone. If shares should be unequal (someone had a more expensive meal, for example), adjust each person\'s "paid" amount manually to reflect the split you actually intend before calculating.'],
                ],
            ],
        ];
    }
}
