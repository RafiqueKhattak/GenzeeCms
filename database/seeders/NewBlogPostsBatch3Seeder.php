<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Third batch of post-launch blog additions (Aug 2026 growth push): six
 * evergreen posts tied to the six NewToolsBatch3Seeder calculators.
 * Idempotent (updateOrCreate) so it's safe to re-run on every deploy.
 */
class NewBlogPostsBatch3Seeder extends Seeder
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
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 2) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'status' => 'published',
                    'published_at' => now()->subDays(18 - $i),
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
                'slug' => 'credit-card-minimum-payment-trap-real-numbers',
                'title' => 'The Credit Card Minimum Payment Trap: Why It Takes So Long, With Real Numbers',
                'excerpt' => 'Paying only the minimum on a credit card feels manageable month to month — but the math behind it explains why balances can take years, or even decades, to clear.',
                'meta_title' => 'The Credit Card Minimum Payment Trap Explained',
                'meta_description' => 'Paying only credit card minimums can take years to clear a balance and can cost more in interest than the original debt. Here is the actual math behind the "minimum payment trap."',
                'tags' => ['Debt'],
                'body' => <<<'HTML'
<p class="lead">A credit card minimum payment is designed to feel manageable — small enough that almost anyone can make it. That's exactly what makes it dangerous: a payment engineered to be easy is also, by the same design, engineered to shrink the balance as slowly as the issuer can get away with.</p>
<h2>How the minimum is actually calculated</h2>
<p>Most issuers calculate the minimum as a percentage of the current balance — commonly 3-5% — or a flat floor amount, whichever is higher. Because it's a percentage of a moving target, the required payment falls every single month right alongside the balance, unlike a fixed loan installment that stays constant until fully paid off.</p>
<h2>A concrete example</h2>
<p>Take a 150,000 balance at 30% APR, with a 5% minimum payment (floor 2,000). Paying only that shrinking minimum every month takes roughly six years and nine months to clear — and along the way, total interest paid comes to over 126,000, close to the size of the original balance itself. The card effectively costs the borrower nearly double the amount originally charged, purely through the mechanics of minimum-only payments.</p>
<h2>Why the trap is so easy to fall into</h2>
<p>Nothing about a minimum-only payment feels irresponsible in any given month — the bill gets paid, no late fees, no default. The cost is invisible in the short term and only becomes obvious when totalled over years, which is precisely why it doesn't feel urgent to fix in the moment it's happening.</p>
<h2>What actually breaks the pattern</h2>
<p>A fixed payment — any amount that doesn't shrink as the balance does — breaks the cycle immediately, because it captures an increasing share of principal every month instead of a shrinking one. Even a modest, consistent overpayment above the calculated minimum can cut years off the payoff timeline and save a meaningful share of the total interest, without requiring the payment to be dramatically larger.</p>
<h2>Where to start if you are in this position</h2>
<p>The most direct fix is simple in principle even if not always easy in practice: pick a fixed payment amount above the current minimum and stick to it every month regardless of how the calculated minimum moves. Running your own balance and rate through a payoff calculator, comparing minimum-only against a chosen fixed payment, turns an abstract "pay more than the minimum" piece of advice into a concrete number worth committing to.</p>
<h2>Why balance transfers are not automatically a fix</h2>
<p>Moving a balance to a new card with a promotional low or 0% rate can genuinely help, but only if the payoff behavior changes alongside it — transferring a balance and then continuing to pay only the new card's minimum simply relocates the same slow-payoff pattern to a different account, often with a transfer fee added on top. A balance transfer works best paired with a fixed, above-minimum payment plan from the start, not as a standalone solution.</p>
HTML,
            ],
            [
                'slug' => 'capital-gains-tax-101-short-vs-long-term',
                'title' => 'Capital Gains Tax 101: Short-Term vs. Long-Term, Explained',
                'excerpt' => 'How long you hold an investment before selling can change the tax rate on the profit significantly. Here is why that distinction exists and how to think about it.',
                'meta_title' => 'Capital Gains Tax: Short-Term vs Long-Term Explained',
                'meta_description' => 'Short-term and long-term capital gains are often taxed at different rates. Here is why the distinction exists and how holding period affects tax on investment profit.',
                'tags' => ['Investing'],
                'body' => <<<'HTML'
<p class="lead">Selling an investment for a profit triggers a tax bill in most jurisdictions — but how much tax depends on more than just the size of the gain. In many tax systems, how long the asset was held before selling changes the rate substantially.</p>
<h2>What counts as a capital gain</h2>
<p>A capital gain is the profit realized when an asset — stocks, property, cryptocurrency, and other investments — sells for more than its purchase price, after accounting for directly related costs like brokerage fees. It's only realized (and generally only taxed) when the asset is actually sold, not simply while its paper value rises.</p>
<h2>Why short-term and long-term are taxed differently</h2>
<p>Many tax systems apply a reduced effective rate to gains on assets held beyond a set threshold — commonly a year, though this varies by country — specifically to encourage longer-term investment over rapid, speculative trading. Short-term gains are frequently taxed at the same rate as ordinary income, which is often meaningfully higher than the long-term rate.</p>
<h2>The practical effect on a sale decision</h2>
<p>Because the tax difference between short-term and long-term treatment can be substantial, the calendar can genuinely matter to an investment decision: selling an asset a few weeks before it crosses the long-term threshold, purely to lock in a gain, can mean paying a noticeably higher tax rate than waiting slightly longer would have required. This isn't a reason to hold every position indefinitely, but it's worth factoring into timing when a sale isn't otherwise urgent.</p>
<h2>What this calculation typically leaves out</h2>
<p>Real capital gains tax calculations often involve more than a single flat rate: exemption thresholds, the ability to offset gains with losses from other sales in the same period, and special treatment for a primary residence are all common in various tax systems. A quick estimate is useful for planning a sale, but the exact figure for filing should come from your jurisdiction's actual rules or a tax advisor, not a simplified calculator alone.</p>
<h2>Why tracking cost basis matters from day one</h2>
<p>Every capital gains calculation starts from the purchase price (the "cost basis"), including any costs that legitimately add to it — a home's cost basis, for example, can include significant capital improvements, not just the original purchase price. Keeping records of an asset's true cost basis from the moment it's acquired, rather than trying to reconstruct it years later at the point of sale, makes an eventual tax calculation both easier and more accurate.</p>
HTML,
            ],
            [
                'slug' => 'what-is-loan-to-value-ltv-mortgage-rate',
                'title' => 'What Is Loan-to-Value (LTV) and Why It Decides Your Mortgage Rate',
                'excerpt' => 'LTV is one of the first numbers a mortgage lender calculates — and it can move your interest rate and whether you need extra insurance. Here is how it works.',
                'meta_title' => 'Loan-to-Value (LTV) Explained: How It Affects Your Mortgage',
                'meta_description' => 'Loan-to-value (LTV) measures your loan against your property\'s value, and it directly affects mortgage rates and insurance requirements. Here is how it works.',
                'tags' => ['Loans'],
                'body' => <<<'HTML'
<p class="lead">Long before a mortgage application looks at income or credit history in detail, lenders calculate one number that shapes almost everything else about the offer: loan-to-value, or LTV.</p>
<h2>The basic calculation</h2>
<p>LTV is simply the loan amount divided by the property's value, expressed as a percentage. Borrow 12,000,000 against a property worth 15,000,000, and the LTV is 80% — the loan covers 80% of the property's value, with the remaining 20% coming from the buyer's down payment or existing equity.</p>
<h2>Why lenders care so much about this number</h2>
<p>LTV is a direct measure of how much cushion exists if the property needs to be sold to recover the loan — say, after a default. A lower LTV means the buyer has more equity in the property from the start, which reduces the lender's exposure if property values fall or the borrower can't keep up payments. This is why LTV, more than almost any other single number, drives both the interest rate offered and whether additional protections like mortgage insurance are required.</p>
<h2>Common LTV thresholds worth knowing</h2>
<p>Many lenders treat 80% LTV as a meaningful line: below it, borrowers often get more favorable rates and avoid extra insurance requirements; above it, lenders frequently require private mortgage insurance or charge a rate premium to offset the added risk. LTV above roughly 90-95% is often treated as high-risk territory, with stricter approval criteria or outright refusal from some lenders.</p>
<h2>How LTV changes after the loan is taken out</h2>
<p>LTV isn't fixed at closing — it moves as the loan balance is paid down and as the property's market value changes. Rising property values can lower a borrower's effective LTV even without any extra payments, which is why some borrowers proactively request a reassessment to remove mortgage insurance once their equity position has genuinely improved, rather than waiting out the original schedule.</p>
<h2>Balancing a lower LTV against other priorities</h2>
<p>A larger down payment lowers LTV and generally improves loan terms, but it also ties up more cash in a single asset. Whether it's worth stretching for a lower LTV depends on what else that cash could be doing — clearing higher-interest debt first, or maintaining a sufficient emergency fund, can be a better use of the same money than shaving a few points off LTV, depending on the specific numbers involved.</p>
<h2>LTV on a refinance is calculated differently</h2>
<p>When refinancing an existing mortgage, LTV is recalculated against the property's current appraised value, not its original purchase price — which means a home that has appreciated since purchase can produce a noticeably better LTV on refinance than the original loan had, even without extra principal payments. This is one of the more overlooked reasons a refinance can unlock better terms: the property side of the equation may have moved favorably on its own.</p>
HTML,
            ],
            [
                'slug' => 'how-to-find-break-even-point-business',
                'title' => 'How to Find Your Business\'s Break-Even Point (And Why It Matters)',
                'excerpt' => 'Before asking whether a business or product idea will be profitable, the more useful question is: how much do you actually need to sell just to stop losing money?',
                'meta_title' => 'How to Calculate Your Business Break-Even Point',
                'meta_description' => 'Break-even point is the sales volume where revenue exactly covers costs. Here is how to calculate it from fixed costs, price and variable cost, and why it matters.',
                'tags' => ['Investing'],
                'body' => <<<'HTML'
<p class="lead">Before a business plan gets to the exciting question of profit, there's a more foundational one worth answering first: at what sales volume does the business stop losing money? That number is the break-even point, and it's one of the most useful early filters for evaluating whether an idea is realistic.</p>
<h2>The two types of costs behind the calculation</h2>
<p>Fixed costs stay constant regardless of sales volume — rent, salaries, insurance, loan payments. Variable costs scale directly with each unit produced or sold — raw materials, packaging, per-unit shipping. Break-even analysis depends on cleanly separating the two, since it's built entirely around how they behave differently as volume changes.</p>
<h2>Contribution margin: the number that does the work</h2>
<p>Subtract variable cost per unit from the selling price per unit, and the result is the contribution margin — how much each individual sale contributes toward covering fixed costs before any profit begins. Break-even point, in units, is simply fixed costs divided by contribution margin: the number of sales needed for accumulated contributions to exactly offset the fixed costs.</p>
<h2>A worked example</h2>
<p>With 500,000 in fixed costs, a selling price of 1,200 and a variable cost of 700 per unit, the contribution margin is 500 per unit. Dividing 500,000 by 500 gives a break-even point of 1,000 units — meaning the 1,001st unit sold in the period is the first one that actually contributes to profit, with every unit before it going toward covering fixed costs.</p>
<h2>Why this changes how price changes should be evaluated</h2>
<p>A price cut aimed at boosting competitiveness looks appealing on its own, but it directly shrinks the contribution margin per unit — which raises the break-even point, sometimes substantially. A seemingly modest price reduction can require a meaningfully higher sales volume just to reach the same break-even position as before, a trade-off that's easy to overlook without running the actual numbers.</p>
<h2>Using break-even analysis beyond a single product</h2>
<p>The same logic extends to bigger decisions: opening a new location, hiring an additional employee, or investing in new equipment all add fixed costs that raise the break-even point for the business as a whole. Running a break-even calculation before committing to a major fixed cost is a quick sanity check on whether the expected additional sales volume is actually realistic before signing any contracts.</p>
<h2>What break-even analysis does not tell you</h2>
<p>Reaching the break-even point confirms costs are covered, but it says nothing about whether the required sales volume is realistic given actual market demand, competition, or capacity constraints. A break-even point of 1,000 units a month is only meaningful alongside a genuine estimate of whether 1,000 units a month can actually be sold — treating the calculation as a full business case on its own, rather than one input into a larger judgment, is a common and avoidable mistake.</p>
HTML,
            ],
            [
                'slug' => 'nominal-vs-effective-interest-rate',
                'title' => 'Nominal vs. Effective Interest Rate: The Difference That Actually Costs You',
                'excerpt' => 'Two loans quoting the identical interest rate can cost meaningfully different amounts, depending on how often that rate compounds. Here is why.',
                'meta_title' => 'Nominal vs Effective Interest Rate: What Actually Costs More',
                'meta_description' => 'The stated (nominal) interest rate and the true (effective) rate can differ meaningfully depending on compounding frequency. Here is why the gap matters when comparing offers.',
                'tags' => ['Interest Rates'],
                'body' => <<<'HTML'
<p class="lead">Two loans advertising the same "12% interest rate" can cost genuinely different amounts — not because either lender is lying, but because the stated rate and the rate that actually applies over a year are not always the same number.</p>
<h2>What "nominal" actually means</h2>
<p>The nominal rate is the stated annual rate before accounting for how often it compounds within the year. It's the number most commonly advertised, precisely because it's usually the smaller-looking figure compared to the effective rate once compounding is factored in.</p>
<h2>What compounding actually does</h2>
<p>When interest compounds more than once a year, each compounding period's interest itself starts earning interest for the rest of the year — interest on interest, not just on the original amount. A 12% nominal rate compounded monthly means 1% is applied every month, and each month's 1% then compounds against a slightly larger base for the remaining months, producing more than a simple 12% total by year's end.</p>
<h2>The actual gap, with numbers</h2>
<p>Run the math on a 12% nominal rate compounded monthly, and the effective annual rate works out to roughly 12.68% — not 12%. The gap between 12% and 12.68% might look small in isolation, but it grows more significant at higher rates or with more frequent compounding (daily compounding produces a larger gap than monthly, for the same nominal rate), and it compounds itself across every year a loan or investment runs.</p>
<h2>Why this matters when comparing offers</h2>
<p>Two products quoting the identical nominal rate but compounding at different frequencies are not actually offering the same deal — the one compounding more frequently is more expensive for a loan and more rewarding for a savings product. Comparing the effective annual rate, rather than the advertised nominal figure, is the only reliable way to fairly compare offers with different compounding structures side by side.</p>
<h2>Why regulators require effective-rate disclosure</h2>
<p>Because nominal rates alone can make genuinely different offers look identical, many countries require lenders to disclose an effective or "annual percentage rate" figure specifically so borrowers can compare products on equal footing. Where that disclosure exists, checking it directly saves the trouble of converting nominal rates by hand — where it doesn't, converting the nominal rate yourself is the only way to see the real cost.</p>
<h2>The same math, working in your favor</h2>
<p>Everything above sounds like a warning about borrowing costs, but the identical mechanism works in a saver's favor on the other side of the transaction: a savings account or investment quoting a nominal return compounds the same way, meaning the real annual growth is slightly higher than the advertised headline figure suggests. More frequent compounding is bad news when you're the one paying interest, and good news when you're the one earning it — the formula doesn't care which side of the transaction you're on.</p>
HTML,
            ],
            [
                'slug' => 'gb-vs-gib-storage-explained',
                'title' => 'Why Your Storage Never Quite Matches What You Bought: GB vs. GiB Explained',
                'excerpt' => 'A "1TB" drive shows up as roughly 931GB on your computer — and no data is missing. The explanation is two different, both legitimate, ways of counting bytes.',
                'meta_title' => 'GB vs GiB: Why Your Storage Shows Less Than You Bought',
                'meta_description' => 'A 1TB drive showing as 931GB on your computer is not missing storage — it is decimal versus binary units disagreeing. Here is exactly why, and where each convention is used.',
                'tags' => ['Technology'],
                'body' => <<<'HTML'
<p class="lead">Buy a "1TB" hard drive, plug it in, and check its capacity in your file explorer — and it will very likely show something like 931GB, not 1,000GB. Nothing was returned, nothing is defective. Two different, both technically correct, ways of counting bytes are simply disagreeing with each other.</p>
<h2>Decimal units: how storage is sold</h2>
<p>Storage manufacturers measure capacity in decimal units, consistent with how every other metric measurement works: 1 kilobyte = 1,000 bytes, 1 megabyte = 1,000 kilobytes, 1 gigabyte = 1,000 megabytes, and so on in clean powers of 1,000. This is a defensible, standards-compliant way to measure — it's literally what "kilo," "mega," and "giga" mean in the International System of Units used across science and engineering.</p>
<h2>Binary units: how your operating system counts</h2>
<p>Computers, at the hardware level, work in powers of two, and early computing conventions carried that into how software reports storage: 1 kibibyte = 1,024 bytes, 1 mebibyte = 1,024 kibibytes, and so on in powers of 1,024. Many operating systems — Windows' file explorer among the most visible examples — report storage this way, while confusingly still labeling the units "KB," "MB," "GB" rather than the technically correct "KiB," "MiB," "GiB."</p>
<h2>Why the gap grows at larger scales</h2>
<p>The difference between 1,000 and 1,024 is about 2.4% at the kilobyte scale, but it compounds at each larger unit — by the terabyte scale, the gap between decimal and binary measurement widens to roughly 10%. That's why the discrepancy becomes genuinely noticeable — hundreds of gigabytes "missing" — on larger modern drives, even though the underlying percentage gap has been consistent all along.</p>
<h2>Where each convention actually applies</h2>
<p>RAM capacity is a genuinely binary quantity because of how memory chips are physically addressed — 8GB of RAM really is exactly 8 × 1,024³ bytes, no ambiguity involved. Storage drive capacity and network/internet speeds, on the other hand, are conventionally decimal, following the same metric-prefix logic as every other unit of measurement. Software that reports storage using binary math but decimal-style labels is the actual source of the confusion, not either measurement system being "wrong" on its own.</p>
<h2>The practical takeaway</h2>
<p>Nothing needs fixing when a drive shows less capacity than its label — it's simply being measured with a different, equally valid ruler. Understanding which convention applies to what you're looking at (decimal for what you bought, likely binary for what your OS displays) removes the mystery, and converting between the two directly shows exactly where the "missing" capacity actually went: nowhere, it was never missing to begin with.</p>
<h2>A quick way to check the math yourself</h2>
<p>To convert a decimal figure to its binary equivalent at the same scale, divide by roughly 1.0737 (the accumulated gap by the gigabyte level) — a 1,000GB decimal figure divided by that factor lands close to 931GiB, matching what a binary-reporting file explorer would show for the same physical drive. Running a few real numbers through the conversion, rather than just reading about the discrepancy, is usually what finally makes the two systems click as genuinely different measurements rather than one of them being an error.</p>
HTML,
            ],
        ];
    }
}
