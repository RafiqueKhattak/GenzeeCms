<script setup>
import { computed, ref } from 'vue';

const monthlyExpenses = ref(80000);
const monthsTarget = ref(6);
const currentSavings = ref(100000);
const monthlySaving = ref(15000);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => monthlyExpenses.value > 0 && monthsTarget.value > 0);
const targetAmount = computed(() => monthlyExpenses.value * monthsTarget.value);
const gap = computed(() => Math.max(0, targetAmount.value - currentSavings.value));
const monthsToTarget = computed(() => {
    if (gap.value === 0) return 0;
    if (monthlySaving.value <= 0) return Infinity;
    return Math.ceil(gap.value / monthlySaving.value);
});
const coveredMonths = computed(() => (monthlyExpenses.value > 0 ? currentSavings.value / monthlyExpenses.value : 0));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ef-expenses">Essential monthly expenses (PKR)</label>
                <input id="ef-expenses" v-model.number="monthlyExpenses" type="number" min="0" step="any" inputmode="decimal" />
                <p class="hint">Rent, groceries, utilities, minimum debt payments — what you'd need even with no income.</p>
            </div>
            <div class="field">
                <label for="ef-months">Target coverage: <output>{{ monthsTarget }} months</output></label>
                <input id="ef-months" v-model.number="monthsTarget" type="range" min="1" max="12" step="1" />
                <p class="hint">3 months is a common minimum; 6 is a common target for less stable income.</p>
            </div>
            <div class="field">
                <label for="ef-current">Already saved for emergencies (PKR)</label>
                <input id="ef-current" v-model.number="currentSavings" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ef-monthly">Monthly amount you can add (PKR)</label>
                <input id="ef-monthly" v-model.number="monthlySaving" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Emergency fund target</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Emergency fund target</h2>
                <p class="result-big">{{ fmt(targetAmount) }}</p>
                <p class="result-sub">Currently covers {{ coveredMonths.toFixed(1) }} of {{ monthsTarget }} months</p>
                <div class="result-rows">
                    <div class="result-row"><span>Still needed</span><b>{{ fmt(gap) }}</b></div>
                    <div class="result-row">
                        <span>Time to reach it</span>
                        <b v-if="monthsToTarget === 0">Already there!</b>
                        <b v-else-if="!isFinite(monthsToTarget)">—</b>
                        <b v-else>{{ Math.floor(monthsToTarget / 12) ? `${Math.floor(monthsToTarget / 12)}y ` : '' }}{{ monthsToTarget % 12 }}m</b>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
