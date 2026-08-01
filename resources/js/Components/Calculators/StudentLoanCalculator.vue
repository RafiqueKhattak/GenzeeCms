<script setup>
import { computed, ref } from 'vue';

const balance = ref(30000);
const rate = ref(6);
const term = ref(10);
const extra = ref(0);
const currency = ref('USD');

function fmt(x) {
    return `${currency.value} ${Math.round(x).toLocaleString('en-US')}`;
}

function monthlyPayment(P, r, n) {
    if (n <= 0 || P <= 0) return 0;
    if (r === 0) return P / n;
    const f = Math.pow(1 + r, n);
    return (P * r * f) / (f - 1);
}

function simulatePayoff(P, r, payment) {
    if (P <= 0) return { months: 0, totalInterest: 0, totalPaid: 0 };
    let balanceLeft = P, months = 0, totalInterest = 0, totalPaid = 0;
    while (balanceLeft > 0 && months < 1200) {
        const interest = balanceLeft * r;
        const principalPortion = payment - interest;
        if (principalPortion <= 0) {
            months = Infinity;
            break;
        }
        if (principalPortion > balanceLeft) {
            totalPaid += balanceLeft + interest;
            totalInterest += interest;
            balanceLeft = 0;
        } else {
            balanceLeft -= principalPortion;
            totalInterest += interest;
            totalPaid += payment;
        }
        months++;
    }
    return { months, totalInterest, totalPaid };
}

function years(months) {
    if (!isFinite(months)) return 'never (payment too low)';
    const y = Math.floor(months / 12);
    const m = months % 12;
    if (y === 0) return `${m} month${m === 1 ? '' : 's'}`;
    return `${y} yr ${m ? m + ' mo' : ''}`;
}

const r = computed(() => rate.value / 12 / 100);
const n = computed(() => Math.round(term.value * 12));
const valid = computed(() => balance.value > 0 && n.value > 0);
const basePayment = computed(() => (valid.value ? monthlyPayment(balance.value, r.value, n.value) : 0));
const standard = computed(() => (valid.value ? simulatePayoff(balance.value, r.value, basePayment.value) : null));
const withExtra = computed(() => (valid.value && extra.value > 0 ? simulatePayoff(balance.value, r.value, basePayment.value + extra.value) : null));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="sl-balance">Loan balance</label>
                <input id="sl-balance" v-model.number="balance" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="sl-rate">Annual interest rate (%)</label>
                <input id="sl-rate" v-model.number="rate" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="sl-term">Loan term (years)</label>
                <input id="sl-term" v-model.number="term" type="number" min="1" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="sl-extra">Extra payment per month (optional)</label>
                <input id="sl-extra" v-model.number="extra" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="sl-currency">Currency label</label>
                <input id="sl-currency" v-model="currency" type="text" maxlength="5" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Monthly payment</h2>
            <p class="result-big">{{ valid ? fmt(basePayment) : '—' }}</p>
            <p class="result-sub">
                <template v-if="valid && withExtra">Paid off in {{ years(withExtra.months) }} (was {{ years(standard.months) }})</template>
                <template v-else-if="valid">Paid off in {{ years(standard.months) }}</template>
            </p>
            <div class="result-rows">
                <div class="result-row"><span>Total interest</span><b>{{ valid ? fmt(standard.totalInterest) : '—' }}</b></div>
                <div class="result-row"><span>Total repaid</span><b>{{ valid ? fmt(standard.totalPaid) : '—' }}</b></div>
                <div class="result-row">
                    <span>Interest saved by extra payment</span>
                    <b>{{ valid && withExtra ? fmt(Math.max(0, standard.totalInterest - withExtra.totalInterest)) : (valid ? fmt(0) : '—') }}</b>
                </div>
                <div class="result-row">
                    <span>Paid off</span>
                    <b>{{ valid && withExtra && isFinite(withExtra.months) ? `${years(standard.months - withExtra.months)} sooner` : '—' }}</b>
                </div>
            </div>
        </div>
    </div>
</template>
