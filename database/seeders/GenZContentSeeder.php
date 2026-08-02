<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tool;
use App\Models\ToolFaq;
use Illuminate\Database\Seeder;

/**
 * Seeds the Gen Z topic cluster: the generation calculator tool plus four
 * supporting blog posts. Idempotent (updateOrCreate throughout) so it can be
 * re-run safely on any environment.
 *
 * Cohort year ranges used throughout are Pew Research Center's (Gen Z =
 * 1997-2012, set by Pew in 2019) except Gen Alpha/Beta, which Pew has not
 * defined — those follow McCrindle Research, who coined the terms. Keep the
 * copy here consistent with GenerationCalculator.vue if either is edited.
 */
class GenZContentSeeder extends Seeder
{
    public function run(): void
    {
        $toolCategory = Category::firstOrCreate(
            ['type' => 'tool', 'slug' => 'everyday-study'],
            ['name' => 'Everyday & Study', 'order' => 5]
        );

        $blogCategory = Category::updateOrCreate(
            ['type' => 'blog', 'slug' => 'generations-culture'],
            [
                'name' => 'Generations & Culture',
                'tagline' => 'Who counts as Gen Z, what the research actually says, and how the cohorts differ.',
                'order' => 1,
            ]
        );

        $this->seedTool($toolCategory);
        $this->seedPosts($blogCategory);
    }

    protected function seedTool(Category $category): void
    {
        $tool = Tool::updateOrCreate(
            ['slug' => 'generation-calculator'],
            [
                'category_id' => $category->id,
                'title' => 'Generation Calculator',
                'icon' => 'GEN',
                'component' => 'GenerationCalculator',
                'short_description' => 'Enter your date of birth to find out whether you are Gen Z, Gen Alpha, a Millennial, Gen X or a Baby Boomer — with the exact birth-year range for each cohort.',
                'guide_content' => $this->toolGuide(),
                'keywords' => [
                    'generation calculator', 'what generation am i', 'gen z birth years',
                    'am i gen z', 'gen z or millennial', 'generation z calculator',
                    'gen alpha birth years', 'boomer generation years',
                ],
                'meta_title' => 'Generation Calculator: Am I Gen Z, Millennial, Gen X or Boomer?',
                'meta_description' => 'Enter your date of birth and find out your generation instantly — Gen Z, Gen Alpha, Millennial, Gen X, Boomer or Silent Generation, with Pew Research birth-year ranges.',
                'status' => 'published',
                'order' => 46,
                'published_at' => now(),
            ]
        );

        $faqs = [
            [
                'What years are Gen Z?',
                'Pew Research Center defines Generation Z as people born between 1997 and 2012. That makes the oldest Gen Z adults 29 in 2026, and the youngest 14. Pew set this boundary in 2019, and it is the definition most widely used in news reporting and academic research.',
            ],
            [
                'Am I Gen Z or a Millennial?',
                'The dividing line is 1996/1997. If you were born in 1996 or earlier you are a Millennial; born in 1997 or later, you are Gen Z. Pew drew the line here because people born from 1997 onward were too young to have any meaningful memory of the September 11 attacks, and grew up with broadband and smartphones as a given.',
            ],
            [
                'What generation comes after Gen Z?',
                'Generation Alpha, generally defined as those born from 2013 to 2024. The term was coined by McCrindle Research, not Pew — Pew has not yet formally named the post-Gen-Z cohort. Generation Beta, starting in 2025, is the next named cohort under the same naming scheme.',
            ],
            [
                'Why do different websites give different Gen Z years?',
                'Because there is no official authority that defines generations. Pew Research Center (1997-2012) is the most cited, but other researchers draw the lines a year or two apart — some start Gen Z in 1995 or 1996. If you were born in the first or last year of a range, you are on the cusp and could reasonably be placed either side.',
            ],
            [
                'Is this generation calculator accurate?',
                'It applies the Pew Research Center cohort boundaries exactly, and flags when your birth year sits on a cusp between two cohorts. Since generational boundaries are a research convention rather than an official standard, the tool also shows which organisation defined each range so you can judge for yourself.',
            ],
        ];

        $tool->faqs()->delete();
        foreach ($faqs as $i => [$question, $answer]) {
            ToolFaq::create([
                'tool_id' => $tool->id,
                'question' => $question,
                'answer' => $answer,
                'order' => $i,
            ]);
        }

        // Link it to the age calculator both ways — they answer adjacent questions.
        $ageCalculator = Tool::where('slug', 'age-calculator')->first();
        if ($ageCalculator) {
            $tool->related()->syncWithoutDetaching([$ageCalculator->id => ['order' => 0]]);
            $ageCalculator->related()->syncWithoutDetaching([$tool->id => ['order' => 99]]);
        }
    }

    protected function toolGuide(): string
    {
        return <<<'HTML'
<h2>How this generation calculator works</h2>
<p>Enter your date of birth and the calculator matches your birth year against the standard generational cohorts. It shows which generation you belong to, the full birth-year range for that cohort, how far into the cohort you sit, and which research organisation defined the boundary.</p>

<h2>The generations and their birth years</h2>
<p>These are the ranges the calculator uses:</p>
<ul>
<li><strong>The Silent Generation</strong> — born 1928 to 1945</li>
<li><strong>Baby Boomers</strong> — born 1946 to 1964</li>
<li><strong>Generation X</strong> — born 1965 to 1980</li>
<li><strong>Millennials (Generation Y)</strong> — born 1981 to 1996</li>
<li><strong>Generation Z</strong> — born 1997 to 2012</li>
<li><strong>Generation Alpha</strong> — born 2013 to 2024</li>
<li><strong>Generation Beta</strong> — born 2025 onward</li>
</ul>
<p>The first five ranges come from the Pew Research Center, which is the most widely cited source for generational boundaries in both journalism and academic work. Pew formally set the Gen Z start year at 1997 in 2019. Generation Alpha and Generation Beta are not Pew definitions — Pew has not yet named the cohorts after Gen Z. Those ranges come from McCrindle Research, the Australian firm that coined both names.</p>

<h2>Why the boundaries are where they are</h2>
<p>Generational cut-offs are not arbitrary, but they are also not official. Researchers draw them around formative shared experiences. Pew's reasoning for starting Gen Z at 1997 was that anyone born from that year onward was at most four years old during the September 11 attacks — too young to have any real memory of a world before them — and grew up with broadband internet, smartphones and social media as ordinary background facts rather than new arrivals.</p>
<p>The same logic sits behind the Millennial start year of 1981: that cohort was old enough to remember the pre-internet world and had to consciously adapt to it, which is a genuinely different experience from never having known anything else.</p>

<h2>Being "on the cusp"</h2>
<p>If your birth year is the first or last year of a cohort, the calculator will flag that you are on the cusp. This matters more than it might sound. Because no official body defines generations, different researchers place the lines a year or two apart — some start Gen Z at 1995, others at 1996 or 1997. Someone born in 1996 will be called a Millennial by Pew and Gen Z by other sources, and both are defensible.</p>
<p>People born on a cusp often report not identifying strongly with either cohort's stereotype, which is exactly what you would expect when the boundary is a research convention rather than a fact about the person.</p>

<h2>What generational labels are actually useful for</h2>
<p>Cohort analysis is a legitimate research method: it lets you separate the effect of age from the effect of the era someone grew up in. If 25-year-olds today behave differently from 25-year-olds in 1995, that difference is about the era, not about being 25.</p>
<p>Where the labels stop being useful is in predicting anything about an individual. A cohort spanning sixteen birth years contains enormous variation in income, country, education and circumstance. The generational label tells you roughly when someone was born and what large-scale events they lived through at what age — not what they are like.</p>
HTML;
    }

    protected function seedPosts(Category $category): void
    {
        foreach ($this->posts() as $post) {
            Post::updateOrCreate(
                ['type' => 'blog', 'slug' => $post['slug']],
                [
                    'category_id' => $category->id,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }
    }

    protected function posts(): array
    {
        return [
            [
                'slug' => 'what-does-gen-z-mean',
                'title' => 'What Does Gen Z Mean? The Definition, Explained Simply',
                'meta_title' => 'What Does Gen Z Mean? Definition, Birth Years & Origin',
                'meta_description' => 'Gen Z means the generation born between 1997 and 2012. Here is where the name came from, who decides the boundaries, and what actually defines the cohort.',
                'excerpt' => 'Gen Z means the generation born roughly between 1997 and 2012 — but who decided that, where did the name come from, and what actually makes a generation?',
                'body' => $this->postWhatDoesGenZMean(),
            ],
            [
                'slug' => 'gen-z-birth-years-what-dates-count',
                'title' => 'Gen Z Birth Years: What Dates Actually Count as Generation Z?',
                'meta_title' => 'Gen Z Birth Years: Exact Date Range for Generation Z (2026)',
                'meta_description' => 'Generation Z covers birth years 1997 to 2012 under the Pew Research definition. Here is why sources disagree, and what it means if you were born on the cusp.',
                'excerpt' => 'The short answer is 1997 to 2012 — but plenty of sources say 1995, or 1996, or 2010. Here is why the disagreement exists and which years are safest to use.',
                'body' => $this->postGenZBirthYears(),
            ],
            [
                'slug' => 'gen-z-vs-gen-alpha-vs-millennials-vs-boomers',
                'title' => 'Gen Z vs Gen Alpha vs Millennials vs Boomers: The Real Differences',
                'meta_title' => 'Gen Z vs Gen Alpha vs Millennials vs Boomers Compared',
                'meta_description' => 'Side-by-side comparison of Gen Alpha, Gen Z, Millennials, Gen X and Boomers — birth years, formative technology and the events that shaped each cohort.',
                'excerpt' => 'Birth years, formative technology, and the events each cohort actually lived through — a side-by-side comparison without the stereotypes.',
                'body' => $this->postComparison(),
            ],
            [
                'slug' => 'gen-z-strengths-and-weaknesses-research',
                'title' => 'Gen Z Strengths and Weaknesses: What the Research Actually Shows',
                'meta_title' => 'Gen Z Strengths & Weaknesses: What Research Actually Shows',
                'meta_description' => 'Beyond the stereotypes — what surveys and studies from Pew, Deloitte and the Fed actually find about Gen Z at work, with money, and online.',
                'excerpt' => 'Beyond the "lazy" and "digital native" clichés — what large surveys and studies actually find about this cohort, including where the popular narrative is wrong.',
                'body' => $this->postStrengthsWeaknesses(),
            ],
        ];
    }

    protected function postWhatDoesGenZMean(): string
    {
        return <<<'HTML'
<p><strong>Gen Z means the generation of people born between 1997 and 2012.</strong> It is short for "Generation Z", and it is the cohort that follows the Millennials and precedes Generation Alpha. In 2026, that makes Gen Z anyone roughly between 14 and 29 years old.</p>
<p>That is the one-line answer. The more interesting question is where the label came from, who has the authority to define it, and whether it describes anything real.</p>

<h2>Where the name came from</h2>
<p>The name is almost accidental. Generation X got its name from a 1991 Douglas Coupland novel, <em>Generation X: Tales for an Accelerated Culture</em>, which itself borrowed the phrase from an earlier book. The X was meant to suggest an undefined, hard-to-label cohort.</p>
<p>Once "Generation X" stuck, the next two cohorts were named by simply continuing the alphabet: Generation Y — later renamed Millennials, because they came of age around the year 2000 — and then Generation Z. There was no committee and no deliberate meaning behind the Z. It is just the letter that came next.</p>
<p>This is also why the naming scheme broke after Z. Having run out of alphabet, McCrindle Research proposed restarting with the Greek alphabet: Generation Alpha for those born from 2013, and Generation Beta from 2025.</p>

<h2>Who decides the boundaries?</h2>
<p>Nobody, officially. There is no government body or international standard that defines generations. In practice, the <strong>Pew Research Center</strong> definition is the one most widely used in journalism and academic work. Pew formally set the Gen Z range in 2019: born 1997 to 2012.</p>
<p>Pew's stated reasoning for the 1997 start was that people born from that year onward were at most four years old when the September 11 attacks happened — too young to hold any real memory of the world before them — and grew up with broadband internet, smartphones and social media as ordinary facts of life rather than new technology that arrived and had to be learned.</p>
<p>Other researchers draw the line differently. Some start Gen Z in 1995, others in 1996. None of these are wrong, exactly; they are just different judgements about which shared experience matters most.</p>

<h2>What actually defines a generation</h2>
<p>The useful idea behind generational analysis is that people who were the same age during the same major events tend to share certain reference points. Someone who was 22 and job-hunting during the 2008 financial crisis had a measurably different early career than someone who was 22 in 2015. That is a real effect, and cohort analysis is how researchers separate it from the effect of simply being young.</p>
<p>For Gen Z specifically, the commonly cited formative experiences are: growing up with smartphones and social media from childhood rather than adolescence; the COVID-19 pandemic disrupting school or early career for essentially the entire cohort at once; and entering a labour market being actively reshaped by automation and AI.</p>

<h2>Where the label stops being useful</h2>
<p>A cohort spanning sixteen birth years, across every country on earth, contains enormous variation. A 28-year-old in Karachi and a 15-year-old in Toronto are both Gen Z, and that fact tells you almost nothing about what either of them is like.</p>
<p>Generational labels describe large-scale statistical patterns. They are genuinely useful for that. They are not useful for predicting anything about an individual person, and most of the popular claims about generational personality traits — that one cohort is lazier, or more entitled, or more anxious — do not survive contact with the actual survey data. We look at what the research does support in <a href="/blog/gen-z-strengths-and-weaknesses-research/">Gen Z strengths and weaknesses</a>.</p>

<h2>Working out your own generation</h2>
<p>If you want to check where your birth date falls — including whether you sit on the cusp between two cohorts, where different sources would classify you differently — our <a href="/tools/generation-calculator/">generation calculator</a> does it instantly and shows the source behind each boundary.</p>
HTML;
    }

    protected function postGenZBirthYears(): string
    {
        return <<<'HTML'
<p><strong>Generation Z covers birth years 1997 to 2012.</strong> That is the Pew Research Center definition, and it is the one most commonly used. If you were born in that window, you are Gen Z.</p>
<p>But you have probably also seen 1995, or 1996, or 2010 given as boundaries. Both of those are also in circulation, and neither is a mistake. Here is why the disagreement exists.</p>

<h2>The short answer, by year</h2>
<ul>
<li><strong>1996 or earlier</strong> — Millennial under the Pew definition</li>
<li><strong>1997 to 2012</strong> — Generation Z</li>
<li><strong>2013 or later</strong> — Generation Alpha</li>
<li><strong>2025 or later</strong> — Generation Beta</li>
</ul>
<p>In 2026, that puts the oldest Gen Z at 29 and the youngest at 14.</p>

<h2>Why sources disagree</h2>
<p>There is no official body that defines generational boundaries. No government sets them, no international standard exists. Each research organisation makes its own judgement about which shared experiences matter most, and then draws the line accordingly.</p>
<p>Pew Research Center chose 1997 as the Gen Z start year because of the September 11 attacks: anyone born from 1997 was four or younger in 2001, too young to remember the world before. That is a defensible cut-off, and Pew's prominence has made it the default.</p>
<p>Researchers who start Gen Z at 1995 tend to weight technology instead — arguing that the meaningful break is between people who had smartphones during their teenage years and people who did not. Someone born in 1995 got their first smartphone around age 12 to 15, which is arguably close enough to the Gen Z experience.</p>
<p>Neither reasoning is wrong. They are weighting different things.</p>

<h2>The Gen Alpha boundary is even softer</h2>
<p>The 2012/2013 line between Gen Z and Gen Alpha is less settled than the Millennial boundary, for a simple reason: Pew has not defined it. Pew has published the Gen Z range ending in 2012, but has been explicit that it has not yet named or bounded the cohort that follows.</p>
<p>The Generation Alpha name and its 2013 to 2024 range come from McCrindle Research, an Australian social research firm, which also proposed Generation Beta starting in 2025. These are widely adopted, but they carry less institutional weight than the Pew boundaries, and they may yet be revised.</p>

<h2>If you were born on a cusp</h2>
<p>Cusp years are 1996/1997 and 2012/2013. If your birth year is one of those, your classification genuinely depends on which source you consult.</p>
<p>People born right on a boundary often report not identifying with either cohort's stereotype — sometimes called the "Zillennial" experience for the 1993 to 1998 window. That is not a coincidence or a personality quirk. It is what you would expect when a boundary is a research convention drawn through a continuous population rather than a real division between two groups of people.</p>
<p>Practically: if you need to state a generation for a form or an article, use the Pew boundary. It is the most widely recognised and the least likely to be challenged.</p>

<h2>Check your own birth date</h2>
<p>Our <a href="/tools/generation-calculator/">generation calculator</a> takes a date of birth and returns the cohort, the full year range, which research organisation defined that boundary, and a warning if you fall on a cusp. Everything runs in your browser — the date you enter is never sent anywhere.</p>
<p>For a broader look at how the cohorts differ, see <a href="/blog/gen-z-vs-gen-alpha-vs-millennials-vs-boomers/">Gen Z vs Gen Alpha vs Millennials vs Boomers</a>.</p>
HTML;
    }

    protected function postComparison(): string
    {
        return <<<'HTML'
<p>Five named generations are alive and economically active right now. This is what actually separates them — birth years, the technology they grew up with, and the events they lived through at a formative age.</p>

<h2>The cohorts at a glance</h2>
<table>
<thead>
<tr><th>Generation</th><th>Birth years</th><th>Age in 2026</th><th>Defined by</th></tr>
</thead>
<tbody>
<tr><td>Generation Beta</td><td>2025 onward</td><td>0–1</td><td>McCrindle</td></tr>
<tr><td>Generation Alpha</td><td>2013–2024</td><td>2–13</td><td>McCrindle</td></tr>
<tr><td>Generation Z</td><td>1997–2012</td><td>14–29</td><td>Pew Research</td></tr>
<tr><td>Millennials (Gen Y)</td><td>1981–1996</td><td>30–45</td><td>Pew Research</td></tr>
<tr><td>Generation X</td><td>1965–1980</td><td>46–61</td><td>Pew Research</td></tr>
<tr><td>Baby Boomers</td><td>1946–1964</td><td>62–80</td><td>Pew Research</td></tr>
</tbody>
</table>

<h2>Baby Boomers (1946–1964)</h2>
<p>Named for the post-war birth rate surge. Grew up with television as the new mass medium and encountered computers, if at all, in adulthood and usually at work. This cohort is now at or past retirement age across most of the world, which makes it central to pension and healthcare policy debates almost everywhere.</p>

<h2>Generation X (1965–1980)</h2>
<p>The smallest of the living cohorts, and the bridge generation. Grew up analogue — landlines, physical media, no internet — and adapted to digital as working adults. Often described as the "latchkey" generation because of rising dual-income households during their childhood. Currently in peak earning years and holding a large share of senior management positions.</p>

<h2>Millennials (1981–1996)</h2>
<p>Came of age exactly as the internet went mainstream. Old enough to remember dial-up and the pre-smartphone world; young enough to have adopted each new platform as it arrived. The defining economic event for this cohort was the 2008 financial crisis, which hit many of them during their first years in the labour market — a period of unemployment or underemployment early in a career has measurable lifetime earnings effects, which is why Millennial wealth accumulation lags earlier cohorts at the same ages.</p>

<h2>Generation Z (1997–2012)</h2>
<p>The first cohort for whom smartphones and social media were present from childhood rather than arriving during adolescence or adulthood. Two shared experiences stand out. First, COVID-19 disrupted school or early career for essentially the entire cohort simultaneously — an unusually uniform shock. Second, they are entering a labour market being actively restructured by automation and AI, with entry-level roles in several sectors visibly contracting.</p>
<p>Gen Z is also the most formally educated cohort in history by enrolment rates, and in most surveyed countries the most ethnically diverse.</p>

<h2>Generation Alpha (2013–2024)</h2>
<p>The children of Millennials. The first cohort born entirely into the touchscreen era — most have never encountered a screen that was not interactive. Too young for meaningful behavioural research yet; nearly everything written about Gen Alpha at this stage is projection rather than finding. Worth treating claims about them with more scepticism than usual.</p>

<h2>What the comparison is actually good for</h2>
<p>The genuine analytical value here is separating age effects from cohort effects. If 25-year-olds today save less than 25-year-olds did in 1990, that could be because young people always save less (an age effect) or because this particular group faced different housing costs and wages (a cohort effect). Comparing cohorts at the same age is how researchers tell those apart.</p>
<p>What it is not good for is predicting individuals. Every cohort here spans 12 to 19 birth years and the entire world. The variation inside any one of them dwarfs the average difference between them — which is why most popular generational stereotypes fall apart when tested against actual survey data, something we look at in <a href="/blog/gen-z-strengths-and-weaknesses-research/">what the research shows about Gen Z</a>.</p>

<h2>Find your own cohort</h2>
<p>Enter your date of birth in our <a href="/tools/generation-calculator/">generation calculator</a> to see your cohort, its full range, and whether you sit on a cusp between two.</p>
HTML;
    }

    protected function postStrengthsWeaknesses(): string
    {
        return <<<'HTML'
<p>Almost everything written about Gen Z's personality is opinion dressed as analysis. This piece sticks to what large surveys and studies actually measure — and is explicit about where the evidence is thin or contested.</p>
<p>One caveat worth stating up front: every finding below is a population-level average across a cohort of roughly two billion people worldwide. None of it predicts anything about an individual.</p>

<h2>What the evidence reasonably supports</h2>

<h3>Higher formal education, worse early-career returns</h3>
<p>Gen Z has the highest rates of secondary and tertiary enrolment of any cohort in history. At the same time, graduate underemployment — working in roles that do not require the degree held — has risen in most OECD countries relative to when Millennials entered the workforce. Both things are true simultaneously, and the combination explains a lot of the frustration this cohort reports about education-to-work transitions.</p>

<h3>Genuinely different technology fluency, but not universal</h3>
<p>The "digital native" claim needs qualifying. Gen Z is unambiguously more fluent with mobile interfaces, short-form video and social platforms than earlier cohorts were at the same age. But multiple workplace studies have found this fluency does not automatically transfer to desktop productivity software, file systems or troubleshooting — skills earlier cohorts acquired precisely because the technology was harder to use. Growing up with technology that works well is not the same as growing up understanding it.</p>

<h3>Financial caution, driven by circumstance</h3>
<p>Surveys consistently find Gen Z reporting higher financial anxiety and more conservative attitudes toward debt than Millennials did at the same age. The straightforward explanation is that they watched the 2008 crisis affect their parents and older siblings, and are entering housing markets at historically poor price-to-income ratios. Deloitte's annual Gen Z and Millennial survey has found cost of living ranking as the top concern for this cohort for several consecutive years.</p>

<h3>Higher reported mental health difficulty</h3>
<p>Self-reported anxiety and depression are measurably higher in this cohort than in previous ones at comparable ages, across many countries. What the data does not settle is how much of that is a real increase in underlying prevalence versus a genuine increase in willingness to report and seek diagnosis — reduced stigma would produce a similar-looking rise. Researchers disagree about the split, and anyone who tells you it is definitively one or the other is overstating the evidence.</p>

<h2>Where the popular narrative is weakest</h2>

<h3>"Gen Z doesn't want to work"</h3>
<p>Labour force participation data does not support this. What surveys do find is different expectations about flexibility, remote arrangements and employer values — and a lower tolerance for staying in an unsatisfying job. That is a change in the terms of employment being negotiated, not an unwillingness to be employed. Nearly identical complaints were made about Millennials in 2010 and Gen X in 1993.</p>

<h3>"Gen Z has an eight-second attention span"</h3>
<p>This claim traces back to a widely-circulated statistic with no identifiable original study behind it, frequently attributed to a Microsoft report that did not actually make the claim. It is not supported by attention research. Treat it as a myth.</p>

<h3>"Gen Z is uniquely entitled"</h3>
<p>The entitlement charge has been levelled at every young cohort in living memory and consistently fails to hold up when the same measures are applied to earlier generations at the same age. It is close to a permanent feature of how older cohorts perceive younger ones rather than a finding about any specific one.</p>

<h2>How to read generational research at all</h2>
<p>Three questions worth asking about any claim you encounter:</p>
<ul>
<li><strong>Is it comparing cohorts at the same age?</strong> Comparing 22-year-olds today with 45-year-olds today measures age, not generation.</li>
<li><strong>Is it based on a survey with a stated sample, or on a consultant's assertion?</strong> A great deal of generational content is the latter.</li>
<li><strong>Does the claim have a plausible mechanism?</strong> "Grew up in a weaker entry-level job market, so accumulated less wealth by 25" is a mechanism. "Is inherently more entitled" is not.</li>
</ul>

<h2>Related reading</h2>
<p>For the definitions themselves, see <a href="/blog/what-does-gen-z-mean/">what Gen Z means</a> and <a href="/blog/gen-z-birth-years-what-dates-count/">Gen Z birth years</a>. For a cohort-by-cohort comparison, see <a href="/blog/gen-z-vs-gen-alpha-vs-millennials-vs-boomers/">Gen Z vs Gen Alpha vs Millennials vs Boomers</a>. To check your own cohort, use the <a href="/tools/generation-calculator/">generation calculator</a>.</p>
HTML;
    }
}
