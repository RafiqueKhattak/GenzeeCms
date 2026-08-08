<script setup>
import { computed, ref } from 'vue';

const monthlyIncome = ref(250000);
const existingDebtPayments = ref(20000);
const downPayment = ref(3000000);
const apr = ref(14);
const termYears = ref(20);
const maxDebtToIncomePct = ref(40);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => monthlyIncome.value > 0);

// Cap total monthly debt (existing + new mortgage payment) at a target
// debt-to-income ratio, then solve backward for the loan amount that
// produces exactly that payment.
const maxTotalDebtPayment = computed(() => (monthlyIncome.value * maxDebtToIncomePct.value) / 100);
const maxMortgagePayment = computed(() => Math.max(0, maxTotalDebtPayment.value - existingDebtPayments.value));

const maxLoanAmount = computed(() => {
    const n = termYears.value * 12;
    const r = apr.value / 100 / 12;
    if (n <= 0) return 0;
    if (r === 0) return maxMortgagePayment.value * n;
    return maxMortgagePayment.value * ((1 - Math.pow(1 + r, -n)) / r);
});

const maxHomePrice = computed(() => maxLoanAmount.value + downPayment.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ma-income">Monthly take-home income (PKR)</label>
                <input id="ma-income" v-model.number="monthlyIncome" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ma-debt">Existing monthly debt payments (PKR)</label>
                <input id="ma-debt" v-model.number="existingDebtPayments" type="number" min="0" step="any" inputmode="decimal" />
                <p class="hint">Car loans, credit card minimums, other loan payments.</p>
            </div>
            <div class="field">
                <label for="ma-down">Down payment available (PKR)</label>
                <input id="ma-down" v-model.number="downPayment" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ma-apr">Mortgage interest rate (%)</label>
                <input id="ma-apr" v-model.number="apr" type="number" min="0" max="40" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ma-term">Loan term (years)</label>
                <input id="ma-term" v-model.number="termYears" type="number" min="1" max="35" step="1" inputmode="numeric" />
            </div>
            <div class="field">
                <label for="ma-dti">Max debt-to-income: <output>{{ maxDebtToIncomePct }}%</output></label>
                <input id="ma-dti" v-model.number="maxDebtToIncomePct" type="range" min="20" max="50" step="1" />
                <p class="hint">Many lenders cap total debt payments (including the new mortgage) around 36-43% of income.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Estimated affordable home price</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Estimated affordable home price</h2>
                <p class="result-big">{{ fmt(maxHomePrice) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Maximum loan amount</span><b>{{ fmt(maxLoanAmount) }}</b></div>
                    <div class="result-row"><span>Estimated mortgage payment</span><b>{{ fmt(maxMortgagePayment) }}/mo</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
