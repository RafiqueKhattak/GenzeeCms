<script setup>
import { computed, ref } from 'vue';

const amount = ref(100000);
const years = ref(10);
const inflationRate = ref(8);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => amount.value > 0 && years.value >= 0);

// What today's amount will be able to buy in the future, in today's terms —
// the purchasing power erosion, not a currency conversion.
const futurePurchasingPower = computed(() => amount.value / Math.pow(1 + inflationRate.value / 100, years.value));
const lostValue = computed(() => amount.value - futurePurchasingPower.value);

// The flip side: how much money you'd need in `years` to have the same
// purchasing power that `amount` has today.
const futureEquivalent = computed(() => amount.value * Math.pow(1 + inflationRate.value / 100, years.value));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ic-amount">Amount today (PKR)</label>
                <input id="ic-amount" v-model.number="amount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ic-years">Years from now</label>
                <input id="ic-years" v-model.number="years" type="number" min="0" max="50" step="1" inputmode="numeric" />
            </div>
            <div class="field">
                <label for="ic-rate">Average annual inflation: <output>{{ inflationRate }}%</output></label>
                <input id="ic-rate" v-model.number="inflationRate" type="range" min="0" max="30" step="0.5" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Future purchasing power</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>What {{ fmt(amount) }} today will buy in {{ years }} years</h2>
                <p class="result-big">{{ fmt(futurePurchasingPower) }}</p>
                <p class="result-sub">worth of today's goods and services</p>
                <div class="result-rows">
                    <div class="result-row"><span>Value lost to inflation</span><b>{{ fmt(lostValue) }}</b></div>
                    <div class="result-row"><span>You'd need this much then, to match today</span><b>{{ fmt(futureEquivalent) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
