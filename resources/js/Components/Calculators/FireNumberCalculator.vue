<script setup>
import { computed, ref } from 'vue';

const annualExpenses = ref(1200000);
const withdrawalRate = ref(4);
const currentSavings = ref(500000);
const monthlyContribution = ref(30000);
const annualReturn = ref(10);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

// The "4% rule": a portfolio this size can sustain your spending indefinitely
// at the given withdrawal rate (25x annual expenses at the classic 4%).
const fireNumber = computed(() => {
    if (withdrawalRate.value <= 0) return Infinity;
    return annualExpenses.value / (withdrawalRate.value / 100);
});

const yearsToFire = computed(() => {
    if (!isFinite(fireNumber.value)) return null;
    if (currentSavings.value >= fireNumber.value) return 0;

    const r = annualReturn.value / 100 / 12;
    let balance = currentSavings.value;
    let months = 0;
    const cap = 1200; // 100 years safety bound

    while (balance < fireNumber.value && months < cap) {
        balance = balance * (1 + r) + monthlyContribution.value;
        months++;
    }

    return months >= cap ? null : months;
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="fn-expenses">Annual expenses in retirement (PKR)</label>
                <input id="fn-expenses" v-model.number="annualExpenses" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="fn-rate">Safe withdrawal rate: <output>{{ withdrawalRate }}%</output></label>
                <input id="fn-rate" v-model.number="withdrawalRate" type="range" min="2" max="6" step="0.25" />
                <p class="hint">4% is the traditional "safe withdrawal rate" from FIRE research; some prefer 3.5% as more conservative.</p>
            </div>
            <div class="field">
                <label for="fn-savings">Current investable savings (PKR)</label>
                <input id="fn-savings" v-model.number="currentSavings" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="fn-monthly">Monthly contribution (PKR)</label>
                <input id="fn-monthly" v-model.number="monthlyContribution" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="fn-return">Expected annual return (%)</label>
                <input id="fn-return" v-model.number="annualReturn" type="number" min="0" max="30" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Your FIRE number</h2>
            <p class="result-big">{{ isFinite(fireNumber) ? fmt(fireNumber) : '—' }}</p>
            <p class="result-sub">Portfolio size needed to cover {{ fmt(annualExpenses) }}/year at a {{ withdrawalRate }}% withdrawal rate</p>
            <div class="result-rows">
                <div class="result-row">
                    <span>Time to reach it</span>
                    <b v-if="yearsToFire === 0">Already there!</b>
                    <b v-else-if="yearsToFire === null">50+ years</b>
                    <b v-else>{{ Math.floor(yearsToFire / 12) }}y {{ yearsToFire % 12 }}m</b>
                </div>
            </div>
        </div>
    </div>
</template>
