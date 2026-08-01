<script setup>
import { computed, ref } from 'vue';

const mode = ref('hourly');
const amount = ref(1500);
const hours = ref(40);

function fmt(x) {
    return isNaN(x) || !isFinite(x) ? '—' : `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => amount.value > 0 && hours.value > 0);
const result = computed(() => {
    if (mode.value === 'hourly') {
        const weekly = amount.value * hours.value;
        const yearly = weekly * 52;
        return { hourly: amount.value, weekly, monthly: yearly / 12, yearly };
    }
    const yearly = amount.value * 12;
    const hourly = hours.value > 0 ? yearly / 52 / hours.value : NaN;
    return { hourly, weekly: yearly / 52, monthly: amount.value, yearly };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <span class="segmented" role="group" aria-label="Direction">
                    <input id="sc-from-hourly" v-model="mode" type="radio" value="hourly" /><label for="sc-from-hourly">I know my hourly rate</label>
                    <input id="sc-from-monthly" v-model="mode" type="radio" value="monthly" /><label for="sc-from-monthly">I know my monthly salary</label>
                </span>
            </div>
            <div class="field">
                <label for="sc-amount">Amount (PKR)</label>
                <input id="sc-amount" v-model.number="amount" type="number" min="0" step="any" inputmode="decimal" />
                <p class="hint">Hourly rate or monthly salary, per the toggle above.</p>
            </div>
            <div class="field">
                <label for="sc-hours">Working hours per week</label>
                <input id="sc-hours" v-model.number="hours" type="number" min="1" max="100" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Equivalent pay</h2>
            <div class="result-rows" style="margin-top: 0">
                <div class="result-row"><span>Hourly</span><b>{{ valid ? fmt(result.hourly) : '—' }}</b></div>
                <div class="result-row"><span>Weekly</span><b>{{ valid ? fmt(result.weekly) : '—' }}</b></div>
                <div class="result-row"><span>Monthly</span><b>{{ valid ? fmt(result.monthly) : '—' }}</b></div>
                <div class="result-row"><span>Yearly</span><b>{{ valid ? fmt(result.yearly) : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
