<script setup>
import { computed, ref } from 'vue';

/**
 * Cohort boundaries follow the Pew Research Center definitions, which are the
 * most widely cited in journalism and academic work (Gen Z = 1997-2012 was
 * formally set by Pew in 2019). Gen Alpha and Gen Beta have no Pew definition
 * yet — those ranges follow McCrindle Research, who coined both terms.
 */
const GENERATIONS = [
    {
        name: 'Generation Beta',
        short: 'Gen Beta',
        start: 2025,
        end: 2039,
        source: 'McCrindle Research',
        blurb: 'The newest named cohort. Born into a world where AI assistants and voice interfaces are simply how technology has always worked.',
    },
    {
        name: 'Generation Alpha',
        short: 'Gen Alpha',
        start: 2013,
        end: 2024,
        source: 'McCrindle Research',
        blurb: 'The children of Millennials. The first cohort born entirely within the smartphone and tablet era — most have never known a home without a touchscreen.',
    },
    {
        name: 'Generation Z',
        short: 'Gen Z',
        start: 1997,
        end: 2012,
        source: 'Pew Research Center',
        blurb: 'The first true digital natives — old enough to remember life before TikTok, young enough that they never had to learn the internet as an adult. Came of age during COVID-19 and entered a job market reshaped by it.',
    },
    {
        name: 'Millennials (Generation Y)',
        short: 'Millennials',
        start: 1981,
        end: 1996,
        source: 'Pew Research Center',
        blurb: 'Grew up as the internet went mainstream. Entered the workforce around the 2008 financial crisis, which shaped much of their financial outlook.',
    },
    {
        name: 'Generation X',
        short: 'Gen X',
        start: 1965,
        end: 1980,
        source: 'Pew Research Center',
        blurb: 'The "latchkey" generation — grew up analogue and adapted to digital as adults. Often described as the bridge cohort between Boomers and Millennials.',
    },
    {
        name: 'Baby Boomers',
        short: 'Boomers',
        start: 1946,
        end: 1964,
        source: 'Pew Research Center',
        blurb: 'Born in the post-war population surge. Currently at or approaching retirement age worldwide.',
    },
    {
        name: 'The Silent Generation',
        short: 'Silent Gen',
        start: 1928,
        end: 1945,
        source: 'Pew Research Center',
        blurb: 'Grew up through the Great Depression and the Second World War.',
    },
];

const dob = ref('');

const result = computed(() => {
    if (!dob.value) return null;

    const [year, month, day] = dob.value.split('-').map(Number);
    if (!year || !month || !day) return null;

    const birth = new Date(year, month - 1, day);
    const now = new Date();
    if (birth > now) return { future: true };

    const generation = GENERATIONS.find((g) => year >= g.start && year <= g.end);
    if (!generation) {
        return { unknown: true, year };
    }

    let age = now.getFullYear() - year;
    const hadBirthday =
        now.getMonth() > month - 1 || (now.getMonth() === month - 1 && now.getDate() >= day);
    if (!hadBirthday) age -= 1;

    // Someone born in the first or last year of a cohort is often described as
    // being "on the cusp" — worth flagging, since different researchers draw
    // these lines a year or two apart.
    const onCusp = year === generation.start || year === generation.end;
    const neighbour = year === generation.start
        ? GENERATIONS.find((g) => g.end === generation.start - 1)
        : GENERATIONS.find((g) => g.start === generation.end + 1);

    return {
        generation,
        age,
        onCusp,
        isFirstYear: year === generation.start,
        neighbour,
        yearsIntoCohort: year - generation.start + 1,
        cohortLength: generation.end - generation.start + 1,
    };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="gen-dob">Date of birth</label>
                <input id="gen-dob" v-model="dob" type="date" />
                <p class="hint">Nothing you enter is sent anywhere — this runs entirely in your browser.</p>
            </div>
        </form>

        <div class="result-panel" aria-live="polite">
            <h2>Your generation</h2>

            <template v-if="result?.future">
                <p class="result-big">Date is in the future</p>
            </template>

            <template v-else-if="result?.unknown">
                <p class="result-big">Before the named cohorts</p>
                <p class="result-sub">
                    {{ result.year }} falls before the Silent Generation (1928), which is the earliest cohort Pew
                    Research Center formally names.
                </p>
            </template>

            <template v-else-if="result">
                <p class="result-big">{{ result.generation.name }}</p>
                <p class="result-sub">
                    Born {{ result.generation.start }}–{{ result.generation.end }} · you are {{ result.age }} years old
                </p>

                <div class="result-rows">
                    <div class="result-row">
                        <span>Cohort range</span>
                        <b>{{ result.generation.start }}–{{ result.generation.end }}</b>
                    </div>
                    <div class="result-row">
                        <span>Where you fall</span>
                        <b>Year {{ result.yearsIntoCohort }} of {{ result.cohortLength }}</b>
                    </div>
                    <div class="result-row">
                        <span>Definition source</span>
                        <b>{{ result.generation.source }}</b>
                    </div>
                </div>

                <p class="gen-blurb">{{ result.generation.blurb }}</p>

                <p v-if="result.onCusp && result.neighbour" class="gen-cusp">
                    <strong>You're on the cusp.</strong> {{ result.isFirstYear ? 'Being born in the first year' : 'Being born in the final year' }}
                    of a cohort means some researchers would place you in
                    {{ result.neighbour.name }} instead — generational boundaries vary by a year or two between
                    sources.
                </p>
            </template>

            <p v-else class="result-big">—</p>
        </div>

        <details class="gen-table-wrap">
            <summary>See all generations and their birth-year ranges</summary>
            <table class="gen-table">
                <thead>
                    <tr>
                        <th>Generation</th>
                        <th>Birth years</th>
                        <th>Defined by</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="g in GENERATIONS" :key="g.name">
                        <td>{{ g.name }}</td>
                        <td>{{ g.start }}–{{ g.end }}</td>
                        <td>{{ g.source }}</td>
                    </tr>
                </tbody>
            </table>
        </details>
    </div>
</template>

<style scoped>
.gen-blurb {
    margin-top: 1rem;
    line-height: 1.6;
}
.gen-cusp {
    margin-top: 0.75rem;
    padding: 0.75rem;
    border-radius: 0.4rem;
    background: rgba(0, 0, 0, 0.04);
    line-height: 1.6;
}
.gen-table-wrap {
    margin-top: 1.25rem;
}
.gen-table-wrap summary {
    cursor: pointer;
    font-weight: 600;
}
.gen-table {
    width: 100%;
    margin-top: 0.75rem;
    border-collapse: collapse;
    font-size: 0.95rem;
}
.gen-table th,
.gen-table td {
    padding: 0.5rem 0.4rem;
    text-align: left;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
