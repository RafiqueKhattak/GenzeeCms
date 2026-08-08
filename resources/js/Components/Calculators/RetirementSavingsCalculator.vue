<script setup>
import { computed, ref } from 'vue';

const currentAge = ref(25);
const retireAge = ref(60);
const currentSavings = ref(200000);
const monthlyContribution = ref(15000);
const annualReturn = ref(10);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const years = computed(() => Math.max(0, retireAge.value - currentAge.value));
const valid = computed(() => years.value > 0 && currentSavings.value >= 0 && monthlyContribution.value >= 0);

// Future value of a lump sum plus a monthly annuity, compounded monthly.
const projected = computed(() => {
    const n = years.value * 12;
    const r = annualReturn.value / 100 / 12;
    const lumpSum = currentSavings.value * Math.pow(1 + r, n);
    const annuity = r === 0 ? monthlyContribution.value * n : monthlyContribution.value * ((Math.pow(1 + r, n) - 1) / r);
    return lumpSum + annuity;
});

const totalContributed = computed(() => currentSavings.value + monthlyContribution.value * years.value * 12);
const growth = computed(() => Math.max(0, projected.value - totalContributed.value));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="rs-current-age">Current age</label>
                <input id="rs-current-age" v-model.number="currentAge" type="number" min="16" max="80" step="1" inputmode="numeric" />
            </div>
            <div class="field">
                <label for="rs-retire-age">Target retirement age</label>
                <input id="rs-retire-age" v-model.number="retireAge" type="number" min="17" max="90" step="1" inputmode="numeric" />
            </div>
            <div class="field">
                <label for="rs-savings">Current retirement savings (PKR)</label>
                <input id="rs-savings" v-model.number="currentSavings" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="rs-monthly">Monthly contribution (PKR)</label>
                <input id="rs-monthly" v-model.number="monthlyContribution" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="rs-return">Expected annual return (%)</label>
                <input id="rs-return" v-model.number="annualReturn" type="number" min="0" max="30" step="any" inputmode="decimal" />
                <p class="hint">A diversified investment mix historically averages 7-12% a year before inflation.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid || years === 0">
                <h2>Projected balance at retirement</h2>
                <p class="result-big">—</p>
                <p class="result-sub">Retirement age must be after your current age.</p>
            </template>
            <template v-else>
                <h2>Projected balance at retirement</h2>
                <p class="result-big">{{ fmt(projected) }}</p>
                <p class="result-sub">In {{ years }} years, at age {{ retireAge }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Total you'll contribute</span><b>{{ fmt(totalContributed) }}</b></div>
                    <div class="result-row"><span>Growth from returns</span><b>{{ fmt(growth) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
