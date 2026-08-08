<script setup>
import { computed, ref } from 'vue';

const FREQUENCIES = { annually: 1, semiAnnually: 2, quarterly: 4, monthly: 12, daily: 365 };

const nominalRate = ref(12);
const frequency = ref('monthly');

const valid = computed(() => nominalRate.value >= 0);

const effectiveRate = computed(() => {
    const n = FREQUENCIES[frequency.value] || 1;
    const r = nominalRate.value / 100 / n;
    return (Math.pow(1 + r, n) - 1) * 100;
});

const difference = computed(() => effectiveRate.value - nominalRate.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ear-rate">Nominal (stated) annual rate (%)</label>
                <input id="ear-rate" v-model.number="nominalRate" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ear-freq">Compounding frequency</label>
                <select id="ear-freq" v-model="frequency">
                    <option value="annually">Annually</option>
                    <option value="semiAnnually">Semi-annually</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="monthly">Monthly</option>
                    <option value="daily">Daily</option>
                </select>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Effective annual rate</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Effective annual rate</h2>
                <p class="result-big">{{ effectiveRate.toFixed(3) }}%</p>
                <p class="result-sub">vs. the stated {{ nominalRate }}% nominal rate</p>
                <div class="result-rows">
                    <div class="result-row"><span>Difference from nominal</span><b>+{{ difference.toFixed(3) }} points</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
