<script setup>
import { computed, ref } from 'vue';

const balance = ref(150000);
const apr = ref(30);
const minPaymentPct = ref(5);
const minPaymentFloor = ref(2000);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => balance.value > 0);

// Minimum payment is typically "X% of balance, or a flat floor, whichever is
// greater" — and it shrinks every month as the balance shrinks, unlike a
// fixed payoff plan. That's what makes it a "trap": the payment keeps
// dropping just as the balance does, so payoff drags on far longer than a
// fixed-payment plan on the same starting balance.
const result = computed(() => {
    if (!valid.value) return null;

    const r = apr.value / 100 / 12;
    let remaining = balance.value;
    let months = 0;
    let totalInterest = 0;
    let totalPaid = 0;
    const cap = 1200;

    while (remaining > 0 && months < cap) {
        const interest = remaining * r;
        const payment = Math.max(remaining * (minPaymentPct.value / 100), minPaymentFloor.value, interest + 1);
        const actualPayment = Math.min(payment, remaining + interest);
        const principal = actualPayment - interest;

        remaining -= principal;
        totalInterest += interest;
        totalPaid += actualPayment;
        months++;
    }

    return { months, totalInterest, totalPaid, hitCap: months >= cap };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="cm-balance">Current balance (PKR)</label>
                <input id="cm-balance" v-model.number="balance" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cm-apr">Interest rate, APR (%)</label>
                <input id="cm-apr" v-model.number="apr" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cm-pct">Minimum payment: <output>{{ minPaymentPct }}% of balance</output></label>
                <input id="cm-pct" v-model.number="minPaymentPct" type="range" min="1" max="10" step="0.5" />
            </div>
            <div class="field">
                <label for="cm-floor">Minimum payment floor (PKR)</label>
                <input id="cm-floor" v-model.number="minPaymentFloor" type="number" min="0" step="any" inputmode="decimal" />
                <p class="hint">Card issuers usually charge whichever is higher — the percentage, or this flat minimum.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Time to pay off at minimum</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else-if="result.hitCap">
                <h2>Time to pay off at minimum</h2>
                <p class="result-big">50+ years</p>
                <p class="result-sub">At this rate and minimum payment, the balance barely shrinks — paying only the minimum will not clear it in any reasonable time.</p>
            </template>
            <template v-else>
                <h2>Time to pay off at minimum</h2>
                <p class="result-big">{{ Math.floor(result.months / 12) }}y {{ result.months % 12 }}m</p>
                <p class="result-sub">Paying only the minimum every month</p>
                <div class="result-rows">
                    <div class="result-row"><span>Total interest paid</span><b>{{ fmt(result.totalInterest) }}</b></div>
                    <div class="result-row"><span>Total paid overall</span><b>{{ fmt(result.totalPaid) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
