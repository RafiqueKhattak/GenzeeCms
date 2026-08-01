<script setup>
import { computed, ref } from 'vue';

const amount = ref(1000000);
const rate = ref(15);
const tenure = ref(5);
const tenureUnit = ref('years');
const currency = ref('PKR');

function emi(P, r, n) {
    if (n <= 0 || P <= 0) return 0;
    if (r === 0) return P / n;
    const f = Math.pow(1 + r, n);
    return (P * r * f) / (f - 1);
}

function schedule(P, r, n) {
    let m = emi(P, r, n);
    const rows = [];
    let balance = P;
    for (let i = 1; i <= n; i++) {
        let interest = balance * r;
        let principal = m - interest;
        if (i === n) {
            principal = balance;
            m = balance + interest;
        }
        balance = Math.max(0, balance - principal);
        rows.push({ month: i, payment: m, principal, interest, balance });
    }
    return rows;
}

function fmt(x) {
    return `${currency.value} ${Math.round(x).toLocaleString('en-PK')}`;
}

const n = computed(() => Math.round(tenureUnit.value === 'years' ? tenure.value * 12 : tenure.value));
const r = computed(() => rate.value / 12 / 100);
const valid = computed(() => amount.value > 0 && n.value > 0);
const monthly = computed(() => (valid.value ? emi(amount.value, r.value, n.value) : 0));
const rows = computed(() => (valid.value ? schedule(amount.value, r.value, n.value) : []));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="loan-amount">Loan amount</label>
                <input id="loan-amount" v-model.number="amount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="loan-rate">Annual interest rate (%)</label>
                <input id="loan-rate" v-model.number="rate" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="loan-tenure">Loan tenure</label>
                <div class="field-row">
                    <input id="loan-tenure" v-model.number="tenure" type="number" min="0" step="any" inputmode="decimal" />
                    <span class="segmented" role="group" aria-label="Tenure unit">
                        <input id="tenure-years" v-model="tenureUnit" type="radio" value="years" /><label for="tenure-years">Years</label>
                        <input id="tenure-months" v-model="tenureUnit" type="radio" value="months" /><label for="tenure-months">Months</label>
                    </span>
                </div>
            </div>
            <div class="field">
                <label for="loan-currency">Currency label</label>
                <input id="loan-currency" v-model="currency" type="text" maxlength="5" />
                <p class="hint">Only a label — change it to Rs, USD, AED, etc.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Monthly installment</h2>
            <p class="result-big">{{ valid ? fmt(monthly) : '—' }}</p>
            <p class="result-sub">{{ valid ? `${n} monthly payments` : '' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Total interest</span><b>{{ valid ? fmt(monthly * n - amount) : '—' }}</b></div>
                <div class="result-row"><span>Total payment</span><b>{{ valid ? fmt(monthly * n) : '—' }}</b></div>
            </div>
        </div>
    </div>
    <details v-if="valid" class="fold no-print">
        <summary>Show amortization schedule (month by month)</summary>
        <div class="fold-body">
            <div class="table-wrap">
                <table>
                    <thead><tr><th scope="col">Month</th><th scope="col" class="num">Payment</th><th scope="col" class="num">Principal</th><th scope="col" class="num">Interest</th><th scope="col" class="num">Balance</th></tr></thead>
                    <tbody>
                        <tr v-for="row in rows" :key="row.month">
                            <td>{{ row.month }}</td>
                            <td class="num">{{ fmt(row.payment) }}</td>
                            <td class="num">{{ fmt(row.principal) }}</td>
                            <td class="num">{{ fmt(row.interest) }}</td>
                            <td class="num">{{ fmt(row.balance) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </details>
</template>
