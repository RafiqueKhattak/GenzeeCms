<script setup>
import { computed, ref } from 'vue';

const principal = ref(3000000);
const apr = ref(12);
const years = ref(5);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const n = computed(() => Math.max(0, Math.round(years.value * 12)));
const valid = computed(() => principal.value > 0 && n.value > 0);

const monthlyPayment = computed(() => {
    if (!valid.value) return 0;
    const r = apr.value / 100 / 12;
    if (r === 0) return principal.value / n.value;
    return (principal.value * r) / (1 - Math.pow(1 + r, -n.value));
});

// First-year schedule only, shown as a preview — the full n-row table would
// be excessive for a single-page calculator, and the first year is where
// the interest-vs-principal split is least intuitive to people.
const schedule = computed(() => {
    if (!valid.value) return [];
    const r = apr.value / 100 / 12;
    let balance = principal.value;
    const rows = [];
    for (let m = 1; m <= Math.min(12, n.value); m++) {
        const interest = balance * r;
        const principalPaid = Math.min(monthlyPayment.value - interest, balance);
        balance -= principalPaid;
        rows.push({ month: m, interest, principal: principalPaid, balance: Math.max(0, balance) });
    }
    return rows;
});

const totalPaid = computed(() => monthlyPayment.value * n.value);
const totalInterest = computed(() => Math.max(0, totalPaid.value - principal.value));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="am-principal">Loan amount (PKR)</label>
                <input id="am-principal" v-model.number="principal" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="am-apr">Annual interest rate (%)</label>
                <input id="am-apr" v-model.number="apr" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="am-years">Loan term (years)</label>
                <input id="am-years" v-model.number="years" type="number" min="0" max="40" step="0.5" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Monthly payment</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Monthly payment</h2>
                <p class="result-big">{{ fmt(monthlyPayment) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Total interest over the loan</span><b>{{ fmt(totalInterest) }}</b></div>
                    <div class="result-row"><span>Total repaid</span><b>{{ fmt(totalPaid) }}</b></div>
                </div>
                <table class="amort-table">
                    <caption>First year — how each payment splits</caption>
                    <thead>
                        <tr><th>Month</th><th>Principal</th><th>Interest</th><th>Balance</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in schedule" :key="row.month">
                            <td>{{ row.month }}</td>
                            <td>{{ fmt(row.principal) }}</td>
                            <td>{{ fmt(row.interest) }}</td>
                            <td>{{ fmt(row.balance) }}</td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </div>
    </div>
</template>

<style scoped>
.amort-table {
    width: 100%;
    margin-top: 1rem;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.amort-table caption {
    text-align: left;
    font-size: 0.78rem;
    color: var(--ink-muted, #6b7280);
    margin-bottom: 0.4rem;
}
.amort-table th,
.amort-table td {
    padding: 0.3rem 0.5rem;
    text-align: right;
    border-bottom: 1px solid var(--border, #e5e7eb);
}
.amort-table th:first-child,
.amort-table td:first-child {
    text-align: left;
}
</style>
