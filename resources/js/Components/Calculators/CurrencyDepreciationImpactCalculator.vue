<script setup>
import { computed, ref } from 'vue';

const foreignAmount = ref(1000);
const oldRate = ref(250);
const newRate = ref(280);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => foreignAmount.value > 0 && oldRate.value > 0 && newRate.value > 0);
const oldCost = computed(() => foreignAmount.value * oldRate.value);
const newCost = computed(() => foreignAmount.value * newRate.value);
const extraCost = computed(() => newCost.value - oldCost.value);
const pctChange = computed(() => (oldRate.value > 0 ? ((newRate.value - oldRate.value) / oldRate.value) * 100 : 0));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="cd-amount">Amount in foreign currency (e.g. USD)</label>
                <input id="cd-amount" v-model.number="foreignAmount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cd-old">Old exchange rate (PKR per unit)</label>
                <input id="cd-old" v-model.number="oldRate" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cd-new">New exchange rate (PKR per unit)</label>
                <input id="cd-new" v-model.number="newRate" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Extra cost from the rate change</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Extra cost from the rate change</h2>
                <p class="result-big" :style="{ color: extraCost > 0 ? '#dc2626' : '#059669' }">{{ extraCost >= 0 ? '+' : '' }}{{ fmt(extraCost) }}</p>
                <p class="result-sub">The rupee moved {{ pctChange >= 0 ? 'up' : 'down' }} {{ Math.abs(pctChange).toFixed(2) }}% against this currency</p>
                <div class="result-rows">
                    <div class="result-row"><span>Cost at old rate</span><b>{{ fmt(oldCost) }}</b></div>
                    <div class="result-row"><span>Cost at new rate</span><b>{{ fmt(newCost) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
