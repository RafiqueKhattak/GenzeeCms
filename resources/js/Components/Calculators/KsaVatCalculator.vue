<script setup>
import { computed, ref } from 'vue';

const RATE = 0.15;
const amount = ref(1000);
const mode = ref('net');

function fmt(x) {
    return `SAR ${Math.round(x).toLocaleString('en-SA')}`;
}

const valid = computed(() => amount.value > 0);
const net = computed(() => (mode.value === 'net' ? amount.value : amount.value / (1 + RATE)));
const vat = computed(() => (mode.value === 'net' ? amount.value * RATE : amount.value - amount.value / (1 + RATE)));
const gross = computed(() => (mode.value === 'net' ? amount.value * (1 + RATE) : amount.value));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ksa-amount">Amount (SAR)</label>
                <input id="ksa-amount" v-model.number="amount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Amount type">
                    <input id="ksa-net" v-model="mode" type="radio" value="net" /><label for="ksa-net">Net amount</label>
                    <input id="ksa-gross" v-model="mode" type="radio" value="gross" /><label for="ksa-gross">Gross amount</label>
                </span>
            </div>
            <div class="notice"><span>ℹ️</span><span><strong>Current rate: 15% (since 1 July 2020).</strong> Some items like food, medicine and education are exempted. Always verify exemptions with the ZAKAT and Tax Authority.</span></div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Breakdown</h2>
            <div class="result-rows">
                <div class="result-row"><span>Net amount</span><b>{{ valid ? fmt(net) : '—' }}</b></div>
                <div class="result-row"><span>VAT (15%)</span><b>{{ valid ? fmt(vat) : '—' }}</b></div>
                <div class="result-row"><span>Gross total</span><b>{{ valid ? fmt(gross) : '—' }}</b></div>
                <div class="result-row"><span>VAT rate</span><b>{{ valid ? '15.00%' : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
