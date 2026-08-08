<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolFaq;
use Illuminate\Database\Seeder;

/**
 * Third batch of post-launch tool additions (Aug 2026 growth push): credit
 * card minimum payment, capital gains tax, loan-to-value, break-even point,
 * effective annual rate, digital storage converter — checked against all
 * 59 existing tools for overlap. Idempotent (updateOrCreate).
 */
class NewToolsBatch3Seeder extends Seeder
{
    public function run(): void
    {
        $moneyCategory = Category::where('type', 'tool')->where('slug', 'money-tax-business')->first();
        $unitsCategory = Category::where('type', 'tool')->where('slug', 'local-regional-units')->first();

        foreach ($this->tools() as $i => $tool) {
            $categoryId = $tool['category'] === 'units' ? $unitsCategory?->id : $moneyCategory?->id;

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
                    'order' => 59 + $i,
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
                'slug' => 'credit-card-minimum-payment-calculator',
                'title' => 'Credit Card Minimum Payment Calculator',
                'icon' => 'CCM',
                'component' => 'CreditCardMinimumPaymentCalculator',
                'category' => 'money',
                'short_description' => 'See how long it really takes — and how much interest you really pay — if you only ever pay the minimum on a credit card.',
                'keywords' => ['credit card minimum payment calculator', 'minimum payment trap', 'how long to pay off credit card minimum', 'credit card interest calculator'],
                'meta_title' => 'Credit Card Minimum Payment Calculator: The Real Cost',
                'meta_description' => 'See exactly how long it takes to clear a credit card balance paying only the minimum, and the total interest that "minimum payment trap" actually costs.',
                'guide_content' => <<<'HTML'
<h2>Why minimum payments are different from a fixed payoff plan</h2>
<p>A credit card minimum payment is usually calculated as a percentage of the current balance (commonly 3-5%), or a flat floor amount, whichever is higher. Because it's a percentage of a shrinking balance, the required payment itself shrinks every month right alongside the balance — unlike a fixed loan payment, which stays constant until the debt is gone.</p>
<h2>Why that makes payoff drag on for years</h2>
<p>As both the balance and the required payment shrink together, an ever-smaller amount goes toward actually reducing what's owed each month, while interest keeps accruing on whatever remains. On a typical credit card balance and interest rate, paying only the minimum can take many years to clear — and the total interest paid along the way can end up costing more than the original balance itself.</p>
<h2>What actually breaks the cycle</h2>
<p>Paying any fixed amount above the calculated minimum — even a modest fixed top-up — breaks this pattern, because a fixed payment doesn't shrink as the balance does. This calculator's numbers are precisely why "pay more than the minimum" is close to universal advice from anyone who has looked at the actual math of revolving credit card debt.</p>
HTML,
                'faqs' => [
                    ['Why does the minimum payment keep shrinking?', 'Because it\'s calculated as a percentage of the current balance each month. As the balance goes down (even slowly), the required minimum goes down with it, which is exactly what drags payoff out for years.'],
                    ['Is there a way to make my minimum payment fixed?', 'Some card issuers let you set up a fixed payment amount instead of the calculated minimum, or you can simply choose to pay a consistent fixed amount above whatever the minimum happens to be each month — both break the shrinking-payment pattern.'],
                    ['Does making only minimum payments hurt my credit score?', 'Paying at least the minimum on time generally protects your payment history, which is a major credit score factor. But a high balance relative to your credit limit (utilization) — which stays high for years under minimum-only payments — can hurt your score independently of whether payments are on time.'],
                ],
            ],
            [
                'slug' => 'capital-gains-tax-calculator',
                'title' => 'Capital Gains Tax Calculator',
                'icon' => 'CGT',
                'component' => 'CapitalGainsTaxCalculator',
                'category' => 'money',
                'short_description' => 'Estimate the tax owed on a capital gain from selling an asset — stocks, property, crypto — including a long-term holding discount if your jurisdiction offers one.',
                'keywords' => ['capital gains tax calculator', 'capital gains calculator', 'tax on stock sale calculator', 'long term vs short term capital gains'],
                'meta_title' => 'Capital Gains Tax Calculator: Estimate Tax on a Sale',
                'meta_description' => 'Calculate estimated capital gains tax on selling an asset from purchase price, sale price and your tax rate, with an optional long-term holding discount.',
                'guide_content' => <<<'HTML'
<h2>What counts as a capital gain</h2>
<p>A capital gain is the profit from selling an asset for more than you paid for it — after accounting for any costs directly tied to the purchase or sale, like brokerage fees or transaction costs. It applies to a wide range of assets: stocks, property, cryptocurrency, and other investments, though the specific tax treatment varies significantly by country and asset type.</p>
<h2>Why holding period often matters</h2>
<p>Many tax systems distinguish between short-term gains (assets held under a set period, often a year) and long-term gains (held longer), taxing long-term gains at a reduced effective rate to encourage longer-term investment over rapid trading. The exact discount and holding-period threshold vary by country — this calculator models it as a simple percentage reduction off your stated rate, which approximates the effect without needing to encode every jurisdiction's specific rules.</p>
<h2>Why this is an estimate, not a filing figure</h2>
<p>Actual capital gains tax rules involve details this calculator deliberately simplifies away — exemption thresholds, offsetting losses against gains, primary residence exemptions, and different rates for different asset classes are all common wrinkles. Use this for a quick estimate before a sale, then confirm the precise figure with your country's actual tax rules or a tax advisor before filing.</p>
HTML,
                'faqs' => [
                    ['Does this account for capital losses?', 'No — this calculates tax on a single gain in isolation. Many tax systems let you offset gains with losses from other sales in the same period, which would reduce the actual tax owed below this estimate.'],
                    ['Is my primary home exempt from capital gains tax?', 'In many countries, yes, up to certain conditions or limits, but rules vary significantly. Check your specific country\'s rules before assuming an exemption applies to a property sale.'],
                    ['What holding period counts as "long-term"?', 'This varies by country and sometimes by asset type — commonly one year, but not universally. Confirm the exact threshold under your jurisdiction\'s rules before relying on the long-term rate.'],
                ],
            ],
            [
                'slug' => 'loan-to-value-calculator',
                'title' => 'Loan-to-Value (LTV) Calculator',
                'icon' => 'LTV',
                'component' => 'LoanToValueCalculator',
                'category' => 'money',
                'short_description' => 'Calculate your loan-to-value ratio from a property\'s value and loan amount — a key number lenders use to price and approve mortgages.',
                'keywords' => ['loan to value calculator', 'ltv calculator', 'ltv ratio mortgage', 'down payment percentage calculator'],
                'meta_title' => 'Loan-to-Value (LTV) Calculator for Mortgages',
                'meta_description' => 'Calculate your loan-to-value (LTV) ratio from a property value and loan amount, and see how it compares to common lender thresholds.',
                'guide_content' => <<<'HTML'
<h2>What LTV measures</h2>
<p>Loan-to-value is the loan amount expressed as a percentage of the property's value — an 80% LTV means the loan covers 80% of the property's value, with the remaining 20% coming from the buyer's down payment. It's one of the primary numbers lenders use to assess risk on a mortgage.</p>
<h2>Why lower LTV usually means better terms</h2>
<p>A lower LTV means the buyer has more equity in the property from day one, which reduces the lender's risk if the borrower defaults and the property needs to be sold. Lenders commonly offer better interest rates at lower LTV bands, and many require additional mortgage insurance above certain thresholds (80% or 90% are common cutoffs) specifically to offset the higher risk of a smaller down payment.</p>
<h2>Why this number matters beyond loan approval</h2>
<p>LTV isn't only relevant when first taking out a mortgage — refinancing, requesting removal of mortgage insurance, or taking out a second loan against the same property (like a home equity loan) all typically depend on the current LTV, recalculated against the property's present value rather than its original purchase price.</p>
HTML,
                'faqs' => [
                    ['What LTV do I need to avoid mortgage insurance?', 'This varies by lender and country, but 80% or lower is a commonly cited threshold. Confirm your specific lender\'s policy, since thresholds and insurance requirements differ.'],
                    ['Can LTV change after I take out the mortgage?', 'Yes — as you pay down the loan and/or the property\'s market value changes, your LTV recalculates. Rising property values can lower your effective LTV even without extra payments, which is why some borrowers request an LTV reassessment to remove mortgage insurance early.'],
                    ['Is a higher down payment always better?', 'A lower LTV generally means better loan terms and lower risk, but it also ties up more cash in the property. Whether maximizing the down payment is the right choice depends on what else that cash could otherwise be used for, including its opportunity cost.'],
                ],
            ],
            [
                'slug' => 'break-even-point-calculator',
                'title' => 'Break-Even Point Calculator',
                'icon' => 'BEP',
                'component' => 'BreakEvenPointCalculator',
                'category' => 'money',
                'short_description' => 'Find out how many units you need to sell to cover your costs, from fixed costs, selling price and variable cost per unit.',
                'keywords' => ['break even calculator', 'break even point calculator', 'how many units to break even', 'break even analysis'],
                'meta_title' => 'Break-Even Point Calculator: Units Needed to Cover Costs',
                'meta_description' => 'Calculate your break-even point — the number of units you need to sell to cover all costs — from fixed costs, price per unit and variable cost per unit.',
                'guide_content' => <<<'HTML'
<h2>What break-even point means</h2>
<p>The break-even point is the sales volume at which total revenue exactly equals total costs — no profit, no loss. Selling below that volume means a loss; selling above it means profit. It's one of the most fundamental numbers in evaluating whether a business idea or pricing plan is viable before committing significant money to it.</p>
<h2>Fixed costs vs. variable costs</h2>
<p>Fixed costs stay the same regardless of how many units are sold — rent, salaries, insurance. Variable costs scale directly with each unit sold — materials, packaging, per-unit shipping. The difference between the selling price and the variable cost per unit is the "contribution margin" — how much each individual sale contributes toward covering the fixed costs before any profit begins.</p>
<h2>How to use the result</h2>
<p>Once fixed costs are covered at the break-even point, every additional unit sold contributes its full margin directly to profit — which is why understanding this number changes how a price change or a cost increase should be evaluated. A price cut that looks appealing for competitiveness might raise the break-even point substantially, requiring meaningfully higher sales volume just to reach the same starting position.</p>
HTML,
                'faqs' => [
                    ['What if my variable cost is higher than my price?', 'Then break-even is mathematically impossible — every unit sold loses money regardless of volume, since there\'s no positive contribution margin to work with. The price or the variable cost needs to change before the business model can work.'],
                    ['Does break-even point include a profit target?', 'No — the standard break-even point is where profit is exactly zero. To find the sales volume needed for a specific profit target, add that target amount to the fixed costs before dividing by the contribution margin.'],
                    ['How often should I recalculate this?', 'Whenever fixed costs, pricing, or variable costs change meaningfully — a rent increase, a supplier price change, or a pricing strategy shift all move the break-even point and are worth recalculating around.'],
                ],
            ],
            [
                'slug' => 'effective-annual-rate-calculator',
                'title' => 'Effective Annual Rate (EAR) Calculator',
                'icon' => 'EAR',
                'component' => 'EffectiveAnnualRateCalculator',
                'category' => 'money',
                'short_description' => 'Convert a nominal (stated) annual interest rate into its true effective annual rate, accounting for how often it compounds.',
                'keywords' => ['effective annual rate calculator', 'ear calculator', 'nominal vs effective interest rate', 'apr to apy calculator'],
                'meta_title' => 'Effective Annual Rate (EAR) Calculator',
                'meta_description' => 'Convert a nominal annual interest rate into its effective annual rate based on compounding frequency — see the real rate behind the stated one.',
                'guide_content' => <<<'HTML'
<h2>Nominal rate vs. effective rate</h2>
<p>The nominal rate is the stated annual interest rate before accounting for compounding — the number usually advertised. The effective annual rate (EAR) accounts for how often interest compounds within the year, and is always equal to or higher than the nominal rate whenever compounding happens more than once a year, since each compounding period earns interest on interest already accrued.</p>
<h2>Why compounding frequency matters</h2>
<p>A 12% nominal rate compounded monthly doesn't simply mean 12% a year — it means 1% is applied each month, and each month's interest itself starts earning interest for the rest of the year. Run through the math and a 12% nominal rate compounded monthly works out to roughly 12.68% effectively — a small-looking gap that grows more significant at higher rates or more frequent compounding.</p>
<h2>Why this matters for comparing offers</h2>
<p>Two loans or savings products quoting the same nominal rate can have meaningfully different real costs or returns if they compound at different frequencies. Comparing the effective annual rate, rather than the advertised nominal rate, is the only way to fairly compare products that compound differently — this is exactly why regulators in many countries require lenders to disclose an effective or "annual percentage rate" figure alongside any nominal rate.</p>
HTML,
                'faqs' => [
                    ['Is effective rate always higher than nominal rate?', 'Yes, whenever compounding happens more than once a year — the two are only equal when compounding is exactly annual. More frequent compounding always produces a higher effective rate for the same nominal rate.'],
                    ['Which rate do lenders usually advertise?', 'It varies — some advertise the nominal rate (which looks lower), others the effective rate. Always check which one is quoted, and use this calculator to convert between them for a fair comparison across offers.'],
                    ['Does this apply to both loans and savings?', 'Yes — the same math applies whether interest is being paid to you (savings, investments) or charged to you (loans, credit cards). A higher effective rate is good news for a saver and bad news for a borrower.'],
                ],
            ],
            [
                'slug' => 'digital-storage-converter',
                'title' => 'Digital Storage Converter',
                'icon' => 'GB',
                'component' => 'DigitalStorageConverter',
                'category' => 'units',
                'short_description' => 'Convert between digital storage units — bits, bytes, KB, MB, GB, TB — in both decimal (what storage is sold in) and binary (what your OS reports) systems.',
                'keywords' => ['digital storage converter', 'gb to mb converter', 'kb mb gb tb converter', 'byte converter', 'file size converter'],
                'meta_title' => 'Digital Storage Converter: Bits, Bytes, KB, MB, GB, TB',
                'meta_description' => 'Convert between digital storage units — bits, bytes, KB, MB, GB, TB, PB — in both decimal (storage marketing) and binary (operating system) unit systems.',
                'guide_content' => <<<'HTML'
<h2>Why a "1TB" drive shows less than 1TB on your computer</h2>
<p>Storage manufacturers measure capacity in decimal units, where 1 kilobyte = 1,000 bytes, 1 megabyte = 1,000 kilobytes, and so on — consistent with how every other metric measurement (kilometres, kilograms) works. Operating systems, however, traditionally report storage in binary units, where 1 kibibyte = 1,024 bytes. The two systems disagree by about 7% at the gigabyte scale and more at larger scales, which is exactly why a drive sold as "1TB" shows up as roughly 931GiB in a binary-reporting file explorer — no storage is missing, it's simply being counted differently.</p>
<h2>Decimal vs. binary, explained</h2>
<p>Decimal units (KB, MB, GB, TB) use clean powers of 1,000 and are the units used in storage marketing and most network speed measurements. Binary units (KiB, MiB, GiB, TiB) use powers of 1,024 and are what many operating systems, including Windows' file explorer, actually display — sometimes using decimal unit labels while performing binary-scale math, which is part of why the confusion persists.</p>
<h2>When each system is used</h2>
<p>RAM capacity is almost always a genuinely binary quantity (8GB of RAM is exactly 8 × 1024³ bytes) because of how memory chips are physically organized. Storage drive capacity and internet/network speeds are conventionally decimal. Knowing which convention applies to what you're measuring avoids the classic "why is my storage smaller than advertised" confusion.</p>
HTML,
                'faqs' => [
                    ['Why does my 1TB drive show 931GB in Windows?', 'Windows reports storage using binary units (multiples of 1,024) while labeling them with decimal unit names (GB instead of GiB). 1TB in decimal terms (1,000,000,000,000 bytes) equals approximately 931GiB in binary terms — no capacity is actually missing.'],
                    ['Is internet speed measured in bits or bytes?', 'Internet and network speeds are almost universally advertised in bits per second (Mbps, Gbps), while file sizes are almost universally measured in bytes. Since 1 byte = 8 bits, a "100 Mbps" connection tops out around 12.5 MB per second in practice, not 100.'],
                    ['Should I use decimal or binary for RAM?', 'RAM capacity is a physically binary quantity due to how memory addressing works, so binary units (GiB) are technically the accurate description, even though RAM is almost always marketed and discussed using decimal-style labels (GB) in casual use.'],
                ],
            ],
        ];
    }
}
