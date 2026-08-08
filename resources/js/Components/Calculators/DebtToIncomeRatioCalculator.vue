<script setup>
import { computed, ref } from 'vue';

const monthlyIncome = ref(200000);
const rent = ref(40000);
const loanPayments = ref(25000);
const creditCardMinimums = ref(8000);
const otherDebt = ref(0);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const totalDebt = computed(() => rent.value + loanPayments.value + creditCardMinimums.value + otherDebt.value);
const valid = computed(() => monthlyIncome.value > 0);
const dti = computed(() => (valid.value ? (totalDebt.value / monthlyIncome.value) * 100 : 0));

const band = computed(() => {
    if (dti.value <= 36) return { label: 'Healthy — typically within lender comfort zones', tone: 'good' };
    if (dti.value <= 43) return { label: 'Manageable but at the upper end of what many lenders allow', tone: 'warn' };
    return { label: 'High — may limit loan approval and leaves little budget flexibility', tone: 'bad' };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="dti-income">Monthly gross income (PKR)</label>
                <input id="dti-income" v-model.number="monthlyIncome" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="dti-rent">Rent or mortgage payment (PKR)</label>
                <input id="dti-rent" v-model.number="rent" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="dti-loans">Other loan payments (PKR)</label>
                <input id="dti-loans" v-model.number="loanPayments" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="dti-cc">Credit card minimum payments (PKR)</label>
                <input id="dti-cc" v-model.number="creditCardMinimums" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="dti-other">Other debt payments (PKR)</label>
                <input id="dti-other" v-model.number="otherDebt" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Debt-to-income ratio</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Debt-to-income ratio</h2>
                <p class="result-big">{{ dti.toFixed(1) }}%</p>
                <p class="result-sub">{{ band.label }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Total monthly debt payments</span><b>{{ fmt(totalDebt) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
