<script setup>
import { computed, ref } from 'vue';

const desiredAnnualIncome = ref(2400000);
const workWeeksPerYear = ref(48);
const billableHoursPerWeek = ref(25);
const businessExpensesPct = ref(15);
const taxRatePct = ref(20);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => desiredAnnualIncome.value > 0 && workWeeksPerYear.value > 0 && billableHoursPerWeek.value > 0);

// Gross the take-home target up for tax and business expenses first, then
// spread it across billable (not total) hours — non-billable time like
// admin, marketing and finding clients doesn't disappear just because it
// isn't invoiced.
const grossNeeded = computed(() => {
    const afterExpenses = desiredAnnualIncome.value / (1 - businessExpensesPct.value / 100);
    return afterExpenses / (1 - taxRatePct.value / 100);
});

const totalBillableHours = computed(() => workWeeksPerYear.value * billableHoursPerWeek.value);
const hourlyRate = computed(() => (totalBillableHours.value > 0 ? grossNeeded.value / totalBillableHours.value : 0));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="fr-income">Desired take-home income per year (PKR)</label>
                <input id="fr-income" v-model.number="desiredAnnualIncome" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="fr-weeks">Working weeks per year</label>
                <input id="fr-weeks" v-model.number="workWeeksPerYear" type="number" min="1" max="52" step="1" inputmode="numeric" />
                <p class="hint">52 minus holidays, sick time and unpaid breaks.</p>
            </div>
            <div class="field">
                <label for="fr-hours">Billable hours per week</label>
                <input id="fr-hours" v-model.number="billableHoursPerWeek" type="number" min="1" max="80" step="1" inputmode="numeric" />
                <p class="hint">Only hours you actually invoice — not time spent on admin, pitching or learning.</p>
            </div>
            <div class="field">
                <label for="fr-expenses">Business expenses: <output>{{ businessExpensesPct }}%</output> of revenue</label>
                <input id="fr-expenses" v-model.number="businessExpensesPct" type="range" min="0" max="50" step="1" />
            </div>
            <div class="field">
                <label for="fr-tax">Tax rate: <output>{{ taxRatePct }}%</output></label>
                <input id="fr-tax" v-model.number="taxRatePct" type="range" min="0" max="50" step="1" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Rate to charge</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Rate to charge</h2>
                <p class="result-big">{{ fmt(hourlyRate) }}/hour</p>
                <p class="result-sub">Based on {{ totalBillableHours }} billable hours a year</p>
                <div class="result-rows">
                    <div class="result-row"><span>Revenue needed before tax & expenses</span><b>{{ fmt(grossNeeded) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
