<script setup>
import { computed, ref } from 'vue';

const amount = ref(1000);
const rate = ref(20);
const mode = ref('add');
const currency = ref('PKR');

function fmt(x) {
    return `${currency.value} ${x.toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const valid = computed(() => amount.value > 0);
const result = computed(() => {
    if (mode.value === 'remove') {
        const net = amount.value / (1 + rate.value / 100);
        return { net, vat: amount.value - net, gross: amount.value };
    }
    const vat = (amount.value * rate.value) / 100;
    return { net: amount.value, vat, gross: amount.value + vat };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="vat-amount">Amount</label>
                <input id="vat-amount" v-model.number="amount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="vat-rate">VAT rate (%)</label>
                <input id="vat-rate" v-model.number="rate" type="number" min="0" max="100" step="any" inputmode="decimal" />
                <p class="hint">UK 20% · UAE/KSA 5%/15% · EU typically 17–27% — enter any rate.</p>
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Direction">
                    <input id="vat-add" v-model="mode" type="radio" value="add" /><label for="vat-add">Add VAT</label>
                    <input id="vat-remove" v-model="mode" type="radio" value="remove" /><label for="vat-remove">Remove VAT</label>
                </span>
            </div>
            <div class="field">
                <label for="vat-currency">Currency label</label>
                <input id="vat-currency" v-model="currency" type="text" maxlength="5" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>{{ mode === 'remove' ? 'Net (before VAT)' : 'Gross (with VAT)' }}</h2>
            <p class="result-big">{{ valid ? fmt(mode === 'remove' ? result.net : result.gross) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Net amount</span><b>{{ valid ? fmt(result.net) : '—' }}</b></div>
                <div class="result-row"><span>VAT</span><b>{{ valid ? fmt(result.vat) : '—' }}</b></div>
                <div class="result-row"><span>Gross amount</span><b>{{ valid ? fmt(result.gross) : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
