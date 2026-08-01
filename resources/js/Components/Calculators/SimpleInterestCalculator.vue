<script setup>
import { computed, ref } from 'vue';

const principal = ref(100000);
const rate = ref(12);
const years = ref(3);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => principal.value > 0 && years.value > 0);
const interest = computed(() => (principal.value * rate.value / 100) * years.value);
const total = computed(() => principal.value + interest.value);
const monthly = computed(() => interest.value / (years.value * 12));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="si-principal">Principal (PKR)</label>
                <input id="si-principal" v-model.number="principal" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="si-rate">Annual rate (%)</label>
                <input id="si-rate" v-model.number="rate" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="si-years">Time (years)</label>
                <input id="si-years" v-model.number="years" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Total interest</h2>
            <p class="result-big">{{ valid ? fmt(interest) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Total amount</span><b>{{ valid ? fmt(total) : '—' }}</b></div>
                <div class="result-row"><span>Interest per month</span><b>{{ valid ? `${fmt(monthly)} / month` : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
