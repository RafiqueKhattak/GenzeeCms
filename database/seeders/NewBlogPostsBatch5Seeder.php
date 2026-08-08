<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fifth (final) batch of post-launch blog additions: six evergreen posts
 * tied to the six NewToolsBatch5Seeder calculators. Idempotent
 * (updateOrCreate) so it's safe to re-run on every deploy.
 */
class NewBlogPostsBatch5Seeder extends Seeder
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
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 6) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'status' => 'published',
                    'published_at' => now()->subDays(30 - $i),
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
                'slug' => 'rule-of-72-quickest-way-estimate-doubling',
                'title' => 'The Rule of 72: The Quickest Way to Estimate When Your Money Doubles',
                'excerpt' => 'Divide 72 by your return rate and you have a surprisingly accurate estimate of how many years it takes money to double — no calculator app required.',
                'meta_title' => 'The Rule of 72 Explained: Estimate Doubling Time Fast',
                'meta_description' => 'The Rule of 72 estimates how long it takes money to double at a given return rate — divide 72 by the rate. Here is why it works and where it breaks down.',
                'tags' => ['Investing'],
                'body' => <<<'HTML'
<p class="lead">Of all the mental-math shortcuts in personal finance, the Rule of 72 might be the most genuinely useful — a single division that gives a surprisingly accurate answer to one of the most common investing questions: how long until this actually doubles?</p>
<h2>The shortcut itself</h2>
<p>Divide 72 by an annual return percentage, and the result is roughly the number of years needed for an investment to double in value. At 8% a year, money doubles in about 9 years. At 12%, about 6 years. At 6%, about 12 years — the same relationship, just running in the other direction.</p>
<h2>Why it actually works</h2>
<p>The true formula for doubling time under compound growth involves natural logarithms — not something anyone does in their head. But across the range of return rates most people actually encounter, roughly 6% to 15%, the logarithmic curve happens to be closely approximated by a simple 72-divided-by-rate calculation. The number 72 is also convenient because it divides evenly by many small numbers (2, 3, 4, 6, 8, 9, 12), making the mental math genuinely easy to do without a calculator.</p>
<h2>Where it starts to drift</h2>
<p>At very low rates (under 4%) or very high rates (above 20%), the Rule of 72 estimate diverges more noticeably from the exact answer — still in the right ballpark, but no longer precise enough for anything beyond a rough sense of scale. For genuinely low or high rates, running the exact compound growth math is worth the extra step.</p>
<h2>Using it to compare options quickly</h2>
<p>The real value of the Rule of 72 shows up in comparison, not isolation — quickly sanity-checking that a 10% return doubles money notably faster than a 5% return (7.2 years versus 14.4 years) makes the practical difference between return rates concrete in a way that abstract percentages alone don't. It's a tool for building fast intuition, meant to be followed up with precise numbers before any real decision.</p>
<h2>It works in reverse too</h2>
<p>The same shortcut applies to anything that grows or shrinks by a consistent percentage rate — inflation eroding purchasing power, debt compounding unpaid interest, or a business metric growing steadily. Divide 72 by an inflation rate to estimate how long until prices double; divide it by a credit card's interest rate to see how fast an unpaid balance could grow if left completely untouched. The math is identical regardless of what's actually growing.</p>
<h2>Variations worth knowing</h2>
<p>Some use the "Rule of 70" instead, which is marginally more accurate at lower growth rates but divides less evenly by common numbers, making the mental math slightly harder. Others use "Rule of 69.3" for the mathematically precise version at continuous compounding — overkill for a quick mental estimate, but worth knowing exists if a specific field of finance references it under a different name.</p>
<h2>A habit worth building</h2>
<p>Applying the Rule of 72 casually — to a savings account rate, an investment return, a country's reported inflation figure — turns abstract percentages encountered in everyday reading into a concrete sense of timescale almost automatically. It's one of the few pieces of financial mental math that pays for the small effort of memorizing it many times over.</p>
HTML,
            ],
            [
                'slug' => 'what-is-good-debt-to-income-ratio',
                'title' => 'What Is a Good Debt-to-Income Ratio? A Practical Guide',
                'excerpt' => 'Lenders use debt-to-income ratio to decide how much more credit you can handle. Here is what the number actually means and how to improve it.',
                'meta_title' => 'What Is a Good Debt-to-Income (DTI) Ratio?',
                'meta_description' => 'Debt-to-income ratio compares your monthly debt payments to your income. Here is what counts as a good DTI, and practical ways to improve yours.',
                'tags' => ['Debt', 'Loans'],
                'body' => <<<'HTML'
<p class="lead">Debt-to-income ratio is one of those numbers that quietly determines a lot — mortgage approval, credit card limits, even the interest rate offered on a loan — without most people ever calculating it themselves until a lender does it for them.</p>
<h2>What the number actually represents</h2>
<p>DTI is total monthly debt payments divided by monthly gross income, expressed as a percentage. It answers a specific question: of everything coming in each month, what share is already spoken for by debt obligations, before any other spending happens at all?</p>
<h2>What counts, and what doesn't</h2>
<p>Rent or mortgage, loan payments, and credit card minimums all count as debt. Everyday costs — groceries, utilities, insurance, subscriptions — don't count toward DTI, even though they're genuinely part of a monthly budget, because DTI is specifically measuring debt commitments, not total cost of living.</p>
<h2>The commonly cited thresholds</h2>
<p>36% or below is generally viewed as healthy, comfortably within what most lenders are willing to work with. The 36-43% range is workable but sits closer to the ceiling many lenders set for approving additional credit. Above 43%, qualifying for new credit becomes noticeably harder, since it signals limited room in the budget to absorb another payment without strain.</p>
<h2>Why DTI matters even without applying for anything</h2>
<p>Even setting aside loan applications, a high DTI is a useful personal signal: it means a large share of income is locked into fixed obligations before any of it reaches savings, investing, or discretionary spending — worth knowing regardless of whether a lender ever asks for the number.</p>
<h2>Practical ways to improve it</h2>
<p>Two levers move DTI: increasing income, or reducing debt payments. Of the two, reducing debt is usually more directly controllable in the short term — paying off a smaller loan entirely, consolidating high-payment debts into one lower payment, or aggressively paying down a credit card balance to shrink its minimum all lower DTI measurably. Even one paid-off obligation can move the ratio meaningfully if it was a large share of total debt payments.</p>
<h2>Checking DTI before a big financial decision</h2>
<p>Calculating DTI before applying for a mortgage or major loan — rather than finding out from a lender's rejection or a worse-than-expected rate — gives time to improve the number first if it's sitting above a comfortable threshold. It's one of the few lending metrics an individual can meaningfully influence with a few months of focused effort.</p>
<h2>Front-end vs. back-end DTI</h2>
<p>Some lenders distinguish between "front-end" DTI (housing costs alone against income) and "back-end" DTI (all debt obligations, including housing, against income) — the back-end figure is generally the more complete picture, and the one this calculator produces. If a lender quotes a DTI figure that seems unusually low compared to your own calculation, confirm whether they're citing the narrower front-end version.</p>
<h2>DTI is a snapshot, not a permanent label</h2>
<p>A high DTI today doesn't lock in a permanent borrowing ceiling — it reflects current debt and income, both of which change. Paying off even one obligation, or a documented raise, can shift the ratio meaningfully within a single reporting period, which is why it's worth recalculating close to any actual loan application rather than relying on an estimate from months earlier.</p>
HTML,
            ],
            [
                'slug' => 'how-to-calculate-negotiate-salary-raise',
                'title' => 'How to Actually Calculate (and Negotiate) Your Salary Raise',
                'excerpt' => 'A stated raise percentage does not always mean what it sounds like. Here is how to calculate it properly, and use the number in a negotiation.',
                'meta_title' => 'How to Calculate Your Salary Raise Percentage Correctly',
                'meta_description' => 'A raise percentage can be calculated against different bases, which changes what it actually means. Here is how to calculate it correctly and use it in negotiation.',
                'tags' => ['Salary'],
                'body' => <<<'HTML'
<p class="lead">"You're getting a 10% raise" sounds like a clear, simple statement — until the actual new number on the offer doesn't quite seem to match. The gap usually comes down to which base the percentage was calculated against, and it's worth checking rather than assuming.</p>
<h2>The basic calculation</h2>
<p>A raise percentage is the increase in salary divided by the original salary, multiplied by 100. Going from 100,000 to 110,000 is a straightforward 10% increase. The confusion creeps in when the "10%" being discussed was actually calculated against a different figure — total compensation including a bonus, for instance, rather than base salary alone.</p>
<h2>Why checking the actual numbers matters</h2>
<p>Calculating the increase directly from the old and new salary figures — rather than trusting a quoted percentage — removes any ambiguity about what base was used. This is a two-minute check worth doing on any offer letter or raise announcement, especially when total compensation includes variable components like bonuses or commission that can shift the percentage depending on how it's framed.</p>
<h2>Nominal raise vs. real raise</h2>
<p>A raise's real value depends heavily on inflation over the same period. A 10% raise in a year when prices rose 9% represents only about a 1% genuine improvement in purchasing power, even though the headline number looks solid. Comparing a raise percentage against the current inflation rate gives a far more honest read on whether a raise actually moves the needle financially.</p>
<h2>Using the number in a negotiation</h2>
<p>Walking into a raise conversation with a specific target percentage — informed by market rate research, inflation, and your own performance case — is a stronger position than a vague sense that "more would be nice." Framing a counter-offer as a specific percentage, backed by a clear reason (a promotion, expanded responsibilities, a comparison to market rate for the role), tends to land better than an unanchored request.</p>
<h2>What to check beyond the percentage</h2>
<p>A raise's real value also depends on when it takes effect (a raise backdated to the start of the year is worth more than the same percentage starting next quarter), whether it compounds into future bonus or benefit calculations, and how it compares not just to inflation but to what the same role commands elsewhere in the current market. The percentage is the headline number, but it's rarely the complete picture.</p>
<h2>Researching market rate before the conversation</h2>
<p>A raise conversation grounded in what comparable roles pay elsewhere carries more weight than one grounded purely in personal financial need — salary data from industry surveys, job postings for similar roles, or professional networks all help establish whether a current salary has genuinely fallen behind market rate, which is a stronger negotiating basis than "costs have gone up" alone.</p>
<h2>What to do if the number falls short</h2>
<p>If an offered raise comes in below both inflation and market rate, it's worth asking directly what would need to change — performance targets, added scope, a defined timeline — to reach a specific target next cycle, rather than accepting a vague "we'll revisit it." A concrete follow-up plan converts a disappointing raise into a documented path forward instead of an open-ended wait.</p>
HTML,
            ],
            [
                'slug' => 'zakat-al-fitr-vs-zakat-al-mal-difference',
                'title' => 'Zakat al-Fitr vs. Zakat al-Mal: What\'s the Difference?',
                'excerpt' => 'Both are called "zakat," but they work completely differently — one is a fixed amount per person around Eid, the other a percentage of wealth calculated annually.',
                'meta_title' => 'Zakat al-Fitr vs Zakat al-Mal: Key Differences Explained',
                'meta_description' => 'Zakat al-Fitr (Fitrana) and Zakat al-Mal are both called zakat but are calculated completely differently. Here is what distinguishes the two.',
                'tags' => ['Zakat'],
                'body' => <<<'HTML'
<p class="lead">Zakat al-Fitr and Zakat al-Mal share a name and a religious foundation, but they're calculated in entirely different ways, owed by different logic, and due at different times — conflating the two is a common source of confusion, especially for anyone calculating their obligations for the first time.</p>
<h2>Zakat al-Mal: the annual wealth-based obligation</h2>
<p>Zakat al-Mal is calculated as a percentage (traditionally 2.5%) of qualifying wealth held above a minimum threshold, known as nisab, for a full lunar year. It applies individually — each person calculates it based on their own qualifying assets: cash, gold, silver, business inventory, and certain other holdings, generally excluding a primary home and personal-use items.</p>
<h2>Zakat al-Fitr: the fixed per-person obligation around Eid</h2>
<p>Zakat al-Fitr, or Fitrana, is a small fixed amount per person, traditionally based on the value of a staple food, due before the Eid al-Fitr prayer at the end of Ramadan. It doesn't depend on wealth level or a minimum threshold the way Zakat al-Mal does — it's owed on behalf of every household member a person is financially responsible for, regardless of that dependent's own financial situation.</p>
<h2>Why the calculation methods are so different</h2>
<p>Zakat al-Mal is designed around redistributing a share of accumulated wealth annually — hence the percentage-of-assets structure. Zakat al-Fitr is designed to ensure every household, regardless of wealth level, can participate in providing for others specifically around Eid, which is why it's a modest fixed amount tied to a staple food's cost rather than scaled to individual wealth.</p>
<h2>Why the Fitrana rate changes every year</h2>
<p>Because it's pegged to the price of a staple food, the appropriate Fitrana amount moves with food prices each year — a rate calculated three years ago will understate the real obligation today. Local religious authorities typically announce a fresh figure each Ramadan specifically to account for this, which is why using last year's number is a common but avoidable mistake.</p>
<h2>Do you owe both?</h2>
<p>They're independent obligations — someone below the Zakat al-Mal wealth threshold may still owe Zakat al-Fitr for their household, since the latter doesn't depend on a wealth minimum. Someone who qualifies for both should calculate and give each separately, using the correct method for each rather than assuming one calculation covers both obligations.</p>
<h2>Common mistakes worth avoiding</h2>
<p>Two errors come up often: applying the 2.5% Zakat al-Mal percentage to a Zakat al-Fitr calculation (they use entirely different formulas), and reusing a Fitrana amount from a previous year without checking whether the currently announced rate has changed. Both are easy to avoid by treating the two obligations as genuinely separate calculations from the start, rather than variations on the same math.</p>
<h2>Giving on behalf of someone else</h2>
<p>Because Zakat al-Fitr is owed per household member rather than per individual wealth, a head of household typically calculates and gives it on behalf of dependents directly, without those dependents needing any qualifying wealth of their own. This is a meaningful structural difference from Zakat al-Mal, where each person's obligation is tied strictly to their own individual assets.</p>
HTML,
            ],
            [
                'slug' => 'rent-or-buy-how-to-run-the-numbers',
                'title' => 'Rent or Buy? How to Actually Run the Numbers',
                'excerpt' => 'The rent-vs-buy decision usually comes down to gut feeling. Run the actual math — mortgage costs, appreciation, rising rent — and the picture often looks different.',
                'meta_title' => 'Rent vs Buy: How to Actually Calculate Which Is Cheaper',
                'meta_description' => 'The rent vs buy decision depends on your time horizon, home appreciation, and rent growth — not just comparing a mortgage payment to rent. Here is how to run the real numbers.',
                'tags' => ['Loans', 'Savings'],
                'body' => <<<'HTML'
<p class="lead">"Rent is throwing money away" and "buying ties up all your cash" are both oversimplified takes on a decision that genuinely depends on the numbers — time horizon, local appreciation trends, and how mortgage costs compare to rent in a specific market matter far more than either slogan suggests.</p>
<h2>Why comparing payments alone is misleading</h2>
<p>A monthly mortgage payment and monthly rent aren't actually comparable on their own. Buying also requires a large upfront down payment and ongoing maintenance costs that renting avoids entirely — but buying builds equity in an appreciating asset, which renting never does. A complete comparison has to account for all of these pieces together, not just the recurring monthly number.</p>
<h2>The role of your time horizon</h2>
<p>Buying involves significant upfront costs that take time to offset through avoided rent increases and accumulated equity. A short expected stay — two or three years — often favors renting, since there isn't enough time for appreciation and equity-building to outweigh the upfront commitment. A longer horizon, ten years or more, generally shifts the math toward buying, assuming reasonable appreciation.</p>
<h2>Why rising rent changes the picture over time</h2>
<p>Rent isn't static — comparing today's rent to today's mortgage payment ignores that rent typically rises each year, sometimes substantially, while a fixed-rate mortgage payment stays constant for the life of the loan. Over a long horizon, rent that started lower than a mortgage payment can end up costing considerably more in total once realistic annual increases are factored in.</p>
<h2>The equity you actually have partway through</h2>
<p>If you compare renting to buying over a horizon shorter than the full mortgage term, remember the buyer doesn't own the home outright at that point — there's still a mortgage balance owed. The real financial position is the home's appreciated value minus that remaining balance, not the full home value, which is an easy detail to accidentally overstate in a rough mental comparison.</p>
<h2>What the math can't decide for you</h2>
<p>Even a careful cost comparison leaves out real, legitimate factors: the flexibility to relocate easily that renting offers, the stability and customization that owning provides, and simple personal preference. Running the numbers is meant to inform the decision with a clear financial picture — not to override every other consideration that reasonably belongs in a housing choice.</p>
<h2>Testing your assumptions matters more than the final answer</h2>
<p>The single output — "renting is cheaper" or "buying is cheaper" — is less useful than seeing how sensitive that answer is to the assumptions feeding it. Running the same comparison with a slightly lower appreciation estimate, a longer or shorter time horizon, or a different rent-increase assumption often reveals that the "right" answer is genuinely close in many realistic scenarios, which is itself useful information — it means other factors, not just the pure financial comparison, can reasonably tip the decision.</p>
<h2>Revisiting the decision isn't a sign of getting it wrong</h2>
<p>Circumstances change — a job offer in a new city, a shift in local appreciation trends, a change in household size — and a rent-vs-buy comparison that made sense two years ago may not hold today. Treating this as a periodic check-in rather than a one-time verdict keeps the decision aligned with current reality instead of a snapshot from whenever it was first calculated.</p>
HTML,
            ],
            [
                'slug' => 'fairest-way-split-group-bill-different-amounts',
                'title' => 'The Fairest Way to Split a Group Bill When Everyone Paid Different Amounts',
                'excerpt' => 'When one person covers dinner and another covers the cab, "just split it evenly" gets complicated fast. Here is the actual math behind fair settlement.',
                'meta_title' => 'How to Fairly Split a Group Bill With Uneven Payments',
                'meta_description' => 'When group members already paid different amounts for a shared trip or event, here is how to calculate a fair settlement with the fewest payments needed.',
                'tags' => ['Savings'],
                'body' => <<<'HTML'
<p class="lead">Splitting a single bill evenly is trivial — divide by the number of people. It gets genuinely more complicated the moment a group trip or event involves several payments made by several different people at different times, and everyone just wants to know, at the end, who actually owes whom.</p>
<h2>Why "just Venmo each other back" gets messy</h2>
<p>Without a structured approach, a group of five people who each covered something during a trip can end up trying to untangle a web of who paid for what, often resulting in far more individual payments back and forth than actually necessary — some people overpaying their true share, others underpaying, without a clear final picture.</p>
<h2>The actual fair-split calculation</h2>
<p>Add up everything spent by the whole group, divide by the number of people, and that's everyone's fair share. Compare what each person actually paid against that fair share: anyone who paid more than their share is owed money; anyone who paid less owes money. This turns a confusing pile of receipts into a clear, simple picture of who's in credit and who's in debit.</p>
<h2>Minimizing the number of actual payments</h2>
<p>Once everyone's balance is known, the group doesn't need every debtor to pay every creditor individually — matching the largest debtor against the largest creditor, settling what can be settled, then moving to the next pair, produces the fair outcome using far fewer total transactions than a naive pairwise settlement would require. For most group sizes, this reduces settlement to just a handful of payments rather than a dozen or more.</p>
<h2>Where this matters beyond one dinner</h2>
<p>The same logic applies to shared trips, group gifts, roommate expenses, or any situation where costs get covered unevenly by different people over time — anywhere money changes hands informally within a group benefits from the same clean, fair-share approach rather than an ad hoc attempt to remember who owes what.</p>
<h2>Keeping it simple in practice</h2>
<p>The math itself takes seconds once every payment is recorded — the real friction is usually just remembering to track who paid what as it happens, rather than trying to reconstruct it from memory or scattered receipts at the end. A quick note after each payment, however informal, makes the eventual settlement calculation immediate rather than a forensic exercise.</p>
<h2>When an equal split isn't actually fair</h2>
<p>An equal-share split assumes everyone consumed roughly the same amount, which doesn't always hold — one person's flight was more expensive, another skipped a meal entirely. In those cases, adjusting each person's recorded contribution to reflect what they should genuinely owe, before running the fair-share calculation, keeps the settlement accurate rather than technically "equal" but not actually fair to everyone involved.</p>
<h2>Settling up sooner rather than later</h2>
<p>The longer a group waits to settle after a shared trip or event, the harder it becomes to remember exactly who paid for what, and the more likely small amounts get written off as "not worth chasing" even when they'd add up to something real if left unresolved. Running the settlement calculation shortly after the last expense, while receipts and memory are both fresh, produces a more accurate result with far less friction than trying to reconstruct it weeks later.</p>
HTML,
            ],
        ];
    }
}
