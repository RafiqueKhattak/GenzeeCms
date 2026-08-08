<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolFaq;
use Illuminate\Database\Seeder;

/**
 * Fourth batch of post-launch tool additions (Aug 2026 growth push):
 * mortgage affordability, loan comparison, currency depreciation impact,
 * gratuity, import duty, and a dedicated USD-to-PKR exchange rate page.
 *
 * The USD/PKR tool deliberately reuses the existing CurrencyConverter.vue
 * component rather than a new one — the underlying live-rate mechanism is
 * already generic and defaults to USD->PKR. A separate Tool row with its
 * own slug/title/meta gives "usd to pkr", "usd/pkr" and "pkr exchange
 * rate" (all real, high-volume search terms per user request) a
 * dedicated, purpose-titled landing page without duplicating any code —
 * multiple Tool rows pointing at the same `component` value is supported,
 * same as how several country salary-tax tools could in principle share
 * logic if their rules ever converged.
 *
 * Idempotent (updateOrCreate).
 */
class NewToolsBatch4Seeder extends Seeder
{
    public function run(): void
    {
        $moneyCategory = Category::where('type', 'tool')->where('slug', 'money-tax-business')->first();

        foreach ($this->tools() as $i => $tool) {
            $record = Tool::updateOrCreate(
                ['slug' => $tool['slug']],
                [
                    'category_id' => $moneyCategory?->id,
                    'title' => $tool['title'],
                    'icon' => $tool['icon'],
                    'component' => $tool['component'],
                    'short_description' => $tool['short_description'],
                    'guide_content' => $tool['guide_content'],
                    'keywords' => $tool['keywords'],
                    'meta_title' => $tool['meta_title'],
                    'meta_description' => $tool['meta_description'],
                    'status' => 'published',
                    'order' => 65 + $i,
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
                'slug' => 'usd-to-pkr-exchange-rate',
                'title' => 'USD to PKR Exchange Rate Today',
                'icon' => 'USD',
                'component' => 'CurrencyConverter',
                'short_description' => 'Live USD to PKR exchange rate converter — check today\'s dollar to rupee rate and convert any amount instantly.',
                'keywords' => ['usd to pkr', 'usd/pkr', 'usdpkr', 'dollar to rupee rate', 'pkr exchange rate', 'pakistani rupee exchange rate', 'today dollar rate in pakistan'],
                'meta_title' => 'USD to PKR Exchange Rate Today — Live Dollar to Rupee Converter',
                'meta_description' => 'Check today\'s live USD to PKR exchange rate and convert any amount between US Dollars and Pakistani Rupees instantly.',
                'guide_content' => <<<'HTML'
<h2>About the USD to PKR rate</h2>
<p>The exchange rate between the US Dollar and the Pakistani Rupee moves throughout each trading day based on supply and demand in the interbank currency market, State Bank of Pakistan (SBP) intervention, remittance inflows, import demand for dollars, and broader global dollar strength. This converter pulls a live reference rate so the figure above reflects current market conditions, not a fixed or outdated number.</p>
<h2>Interbank rate vs. open market rate</h2>
<p>Pakistan effectively has two commonly quoted USD/PKR rates: the interbank rate (used for bank transfers and large transactions) and the open market rate (what currency exchange counters offer for cash). The two normally track closely but can diverge somewhat during periods of high demand or currency controls — this tool reflects a market reference rate, not necessarily the exact retail cash rate available at any specific exchange counter on a given day.</p>
<h2>Why the rate matters beyond currency trading</h2>
<p>The USD/PKR rate affects far more than people directly buying or selling dollars — it feeds into import costs (and therefore prices of many everyday goods), the rupee value of remittances sent home by overseas Pakistanis, and Pakistan's overall inflation trajectory, since Pakistan imports a significant share of its energy and raw materials in dollar-priced terms.</p>
HTML,
                'faqs' => [
                    ['Is this the same rate my bank or exchange counter will give me?', 'This shows a live market reference rate. Banks and exchange counters typically add a small margin on top of (or below) the reference rate, so the exact rate you\'re offered for a real transaction may differ slightly — always confirm the actual rate with your specific bank or exchange counter before transacting.'],
                    ['Why does the USD to PKR rate keep changing?', 'It reflects real-time supply and demand for dollars in Pakistan\'s currency market, influenced by remittance flows, import/export activity, SBP intervention, and broader global currency movements — the same reasons any market-driven exchange rate fluctuates.'],
                    ['Can I convert other currencies too?', 'Yes — this tool supports conversion between over 30 currencies, not just USD and PKR. Use the "From" and "To" dropdowns to select any supported currency pair.'],
                ],
            ],
            [
                'slug' => 'mortgage-affordability-calculator',
                'title' => 'Mortgage Affordability Calculator',
                'icon' => 'MTG',
                'component' => 'MortgageAffordabilityCalculator',
                'short_description' => 'Find out how much home you can realistically afford based on your income, existing debts, down payment and a target debt-to-income ratio.',
                'keywords' => ['mortgage affordability calculator', 'how much house can i afford', 'home loan affordability calculator', 'how much mortgage can i qualify for'],
                'meta_title' => 'Mortgage Affordability Calculator: How Much House Can You Afford?',
                'meta_description' => 'Calculate how much home you can afford based on income, existing debt payments, down payment and interest rate, using a target debt-to-income ratio.',
                'guide_content' => <<<'HTML'
<h2>How affordability is actually calculated</h2>
<p>Lenders typically cap total monthly debt payments — including a new mortgage — at a percentage of gross or take-home income, commonly somewhere between 36% and 43%. This calculator works backward from that cap: it subtracts your existing debt payments from the maximum allowed total, then solves for the loan amount that produces exactly that remaining payment at your chosen rate and term.</p>
<h2>Why existing debt matters so much</h2>
<p>Two people with identical income can qualify for very different mortgage amounts if one has significant existing debt payments (car loans, other loans) eating into their allowed debt-to-income ratio. Paying down existing debt before a mortgage application can meaningfully increase how much home you can qualify for, sometimes more effectively than saving a larger down payment.</p>
<h2>Affordable vs. comfortable</h2>
<p>This calculates the maximum a lender's debt-to-income formula would typically allow — not necessarily what's comfortable to actually pay every month once other living costs are factored in. Many financial planners suggest targeting a mortgage payment meaningfully below the calculated maximum, leaving room for savings, emergencies, and lifestyle spending beyond bare affordability.</p>
HTML,
                'faqs' => [
                    ['Does this include property tax and insurance?', 'No — this estimates the loan-based affordability only. Property tax, homeowner\'s insurance, and maintenance costs would reduce the realistic affordable amount further and should be budgeted for separately.'],
                    ['What debt-to-income ratio should I use?', 'Lender requirements vary, but 36-43% total debt-to-income is a common range. Using a more conservative ratio than the maximum a lender might allow generally leaves more breathing room in your monthly budget.'],
                    ['Should I use gross or take-home income?', 'Lenders commonly qualify borrowers based on gross income, but budgeting comfortably is better anchored to take-home income after tax. Using take-home income here gives a more conservative, realistic affordability estimate.'],
                ],
            ],
            [
                'slug' => 'loan-comparison-calculator',
                'title' => 'Loan Comparison Calculator',
                'icon' => 'CMP',
                'component' => 'LoanComparisonCalculator',
                'short_description' => 'Compare two loan offers side by side — monthly payment and total interest — to see which one actually costs less.',
                'keywords' => ['loan comparison calculator', 'compare loan offers', 'which loan is cheaper calculator', 'loan comparison tool'],
                'meta_title' => 'Loan Comparison Calculator: Compare Two Loan Offers',
                'meta_description' => 'Compare two loan offers side by side on monthly payment and total interest to see which one actually costs less overall.',
                'guide_content' => <<<'HTML'
<h2>Why comparing loans isn't just about the interest rate</h2>
<p>The lowest advertised interest rate doesn't always mean the lowest total cost — a shorter term at a slightly higher rate can cost less in total interest than a longer term at a lower rate, because total interest depends on both the rate and how long the balance stays outstanding. Comparing monthly payment alone can also mislead, since a lower payment often just means a longer term and more total interest.</p>
<h2>What to actually compare</h2>
<p>Total interest paid over the full loan life is the most complete single number for comparing overall cost between two loans of the same amount. Monthly payment matters separately for budgeting purposes — the "best" loan for a given situation sometimes trades a higher monthly payment for significantly lower total interest, or vice versa, depending on what matters more to the borrower.</p>
<h2>Beyond the numbers this calculator shows</h2>
<p>Real loan offers often differ in origination fees, prepayment penalties, and other terms not captured by rate and term alone. Use this calculator to compare the core interest cost, then factor in any additional fees or conditions from the actual loan documents before making a final decision.</p>
HTML,
                'faqs' => [
                    ['Should I always pick the loan with lower total interest?', 'It\'s the right default comparison for pure cost, but not the only consideration — a loan with a higher monthly payment but lower total interest might strain your monthly budget more than it\'s worth, depending on your situation.'],
                    ['Does this account for fees?', 'No — this compares interest cost based on amount, rate and term only. Add any origination fees, processing charges or other costs to each loan\'s total separately for a complete comparison.'],
                    ['What if the two loans have different amounts?', 'You can still compare them directly — the calculator shows each loan\'s own total interest and payment independently, which is meaningful even when amounts differ, though comparing cost-per-rupee-borrowed can add useful context in that case.'],
                ],
            ],
            [
                'slug' => 'currency-depreciation-impact-calculator',
                'title' => 'Currency Depreciation Impact Calculator',
                'icon' => 'DEP',
                'component' => 'CurrencyDepreciationImpactCalculator',
                'short_description' => 'See exactly how much extra a purchase in foreign currency costs you after the rupee depreciates — enter an old and new exchange rate to find out.',
                'keywords' => ['currency depreciation calculator', 'rupee depreciation impact', 'exchange rate change calculator', 'what does a weaker rupee cost'],
                'meta_title' => 'Currency Depreciation Impact Calculator',
                'meta_description' => 'Calculate exactly how much more (or less) a foreign-currency purchase costs after an exchange rate change — useful for tracking rupee depreciation impact.',
                'guide_content' => <<<'HTML'
<h2>What this calculator shows</h2>
<p>Enter an amount in a foreign currency along with an old and a new exchange rate, and this calculator shows the exact rupee cost difference between the two — a concrete way to see what currency depreciation (or appreciation) actually costs on a real purchase, rather than just seeing a percentage move quoted in the news.</p>
<h2>Why this matters for imported goods</h2>
<p>Many everyday products — fuel, electronics, machinery, raw materials for local manufacturing — are priced in dollars even when sold locally in rupees. When the rupee depreciates against the dollar, the rupee cost of importing those same goods rises directly, which is one of the main channels through which currency depreciation feeds into domestic inflation.</p>
<h2>Using this to understand a specific headline</h2>
<p>News reports often state a currency move as a percentage (\"the rupee fell 3% against the dollar\") without translating it into a concrete cost. Plugging in a real purchase amount — a phone, a shipment of raw materials, an overseas tuition bill — turns an abstract percentage into a specific rupee figure that's easier to actually reason about.</p>
HTML,
                'faqs' => [
                    ['Does this work for currency appreciation too?', 'Yes — if the new rate is lower than the old rate (the rupee strengthened), the result shows a negative extra cost, meaning the purchase became cheaper in rupee terms.'],
                    ['Where can I find the "old" and "new" exchange rates to compare?', 'Use the State Bank of Pakistan\'s published rates, or check historical data from a currency data provider, to find the rate at two different points in time you want to compare.'],
                    ['Does this include the cost of exchanging currency itself?', 'No — this compares the underlying exchange rate only. Actual currency exchange usually involves an additional margin or fee on top of the reference rate, which this calculator does not include.'],
                ],
            ],
            [
                'slug' => 'gratuity-calculator',
                'title' => 'Gratuity Calculator',
                'icon' => 'GRT',
                'component' => 'GratuityCalculator',
                'short_description' => 'Estimate your end-of-service gratuity from your last drawn salary, years of service, and your employer\'s gratuity rate.',
                'keywords' => ['gratuity calculator', 'gratuity calculator pakistan', 'end of service benefit calculator', 'how to calculate gratuity'],
                'meta_title' => 'Gratuity Calculator: Estimate Your End-of-Service Benefit',
                'meta_description' => 'Calculate your estimated gratuity (end-of-service benefit) from your last drawn salary, years of service, and your employer\'s specific gratuity rate.',
                'guide_content' => <<<'HTML'
<h2>What gratuity is</h2>
<p>Gratuity is a lump-sum end-of-service benefit paid to an employee, typically based on their length of service and last drawn salary. It's separate from provident fund contributions and any final salary owed — a distinct benefit specifically tied to years worked at an organization.</p>
<h2>Why the rate is configurable here</h2>
<p>Gratuity formulas vary meaningfully by country, and even by specific employer policy within the same country — this calculator deliberately doesn't hard-code one formula. A common approach in Pakistan's private sector is roughly one month's last-drawn salary per completed year of service, but some schemes use a fraction of a month (like 15 or 26 days) per year instead. Check your specific employment contract, company policy, or applicable labor law for the exact rate that applies to you.</p>
<h2>What "last drawn salary" typically means</h2>
<p>Most gratuity schemes calculate based on basic salary at the time of leaving, not total compensation including allowances and bonuses — though this also varies by scheme. Confirm with your employer's HR policy or contract exactly which salary component your gratuity calculation is based on before relying on an estimate.</p>
HTML,
                'faqs' => [
                    ['Is gratuity the same as provident fund?', 'No — provident fund is typically a savings scheme with contributions from both employee and employer over time, while gratuity is a separate lump-sum benefit based on length of service, often funded entirely by the employer.'],
                    ['Do I need to complete a minimum service period to qualify?', 'Many gratuity schemes require a minimum period of continuous service (commonly a set number of years) before any gratuity becomes payable. Check your specific employment terms or applicable labor law for the exact threshold.'],
                    ['Is gratuity taxable?', 'Tax treatment of gratuity varies by country and sometimes by amount, with many jurisdictions offering at least a partial exemption. Confirm the specific tax treatment that applies to your situation with a tax advisor or your country\'s tax authority.'],
                ],
            ],
            [
                'slug' => 'import-duty-calculator',
                'title' => 'Import Duty & Landed Cost Calculator',
                'icon' => 'IMP',
                'component' => 'ImportDutyCalculator',
                'short_description' => 'Estimate the total landed cost of an imported item in PKR, including customs duty, sales tax and other charges — and see how exchange rate changes affect it.',
                'keywords' => ['import duty calculator pakistan', 'customs duty calculator', 'landed cost calculator', 'import tax calculator'],
                'meta_title' => 'Import Duty & Landed Cost Calculator (Pakistan)',
                'meta_description' => 'Estimate the total landed cost in PKR of an item imported into Pakistan, including customs duty, sales tax and other charges, based on the current exchange rate.',
                'guide_content' => <<<'HTML'
<h2>What "landed cost" includes</h2>
<p>Landed cost is the total cost of getting an imported item to its final destination — not just the item's sticker price abroad, but the rupee equivalent of that price plus customs duty, sales tax, and other charges like handling or regulatory fees. It's the number that actually determines what an imported item costs a buyer or business, not the foreign-currency price alone.</p>
<h2>Why sales tax is calculated on value plus duty</h2>
<p>In many tax systems, including Pakistan's, sales tax on imports is applied to the value of the goods <em>after</em> customs duty has already been added — not to the original item value alone. This "tax on tax" effect means the sales tax line is calculated on a larger base than just the item's price, which this calculator reflects by adding duty first before applying the sales tax percentage.</p>
<h2>Why the exchange rate matters as much as the duty rate</h2>
<p>Every cost in this calculation scales directly with the exchange rate entered — a weaker rupee raises the landed cost of an import just as surely as a higher duty rate would, even if the foreign-currency price and duty rate never change. This is one of the most direct channels through which currency depreciation raises domestic prices for imported goods.</p>
HTML,
                'faqs' => [
                    ['Are duty and tax rates the same for every product?', 'No — customs duty and sales tax rates vary significantly by product category (HS code) and can change with policy updates. Check Pakistan\'s current tariff schedule or consult a customs agent for the exact rates that apply to a specific item.'],
                    ['Does this include shipping and insurance costs?', 'No — this estimates duty, sales tax and other charges based on the item\'s value alone. Add shipping and insurance costs to the item value first if you want a landed cost that includes them, since duty is often calculated on a CIF (cost, insurance, freight) basis in practice.'],
                    ['Why is my actual customs bill different from this estimate?', 'Actual customs assessments can differ due to valuation methods, product-specific exemptions, regulatory duties, or additional charges not modeled here. Treat this as a planning estimate, not a substitute for an official customs assessment.'],
                ],
            ],
        ];
    }
}
