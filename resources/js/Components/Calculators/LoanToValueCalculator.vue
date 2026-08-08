<script setup>
import { computed, ref } from 'vue';

const propertyValue = ref(15000000);
const loanAmount = ref(12000000);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => propertyValue.value > 0);
const ltv = computed(() => (valid.value ? (loanAmount.value / propertyValue.value) * 100 : 0));
const downPayment = computed(() => Math.max(0, propertyValue.value - loanAmount.value));
const downPaymentPct = computed(() => (valid.value ? (downPayment.value / propertyValue.value) * 100 : 0));

const riskBand = computed(() => {
    if (ltv.value <= 80) return { label: 'Typically favourable terms', tone: 'good' };
    if (ltv.value <= 90) return { label: 'May need mortgage insurance or a higher rate', tone: 'warn' };
    return { label: 'High LTV — expect stricter terms or lender refusal', tone: 'bad' };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ltv-value">Property value (PKR)</label>
                <input id="ltv-value" v-model.number="propertyValue" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ltv-loan">Loan amount (PKR)</label>
                <input id="ltv-loan" v-model.number="loanAmount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Loan-to-value ratio</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Loan-to-value ratio</h2>
                <p class="result-big">{{ ltv.toFixed(1) }}%</p>
                <p class="result-sub">{{ riskBand.label }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Down payment</span><b>{{ fmt(downPayment) }} ({{ downPaymentPct.toFixed(1) }}%)</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
