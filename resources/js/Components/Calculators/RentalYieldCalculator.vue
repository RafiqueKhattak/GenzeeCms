<script setup>
import { computed, ref } from 'vue';

const propertyPrice = ref(15000000);
const monthlyRent = ref(75000);
const annualExpenses = ref(60000);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

function pct(x) {
    return `${x.toFixed(2)}%`;
}

const valid = computed(() => propertyPrice.value > 0);
const annualRent = computed(() => monthlyRent.value * 12);
const grossYield = computed(() => (annualRent.value / propertyPrice.value) * 100);
const netYield = computed(() => ((annualRent.value - annualExpenses.value) / propertyPrice.value) * 100);
const monthlyCashFlow = computed(() => monthlyRent.value - annualExpenses.value / 12);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ry-price">Property price (PKR)</label>
                <input id="ry-price" v-model.number="propertyPrice" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ry-rent">Monthly rental income (PKR)</label>
                <input id="ry-rent" v-model.number="monthlyRent" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ry-expenses">Annual expenses — maintenance, tax, insurance (PKR)</label>
                <input id="ry-expenses" v-model.number="annualExpenses" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Rental yield</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Net rental yield</h2>
                <p class="result-big">{{ pct(netYield) }}</p>
                <p class="result-sub">After expenses, per year on the property's price</p>
                <div class="result-rows">
                    <div class="result-row"><span>Gross yield (before expenses)</span><b>{{ pct(grossYield) }}</b></div>
                    <div class="result-row"><span>Monthly cash flow</span><b>{{ fmt(monthlyCashFlow) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
