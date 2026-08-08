<?php

namespace App\Console\Commands;

use App\Http\Controllers\Site\PostController as PublicPostController;
use App\Http\Controllers\Site\SeoController;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * One-time backfill: the 41 legacy news items imported from the old static
 * site (ids 41-81, June-July 2026) predate source_name/source_url existing
 * as a feature, so they were imported with both fields empty. Every mapping
 * below was verified by hand against a real, currently-live article
 * covering the same event, matched against this post's exact title/excerpt/
 * date — not a generic placeholder.
 *
 * "welcome-to-genzeelogics-news" is deliberately excluded — it's the site's
 * own launch announcement, not externally-reported news, so there is no
 * real external source to cite.
 *
 * Idempotent: only fills posts where source_name is currently null, so
 * re-running never overwrites a source someone has since edited by hand.
 */
class BackfillLegacyNewsSources extends Command
{
    protected $signature = 'content:backfill-news-sources {--dry-run : List what would be set without saving}';

    protected $description = 'Backfill source_name/source_url on legacy-imported news posts that have none';

    /** slug => [source_name, source_url] */
    protected const SOURCES = [
        'pakistan-salaried-tax-2026-27-what-changed' => [
            'The Express Tribune',
            'https://tribune.com.pk/story/2612818/budget-2026-27-govt-cuts-taxes-ends-surcharge-for-four-salaried-class-income-slabs',
        ],
        'gen-z-ai-job-market-goldman-sachs-2026' => [
            'Fortune',
            'https://fortune.com/2026/06/01/how-many-jobs-is-ai-destroying-goldman-sachs-11000-per-month-gen-z-economy/',
        ],
        'uae-wage-protection-system-new-deadline-2026' => [
            'Khaleej Times',
            'https://www.khaleejtimes.com/uae/uae-wage-protection-system-salary-payment-first-day-month-2026',
        ],
        'rbi-holds-repo-rate-5-25-percent-june-2026' => [
            'Drishti IAS',
            'https://www.drishtiias.com/daily-updates/daily-news-analysis/rbi-holds-repo-rate-and-lowers-gdp-growth-forecast',
        ],
        'us-2026-tax-brackets-no-tax-on-tips-obbba' => [
            'Tax Foundation',
            'https://taxfoundation.org/data/all/federal/2026-tax-brackets/',
        ],
        'cfpb-bnpl-study-not-harmful-june-2026' => [
            'Payments Dive',
            'https://www.paymentsdive.com/news/consumer-financial-protection-bureau-bnpl-buy-now-pay-later-research/750651/',
        ],
        'canada-federal-tax-rate-14-percent-2026' => [
            'Global News',
            'https://globalnews.ca/news/11555566/income-tax-brackets-changing-2026/',
        ],
        'pakistan-govt-employees-pay-raise-budget-2026-27' => [
            'Profit by Pakistan Today',
            'https://profit.pakistantoday.com.pk/2026/06/12/federal-govt-proposes-7percent-pay-and-pension-increase-10percent-minimum-wage-hike',
        ],
        'fbr-fixed-tax-scheme-retailers-2026' => [
            'Business Recorder',
            'https://www.brecorder.com/news/40425730/new-fixed-tax-scheme-from-july-1-100000-small-shopkeepers-retailers-to-avail-benefits',
        ],
        'jazzcash-treasury-bills-app-launch' => [
            'Profit by Pakistan Today',
            'https://profit.pakistantoday.com.pk/2026/06/17/jazzcash-launches-retail-treasury-bills-access-through-app-in-push-to-widen-investment-base',
        ],
        'pakistan-freelancer-it-export-tax-0-25-extended' => [
            'Business Recorder',
            'https://www.brecorder.com/news/40423482',
        ],
        'uae-golden-visa-content-creators-freelancers' => [
            'Emirates 24|7',
            'https://www.emirates247.com/uae/uae-golden-visa-for-content-creators-who-can-apply-eligibility-rules-and-how-to-get-it/2149',
        ],
        'gen-z-unemployment-fed-st-louis-study-2026' => [
            'St. Louis Fed',
            'https://www.stlouisfed.org/on-the-economy/2026/jun/how-shifts-labor-supply-demand-shape-outcomes-young-workers',
        ],
        'occ-genius-act-stablecoin-aml-rule-2026' => [
            'Office of the Comptroller of the Currency',
            'https://www.occ.gov/news-issuances/bulletins/2026/bulletin-2026-28.html',
        ],
        'sec-novel-crypto-etf-comment-period-2026' => [
            'U.S. Securities and Exchange Commission',
            'https://www.sec.gov/files/rules/other/2026/33-11426.pdf',
        ],
        'us-pslf-rules-vacated-courts-2026' => [
            'NASFAA',
            'https://www.nasfaa.org/news-item/39289/Federal_Court_Vacates_PSLF_Final_Rule_on_Employer_Eligibility_Hours_Before_July_1_Effective_Date',
        ],
        'bitcoin-etf-outflows-worst-month-june-2026' => [
            'BeInCrypto',
            'https://beincrypto.com/bitcoin-etf-outflows-june-2026-record/',
        ],
        'india-gst-itc-locking-gstr-3b-july-2026' => [
            'Taxscan',
            'https://www.taxscan.in/top-stories/big-gst-change-from-july-2026-gstr-3b-itc-locking-explained-1448389',
        ],
        'uae-e-invoicing-pilot-july-2026' => [
            'Gulf News',
            'https://gulfnews.com/business/tax-news/uae-to-launch-pilot-phase-of-electronic-invoicing-system-in-july-2026-1.500424633',
        ],
        'us-student-loan-rap-repayment-plan-launches' => [
            'NerdWallet',
            'https://www.nerdwallet.com/student-loans/learn/what-is-the-new-repayment-assistance-plan-rap-for-student-loans',
        ],
        'zatca-penalty-exemption-extended-2026' => [
            'ZATCA',
            'https://zatca.gov.sa/en/MediaCenter/News/Pages/Cancellation-of-fines-Dec-2026.aspx',
        ],
        'canada-gst-credit-top-up-july-2026' => [
            'Department of Finance Canada',
            'https://www.canada.ca/en/department-finance/news/2026/06/canadians-to-begin-receiving-enhanced-canada-groceries-and-essentials-benefit-starting-today0.html',
        ],
        'pakistan-crypto-regulator-licensing-consultation' => [
            'PVARA',
            'https://www.pvara.gov.pk/licensing',
        ],
        'uae-education-vat-guide-fta' => [
            'Deloitte',
            'https://www.deloitte.com/middle-east/en/services/tax/perspectives/fta-issues-first-vat-guide-for-the-uae-education-sector.html',
        ],
        'us-student-loan-save-plan-forbearance-ending-2026' => [
            'The College Investor',
            'https://thecollegeinvestor.com/83587/what-changes-for-student-loan-borrowers-on-july-1-2026/',
        ],
        'india-gst-eway-bill-ship-to-gstin-postponed' => [
            'Fibre2Fashion',
            'https://www.fibre2fashion.com/news/textile-news/gstn-defers-e-way-bill-changes-puts-august-1-rollout-on-hold-312256-newsdetails.htm',
        ],
        'psx-kse-100-record-high-july-2026' => [
            'Dunya News',
            'https://dunyanews.tv/en/Business/961113-pakistan-stocks-surge-as-kse100-closes-above-187000-points-for-the-f',
        ],
        'npci-agentic-ai-upi-payments-protocol' => [
            'Business Standard',
            'https://www.business-standard.com/finance/news/india-may-allow-agentic-ai-led-upi-transactions-under-new-npci-protocol-126070801343_1.html',
        ],
        'pakistan-remittances-record-41-6-billion-fy26' => [
            'DAWN.COM',
            'https://www.dawn.com/news/2014137/workers-remittances-hit-record-416bn-in-fy26',
        ],
        'uk-mansion-tax-high-value-council-tax-surcharge' => [
            'GOV.UK',
            'https://www.gov.uk/government/consultations/high-value-council-tax-surcharge/high-value-council-tax-surcharge',
        ],
        'paypal-stripe-advent-acquisition-offer-2026' => [
            'Bloomberg',
            'https://www.bloomberg.com/news/articles/2026-07-15/stripe-advent-offer-to-buy-paypal-for-53-billion-reuters-says',
        ],
        'uk-making-tax-digital-income-tax-live-2026' => [
            'BDO',
            'https://www.bdo.co.uk/en-gb/news/2026/landlords-and-self-employed-urged-to-get-ready-for-making-tax-digital',
        ],
        'sec-bitwise-crypto-etf-approval-stay-july-2026' => [
            'etf.com',
            'https://www.etf.com/sections/news/sec-stays-bitwise-crypto-etf-launch-despite-approval',
        ],
        'saudi-vat-return-deadline-july-31-2026' => [
            'Saudi Press Agency',
            'https://www.spa.gov.sa/en/N2638558',
        ],
        'india-itr-2026-deadline-new-tax-act-compliance' => [
            'ClearTax',
            'https://cleartax.in/last-date-to-file-itr',
        ],
        'fbr-opens-tax-year-2026-return-filing' => [
            'ProPakistani',
            'https://propakistani.pk/2026/07/24/fbr-announces-date-for-filing-2026-tax-returns',
        ],
        'sbp-holds-policy-rate-11-5-percent' => [
            'Pakistan Today',
            'https://www.pakistantoday.com.pk/2026/07/27/sbp-leaves-key-rate-unchanged-at-115percent-amid-inflation-and-external-risks',
        ],
        'uk-stamp-duty-burnham-rules-out-changes' => [
            'ITV News',
            'https://www.itv.com/news/2026-07-27/burnham-says-big-decisions-ahead-on-council-tax',
        ],
        'coinbase-robinhood-q2-earnings-preview-2026' => [
            'Fortune',
            'https://fortune.com/2026/07/28/coinbase-and-robinhood-are-locked-in-a-heated-battle-ahead-of-q2-earningsbut-only-one-is-being-called-the-industrys-first-hyperscaler/',
        ],
        'us-fed-holds-rates-july-2026' => [
            'CBS News',
            'https://www.cbsnews.com/news/fed-interest-rate-decision-july-meeting/',
        ],
    ];

    public function handle(): int
    {
        $posts = Post::withTrashed()->where('type', 'news')->whereNull('source_name')->get(['id', 'slug', 'title']);
        $updated = 0;
        $skipped = [];

        foreach ($posts as $post) {
            if (! isset(self::SOURCES[$post->slug])) {
                $skipped[] = "{$post->id}: {$post->title} ({$post->slug})";
                continue;
            }

            [$sourceName, $sourceUrl] = self::SOURCES[$post->slug];
            $this->info("{$post->id}: {$post->title} -> {$sourceName}");

            if ($this->option('dry-run')) {
                continue;
            }

            $post->update(['source_name' => $sourceName, 'source_url' => $sourceUrl]);
            $updated++;
        }

        if (! empty($skipped)) {
            $this->warn('No mapping (left as-is): '.PHP_EOL.implode(PHP_EOL, $skipped));
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run — re-run without --dry-run to apply.');

            return self::SUCCESS;
        }

        if ($updated > 0) {
            Cache::forget(SeoController::CACHE_KEY);
            Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'news');
        }

        $this->info("Updated {$updated} post(s).");

        return self::SUCCESS;
    }
}
