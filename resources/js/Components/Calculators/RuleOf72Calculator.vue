<script setup>
import { computed, ref } from 'vue';

const annualReturn = ref(12);
const startingAmount = ref(100000);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => annualReturn.value > 0);
const yearsToDouble = computed(() => (valid.value ? 72 / annualReturn.value : 0));
// The exact (not approximated) doubling time, for comparison against the
// Rule of 72's quick mental-math estimate.
const exactYearsToDouble = computed(() => (valid.value ? Math.log(2) / Math.log(1 + annualReturn.value / 100) : 0));
const doubledAmount = computed(() => startingAmount.value * 2);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="r72-amount">Starting amount (PKR)</label>
                <input id="r72-amount" v-model.number="startingAmount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="r72-return">Expected annual return (%)</label>
                <input id="r72-return" v-model.number="annualReturn" type="number" min="0.1" max="50" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Years to double</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Years to double your money</h2>
                <p class="result-big">~{{ yearsToDouble.toFixed(1) }} years</p>
                <p class="result-sub">{{ fmt(startingAmount) }} grows to {{ fmt(doubledAmount) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Rule of 72 estimate</span><b>{{ yearsToDouble.toFixed(2) }} years</b></div>
                    <div class="result-row"><span>Exact compound growth answer</span><b>{{ exactYearsToDouble.toFixed(2) }} years</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
