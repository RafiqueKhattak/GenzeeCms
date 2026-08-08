<script setup>
import { computed, ref } from 'vue';

const loanA = ref({ amount: 3000000, apr: 14, years: 5 });
const loanB = ref({ amount: 3000000, apr: 12.5, years: 5 });

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

function monthlyPayment(loan) {
    const n = loan.years * 12;
    const r = loan.apr / 100 / 12;
    if (n <= 0 || loan.amount <= 0) return 0;
    if (r === 0) return loan.amount / n;
    return (loan.amount * r) / (1 - Math.pow(1 + r, -n));
}

function summarize(loan) {
    const payment = monthlyPayment(loan);
    const totalPaid = payment * loan.years * 12;
    const totalInterest = Math.max(0, totalPaid - loan.amount);
    return { payment, totalPaid, totalInterest };
}

const resultA = computed(() => summarize(loanA.value));
const resultB = computed(() => summarize(loanB.value));
const cheaper = computed(() => (resultA.value.totalInterest <= resultB.value.totalInterest ? 'A' : 'B'));
const interestDifference = computed(() => Math.abs(resultA.value.totalInterest - resultB.value.totalInterest));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field-row">
                <div class="field">
                    <label for="lc-a-amount">Loan A amount (PKR)</label>
                    <input id="lc-a-amount" v-model.number="loanA.amount" type="number" min="0" step="any" inputmode="decimal" />
                    <label for="lc-a-apr">Loan A rate (%)</label>
                    <input id="lc-a-apr" v-model.number="loanA.apr" type="number" min="0" max="60" step="any" inputmode="decimal" />
                    <label for="lc-a-years">Loan A term (years)</label>
                    <input id="lc-a-years" v-model.number="loanA.years" type="number" min="0" max="40" step="0.5" inputmode="decimal" />
                </div>
                <div class="field">
                    <label for="lc-b-amount">Loan B amount (PKR)</label>
                    <input id="lc-b-amount" v-model.number="loanB.amount" type="number" min="0" step="any" inputmode="decimal" />
                    <label for="lc-b-apr">Loan B rate (%)</label>
                    <input id="lc-b-apr" v-model.number="loanB.apr" type="number" min="0" max="60" step="any" inputmode="decimal" />
                    <label for="lc-b-years">Loan B term (years)</label>
                    <input id="lc-b-years" v-model.number="loanB.years" type="number" min="0" max="40" step="0.5" inputmode="decimal" />
                </div>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Loan {{ cheaper }} costs less overall</h2>
            <p class="result-big">{{ fmt(interestDifference) }} less interest</p>
            <div class="result-rows">
                <div class="result-row"><span>Loan A — monthly payment</span><b>{{ fmt(resultA.payment) }}</b></div>
                <div class="result-row"><span>Loan A — total interest</span><b>{{ fmt(resultA.totalInterest) }}</b></div>
                <div class="result-row"><span>Loan B — monthly payment</span><b>{{ fmt(resultB.payment) }}</b></div>
                <div class="result-row"><span>Loan B — total interest</span><b>{{ fmt(resultB.totalInterest) }}</b></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
</style>
