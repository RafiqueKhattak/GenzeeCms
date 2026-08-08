<script setup>
import { computed, ref } from 'vue';

const lastDrawnSalary = ref(80000);
const yearsOfService = ref(7);
const monthsPerYear = ref(1);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => lastDrawnSalary.value >= 0 && yearsOfService.value >= 0);

// Deliberately configurable rather than hard-coded to one country's formula
// — end-of-service gratuity rules vary by country and even by employer
// policy (e.g. "1 month's salary per year" is common in Pakistan's private
// sector; other schemes use a 15/26-day fraction per year instead).
const gratuity = computed(() => lastDrawnSalary.value * monthsPerYear.value * yearsOfService.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="gr-salary">Last drawn monthly salary (PKR)</label>
                <input id="gr-salary" v-model.number="lastDrawnSalary" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="gr-years">Years of service (completed)</label>
                <input id="gr-years" v-model.number="yearsOfService" type="number" min="0" max="50" step="0.5" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="gr-rate">Gratuity rate: <output>{{ monthsPerYear }} month(s) of salary per year</output></label>
                <input id="gr-rate" v-model.number="monthsPerYear" type="range" min="0.25" max="2" step="0.25" />
                <p class="hint">1 month per year of service is common under many private-sector schemes in Pakistan — check your specific employment contract or company policy, since this varies.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Estimated gratuity</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Estimated gratuity</h2>
                <p class="result-big">{{ fmt(gratuity) }}</p>
                <p class="result-sub">Based on {{ yearsOfService }} years at {{ fmt(lastDrawnSalary) }}/month, {{ monthsPerYear }} month(s) per year</p>
            </template>
        </div>
    </div>
</template>
