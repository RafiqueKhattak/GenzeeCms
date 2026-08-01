<script setup>
import { computed, ref } from 'vue';

const price = ref(400);
const installments = ref(4);
const apr = ref(0);
const lateFee = ref(8);
const missed = ref(0);
const currency = ref('USD');

function fmt(x) {
    return `${currency.value} ${x.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
function signed(x, formatted) {
    return (x >= 0 ? '+' : '') + formatted;
}

const n = computed(() => Math.round(installments.value));
const valid = computed(() => price.value > 0 && n.value > 0);
const installment = computed(() => {
    if (!valid.value) return 0;
    const r = apr.value / 100 / 12;
    if (r === 0) return price.value / n.value;
    const f = Math.pow(1 + r, n.value);
    return (price.value * r * f) / (f - 1);
});
const total = computed(() => {
    const missedCount = Math.max(0, Math.min(Math.round(missed.value), n.value));
    return installment.value * n.value + lateFee.value * missedCount;
});
const extra = computed(() => total.value - price.value);
const extraPct = computed(() => (price.value <= 0 ? 0 : (extra.value / price.value) * 100));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="bnpl-price">Purchase price</label>
                <input id="bnpl-price" v-model.number="price" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="bnpl-installments">Number of installments</label>
                <input id="bnpl-installments" v-model.number="installments" type="number" min="1" step="1" inputmode="numeric" />
                <p class="hint">4 for a classic Pay-in-4 plan; 6, 12 or 24 for longer financing.</p>
            </div>
            <div class="field">
                <label for="bnpl-apr">Financing interest rate (APR %, optional)</label>
                <input id="bnpl-apr" v-model.number="apr" type="number" min="0" max="100" step="any" inputmode="decimal" />
                <p class="hint">Leave at 0 for a standard interest-free Pay-in-4 plan.</p>
            </div>
            <div class="field">
                <label for="bnpl-late-fee">Late fee per missed payment</label>
                <input id="bnpl-late-fee" v-model.number="lateFee" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="bnpl-missed">Payments you expect to miss</label>
                <input id="bnpl-missed" v-model.number="missed" type="number" min="0" step="1" inputmode="numeric" />
            </div>
            <div class="field">
                <label for="bnpl-currency">Currency label</label>
                <input id="bnpl-currency" v-model="currency" type="text" maxlength="5" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Per installment</h2>
            <p class="result-big">{{ valid ? fmt(installment) : '—' }}</p>
            <p class="result-sub">{{ valid ? `${n} payments of ${fmt(installment)}` : '' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Total you'll pay</span><b>{{ valid ? fmt(total) : '—' }}</b></div>
                <div class="result-row"><span>Extra vs price today</span><b>{{ valid ? signed(extra, fmt(extra)) : '—' }}</b></div>
            </div>
            <p class="result-sub">{{ valid ? `${signed(extra, extraPct.toFixed(1) + '%')} vs paying the price today` : '' }}</p>
        </div>
    </div>
</template>
