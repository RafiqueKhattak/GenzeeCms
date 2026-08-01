<script setup>
import { computed, ref } from 'vue';

const amount = ref(5000);
const rate = ref(18);
const mode = ref('add');

function fmt(x) {
    return `PKR ${x.toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const valid = computed(() => amount.value > 0);
const result = computed(() => {
    if (mode.value === 'extract') {
        const price = amount.value / (1 + rate.value / 100);
        return { price, tax: amount.value - price, total: amount.value };
    }
    const tax = (amount.value * rate.value) / 100;
    return { price: amount.value, tax, total: amount.value + tax };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="st-amount">Amount (PKR)</label>
                <input id="st-amount" v-model.number="amount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="st-rate">Tax rate (%)</label>
                <input id="st-rate" v-model.number="rate" type="number" min="0" max="100" step="any" inputmode="decimal" />
                <p class="hint">Pakistan standard GST: 18%. Provincial services taxes: 13–16%.</p>
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Direction">
                    <input id="st-add" v-model="mode" type="radio" value="add" /><label for="st-add">Add tax</label>
                    <input id="st-extract" v-model="mode" type="radio" value="extract" /><label for="st-extract">Price includes tax</label>
                </span>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>{{ mode === 'extract' ? 'Price before tax' : 'Total with tax' }}</h2>
            <p class="result-big">{{ valid ? fmt(mode === 'extract' ? result.price : result.total) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Price before tax</span><b>{{ valid ? fmt(result.price) : '—' }}</b></div>
                <div class="result-row"><span>Tax</span><b>{{ valid ? fmt(result.tax) : '—' }}</b></div>
                <div class="result-row"><span>Total</span><b>{{ valid ? fmt(result.total) : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
