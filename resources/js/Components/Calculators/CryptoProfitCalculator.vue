<script setup>
import { computed, ref } from 'vue';

const buyPrice = ref(20000);
const quantity = ref(0.5);
const buyFee = ref(0.5);
const sellPrice = ref(60000);
const sellFee = ref(0.5);
const taxRate = ref(0);
const currency = ref('USD');

function fmt(x) {
    return `${currency.value} ${Math.round(x).toLocaleString('en-US')}`;
}
function signed(x, formatted) {
    return (x >= 0 ? '+' : '') + formatted;
}

const basis = computed(() => {
    const gross = buyPrice.value * quantity.value;
    return gross + gross * (buyFee.value / 100);
});
const proceeds = computed(() => {
    const gross = sellPrice.value * quantity.value;
    return gross - gross * (sellFee.value / 100);
});
const profit = computed(() => proceeds.value - basis.value);
const roi = computed(() => (basis.value <= 0 ? 0 : (profit.value / basis.value) * 100));
const tax = computed(() => (profit.value <= 0 ? 0 : profit.value * (taxRate.value / 100)));
const afterTax = computed(() => profit.value - tax.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="cp-buy-price">Buy price (per unit)</label>
                <input id="cp-buy-price" v-model.number="buyPrice" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cp-quantity">Quantity</label>
                <input id="cp-quantity" v-model.number="quantity" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cp-buy-fee">Buy fee (%)</label>
                <input id="cp-buy-fee" v-model.number="buyFee" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cp-sell-price">Sell price (per unit)</label>
                <input id="cp-sell-price" v-model.number="sellPrice" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cp-sell-fee">Sell fee (%)</label>
                <input id="cp-sell-fee" v-model.number="sellFee" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cp-tax-rate">Estimated tax rate (%, optional)</label>
                <input id="cp-tax-rate" v-model.number="taxRate" type="number" min="0" max="100" step="any" inputmode="decimal" />
                <p class="hint">Your capital gains rate — varies by country. Leave at 0 to skip.</p>
            </div>
            <div class="field">
                <label for="cp-currency">Currency label</label>
                <input id="cp-currency" v-model="currency" type="text" maxlength="5" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Profit / loss</h2>
            <p class="result-big">{{ signed(profit, fmt(profit)) }}</p>
            <p class="result-sub">{{ signed(roi, roi.toFixed(2) + '%') }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Cost basis (incl. buy fee)</span><b>{{ fmt(basis) }}</b></div>
                <div class="result-row"><span>Proceeds (after sell fee)</span><b>{{ fmt(proceeds) }}</b></div>
                <div class="result-row"><span>Estimated tax</span><b>{{ fmt(tax) }}</b></div>
                <div class="result-row"><span>After-tax profit</span><b>{{ signed(afterTax, fmt(afterTax)) }}</b></div>
            </div>
        </div>
    </div>
</template>
