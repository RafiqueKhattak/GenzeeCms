<script setup>
import { computed, ref } from 'vue';

const balance = ref(300000);
const apr = ref(24);
const monthlyPayment = ref(15000);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

// Minimum payment needed just to cover the first month's interest — below
// this the balance never shrinks, however long you "pay".
const minViablePayment = computed(() => (balance.value * (apr.value / 100)) / 12);

const valid = computed(() => balance.value > 0 && monthlyPayment.value > minViablePayment.value);

const payoff = computed(() => {
    if (!valid.value) return null;

    const r = apr.value / 100 / 12;
    let remaining = balance.value;
    let months = 0;
    let totalInterest = 0;
    const cap = 1200; // 100 years — safety bound against pathological inputs

    while (remaining > 0 && months < cap) {
        const interest = remaining * r;
        const principal = Math.min(monthlyPayment.value - interest, remaining);
        remaining -= principal;
        totalInterest += interest;
        months++;
    }

    return { months, totalInterest, totalPaid: balance.value + totalInterest };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="dp-balance">Current balance (PKR)</label>
                <input id="dp-balance" v-model.number="balance" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="dp-apr">Interest rate, APR (%)</label>
                <input id="dp-apr" v-model.number="apr" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="dp-payment">Fixed monthly payment (PKR)</label>
                <input id="dp-payment" v-model.number="monthlyPayment" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Time to pay off</h2>
                <p class="result-big">—</p>
                <p class="result-sub">
                    Your payment must be more than {{ fmt(minViablePayment) }}/month, otherwise interest alone eats it and the balance never shrinks.
                </p>
            </template>
            <template v-else>
                <h2>Time to pay off</h2>
                <p class="result-big">{{ Math.floor(payoff.months / 12) ? `${Math.floor(payoff.months / 12)}y ` : '' }}{{ payoff.months % 12 }}m</p>
                <p class="result-sub">{{ payoff.months }} monthly payments of {{ fmt(monthlyPayment) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Total interest paid</span><b>{{ fmt(payoff.totalInterest) }}</b></div>
                    <div class="result-row"><span>Total paid overall</span><b>{{ fmt(payoff.totalPaid) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
