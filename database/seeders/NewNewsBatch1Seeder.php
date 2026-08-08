<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * First batch of post-launch news additions (Aug 2026 growth push): six
 * short news items covering real, sourced events from early August 2026 —
 * not yet covered by any existing post (checked against titles already in
 * the DB before writing these). Idempotent (updateOrCreate) so it's safe
 * to re-run on every deploy.
 */
class NewNewsBatch1Seeder extends Seeder
{
    public function run(): void
    {
        $editors = User::where('role', 'editor')->pluck('id');

        foreach ($this->posts() as $i => $post) {
            $category = Category::where('type', 'news')->where('slug', $post['department'])->first();

            $record = Post::updateOrCreate(
                ['type' => 'news', 'slug' => $post['slug']],
                [
                    'category_id' => $category?->id,
                    'author_id' => $editors->isNotEmpty() ? $editors[($i + 3) % $editors->count()] : null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'source_name' => $post['source_name'],
                    'source_url' => $post['source_url'],
                    'status' => 'published',
                    'published_at' => now()->subHours(($i + 1) * 5),
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
                'slug' => 'fbr-july-2026-collection-beats-target',
                'title' => 'FBR Beats Its July Collection Target by Rs30 Billion',
                'excerpt' => 'Pakistan\'s tax authority collected Rs810 billion in July against a Rs780 billion target, as AI-driven production monitoring expands into more sectors.',
                'meta_title' => 'FBR July 2026 Tax Collection Beats Target by Rs30 Billion',
                'meta_description' => 'FBR collected Rs810 billion in July 2026, Rs30 billion above target, as digital production monitoring — now live in four sectors — expands toward 16 more.',
                'department' => 'tax-policy',
                'source_name' => 'The Nation',
                'source_url' => 'https://www.nation.com.pk/01-Aug-2026/fbr-surpasses-july-tax-collection-target-rs30b',
                'tags' => ['FBR', 'Pakistan', 'Tax'],
                'body' => <<<'HTML'
<p class="lead">The Federal Board of Revenue collected Rs810 billion in tax during July, beating its Rs780 billion target by Rs30 billion — an early signal for a fiscal year in which FBR is aiming for Rs15.264 trillion overall.</p>
<h2>Where the extra revenue came from</h2>
<p>A large share is credited to expanding digital production monitoring, which tracks output in real time at the factory level rather than relying solely on self-reported figures. The system is now operational in four sectors and is being rolled out or designed across 16 more, together accounting for around 70% of Pakistan's manufacturing GDP.</p>
<h2>Sector-level gains</h2>
<p>Monitored production in the sugar sector rose 31% during the latest crushing season, a change expected to generate roughly Rs27 billion in additional revenue on its own. Separately, authorities report recovering Rs32 billion from the cement sector through the same monitoring approach.</p>
<h2>Why this matters for taxpayers</h2>
<p>A tax authority beating collection targets through better enforcement of existing rules — rather than new taxes — is generally the less disruptive way to close a revenue gap. If digital monitoring continues expanding as planned, it may reduce pressure for new taxes on salaried individuals in future budgets, though that outcome isn't guaranteed.</p>
HTML,
            ],
            [
                'slug' => 'uae-extends-small-business-tax-relief-2029',
                'title' => 'UAE Extends Small Business Corporate Tax Relief to 2029',
                'excerpt' => 'Businesses earning up to AED 3 million a year remain eligible for simplified corporate tax treatment under an extended relief scheme, the UAE Ministry of Finance confirmed.',
                'meta_title' => 'UAE Small Business Corporate Tax Relief Extended to 2029',
                'meta_description' => 'The UAE has extended its small business corporate tax relief programme to 31 December 2029, keeping simplified treatment available for businesses earning up to AED 3 million.',
                'department' => 'tax-policy',
                'source_name' => 'The National',
                'source_url' => 'https://www.thenationalnews.com/business/economy/2026/08/07/uae-extends-corporate-tax-relief-for-small-businesses-until-2029/',
                'tags' => ['UAE', 'Tax'],
                'body' => <<<'HTML'
<p class="lead">The UAE has extended its small business corporate tax relief programme until 31 December 2029, giving startups and small businesses several more years of simplified tax treatment under the country's corporate tax regime.</p>
<h2>Who qualifies</h2>
<p>Businesses with annual revenue up to AED 3 million (around $816,880) remain eligible to elect into the relief scheme, which lets qualifying small businesses avoid the full corporate tax compliance and calculation burden that larger companies face.</p>
<h2>Why the extension matters</h2>
<p>The UAE introduced federal corporate tax only in 2023, a significant shift for a jurisdiction long associated with a zero-tax reputation. The small business relief scheme was one of the main tools used to cushion that transition for entrepreneurs and freelancers formalising a business — extending it signals the government wants that on-ramp to stay available well into the rest of the decade rather than tightening quickly.</p>
<h2>The bigger picture</h2>
<p>The extension comes as the UAE separately works to align with OECD international tax standards through a Domestic Minimum Top-Up Tax aimed at large multinational groups — a reminder that the relief for small, mostly domestic businesses and the new rules targeting big multinational profit-shifting are two different tracks moving in parallel, not a general tightening across the board.</p>
HTML,
            ],
            [
                'slug' => 'bank-of-canada-holds-rate-2-25-percent-sixth-time',
                'title' => 'Bank of Canada Holds Rate at 2.25% for a Sixth Straight Decision',
                'excerpt' => 'The Bank of Canada left its overnight rate unchanged again in July, keeping borrowing costs steady as it watches inflation and a softening labour market.',
                'meta_title' => 'Bank of Canada Holds Interest Rate at 2.25% Again',
                'meta_description' => 'The Bank of Canada kept its overnight rate at 2.25% for a sixth consecutive decision in July 2026, with the next announcement due 2 September.',
                'department' => 'finance-markets',
                'source_name' => 'WOWA.ca',
                'source_url' => 'https://wowa.ca/bank-of-canada-interest-rate',
                'tags' => ['Canada', 'Interest Rates'],
                'body' => <<<'HTML'
<p class="lead">The Bank of Canada left its target for the overnight rate unchanged at 2.25% in its July 2026 decision — the sixth consecutive meeting without a move, extending a holding pattern that has now lasted several months.</p>
<h2>Why the Bank keeps holding</h2>
<p>Central banks typically pause once they judge policy is roughly balanced between controlling inflation and avoiding unnecessary damage to growth and employment. A run of six unchanged decisions suggests the Bank of Canada currently sees 2.25% as close to that balance point, watching incoming data rather than actively steering the rate in either direction for now.</p>
<h2>What it means for borrowers</h2>
<p>A held rate means variable-rate mortgage and loan payments tied to the Bank's overnight rate stay where they are for now — no fresh relief, but also no fresh squeeze. Anyone with a mortgage renewal coming up can plan around a broadly stable rate environment rather than a moving target, at least until the next scheduled announcement.</p>
<h2>What comes next</h2>
<p>The Bank's next scheduled rate announcement is 2 September 2026. Markets are currently pricing the policy rate to stay around 2.25% through into 2027, though that expectation can shift quickly if inflation or employment data surprises in either direction before then.</p>
HTML,
            ],
            [
                'slug' => 'uk-removes-vat-household-electricity-october-2026',
                'title' => 'UK to Remove VAT From Household Electricity Bills From October',
                'excerpt' => 'The UK government has confirmed household electricity bills will be freed of VAT from October 2026, part of a wider package of tax announcements this month.',
                'meta_title' => 'UK Removes VAT on Household Electricity From October 2026',
                'meta_description' => 'The UK will remove VAT from household electricity bills starting October 2026, a confirmed measure aimed at easing energy costs for consumers.',
                'department' => 'tax-policy',
                'source_name' => 'Caseron Cloud Accounting',
                'source_url' => 'https://caseron.co.uk/news-round-up-august-2026/',
                'tags' => ['UK', 'VAT', 'Cost of Living'],
                'body' => <<<'HTML'
<p class="lead">The UK government has confirmed that VAT will be removed from household electricity bills starting October 2026, one of the headline tax measures announced this month as part of a wider package aimed at easing living costs.</p>
<h2>What actually changes</h2>
<p>Domestic electricity bills currently carry VAT, and removing it directly lowers the final amount households pay per unit of electricity — unlike many "cost of living" announcements that work through subsidies or one-off payments, this is a straightforward reduction embedded in every bill from the effective date.</p>
<h2>Why electricity specifically</h2>
<p>Energy costs have been a persistent driver of household budget pressure, and unlike discretionary spending, electricity is close to a fixed necessity for most homes — cutting the tax on it reaches essentially every household rather than only those who qualify for a targeted support scheme.</p>
<h2>What to watch before October</h2>
<p>As with most tax changes tied to a future date, the exact mechanics — how quickly suppliers pass the saving through, and whether standing charges are treated the same way as usage charges — typically get confirmed in more detailed guidance closer to the effective date. Households budgeting for October onward can reasonably expect a lower bill, without yet having an exact per-unit saving to plan around.</p>
HTML,
            ],
            [
                'slug' => 'india-gst-collections-rise-15-percent-july-2026',
                'title' => 'India\'s GST Collections Rise 15.4% to Over Rs2.11 Lakh Crore',
                'excerpt' => 'Gross GST collections jumped 15.4% year-on-year in July 2026, with both domestic transactions and imports contributing to the increase.',
                'meta_title' => 'India GST Collections Rise 15.4% in July 2026',
                'meta_description' => 'India\'s gross GST collections rose 15.4% to over Rs2.11 lakh crore in July 2026, with domestic transactions up 10.1% and import-driven revenue up 29%.',
                'department' => 'finance-markets',
                'source_name' => 'SAG Infotech',
                'source_url' => 'https://blog.saginfotech.com/july-2026-gst-collection',
                'tags' => ['India', 'GST'],
                'body' => <<<'HTML'
<p class="lead">India's gross GST collections rose 15.4% year-on-year to more than Rs2.11 lakh crore in July 2026, a jump officials attribute to stronger domestic sales and rising import activity.</p>
<h2>Where the growth came from</h2>
<p>Revenue from domestic transactions climbed 10.1% to over Rs1.44 lakh crore, while collections from imports rose a sharper 29% to Rs66,511 crore. The gap between the two growth rates suggests import-linked activity — which can reflect both consumption demand and currency effects — is currently outpacing the underlying growth in domestic sales.</p>
<h2>Why GST collections are watched closely</h2>
<p>Because GST is collected on almost all consumption in real time, month-on-month collection figures function as one of the more up-to-date signals of economic activity available to policymakers, well ahead of slower-moving indicators like formal GDP releases. A double-digit rise is generally read as a sign of resilient consumer and business spending.</p>
<h2>Compliance dates to note</h2>
<p>Separately, GSTN has confirmed changes to e-Invoice and e-Way Bill system APIs effective from 1 August 2026, including a new requirement making the "Ship-to GSTIN" field mandatory in both systems whenever Ship-to information is present — a technical change businesses using automated filing software will need their providers to have accounted for.</p>
HTML,
            ],
            [
                'slug' => 'sec-approves-faster-listing-standards-crypto-etps',
                'title' => 'SEC Approves Faster Listing Standards for Crypto ETPs',
                'excerpt' => 'New generic listing standards let eligible crypto exchange-traded products launch without the full individual rule-change process, cutting potential approval time from 240 days to as little as 75.',
                'meta_title' => 'SEC Approves Generic Listing Standards for Crypto ETPs',
                'meta_description' => 'The SEC has approved generic listing standards for crypto exchange-traded products, shortening potential approval timelines from up to 240 days to as little as 75.',
                'department' => 'technology',
                'source_name' => 'The Block',
                'source_url' => 'https://www.theblock.co/post/383361/crypto-etfs-2026-regulatory-tailwinds-issuers-brace-crowded-year',
                'tags' => ['Crypto', 'Bitcoin', 'United States'],
                'body' => <<<'HTML'
<p class="lead">The US Securities and Exchange Commission has approved new generic listing standards for crypto exchange-traded products, letting eligible funds list without going through the full individual rule-change process each product previously required.</p>
<h2>What actually changed</h2>
<p>Previously, each new crypto ETP typically needed its own 19b-4 rule-change filing — a process that could take as long as 240 days to clear. Under the new generic standards, eligible products can instead qualify against a pre-set framework, cutting the potential approval timeline to as little as 75 days.</p>
<h2>Why this matters beyond one product</h2>
<p>A faster, standardised approval path effectively brings crypto ETPs closer to the process already used for commodity-based trust products like gold ETFs. That's a structural change, not a one-time approval — it lowers the barrier for issuers to bring new crypto-linked funds to market going forward, which is likely to mean more product choice and more competition among issuers over time.</p>
<h2>The wider regulatory context</h2>
<p>The move sits alongside a broader push from SEC leadership toward clearer crypto rules, including a proposed "token taxonomy" to define which digital assets count as securities. Faster listings do not equal fewer investor protections built into each individual product — the underlying disclosure and eligibility requirements still apply, just through a standardised process rather than a bespoke one each time.</p>
HTML,
            ],
        ];
    }
}
