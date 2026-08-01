<script setup>
import { computed, ref } from 'vue';

const KG_PER_LB = 0.45359237;

const units = ref('metric');
const kgInput = ref(70);
const cmInput = ref(175);
const lb = ref(154);
const ft = ref(5);
const inch = ref(9);

function category(x) {
    if (isNaN(x)) return '';
    if (x < 18.5) return 'Underweight';
    if (x < 25) return 'Normal weight';
    if (x < 30) return 'Overweight';
    return 'Obese';
}

const kg = computed(() => (units.value === 'metric' ? kgInput.value || 0 : (lb.value || 0) * KG_PER_LB));
const meters = computed(() => {
    if (units.value === 'metric') return (cmInput.value || 0) / 100;
    const inches = (ft.value || 0) * 12 + (inch.value || 0);
    return inches * 0.0254;
});
const bmiValue = computed(() => (kg.value > 0 && meters.value > 0 ? kg.value / (meters.value * meters.value) : NaN));
const valid = computed(() => !isNaN(bmiValue.value) && isFinite(bmiValue.value));
const range = computed(() => ({ min: 18.5 * meters.value * meters.value, max: 24.9 * meters.value * meters.value }));
const rangeText = computed(() => {
    if (!valid.value) return '—';
    return units.value === 'metric'
        ? `${range.value.min.toFixed(1)} – ${range.value.max.toFixed(1)} kg`
        : `${(range.value.min / KG_PER_LB).toFixed(0)} – ${(range.value.max / KG_PER_LB).toFixed(0)} lb`;
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <span class="segmented" role="group" aria-label="Unit system">
                    <input id="bmi-metric" v-model="units" type="radio" value="metric" /><label for="bmi-metric">Metric (kg, cm)</label>
                    <input id="bmi-imperial" v-model="units" type="radio" value="imperial" /><label for="bmi-imperial">Imperial (lb, ft)</label>
                </span>
            </div>
            <div v-show="units === 'metric'">
                <div class="field-row">
                    <div class="field"><label for="bmi-kg">Weight (kg)</label><input id="bmi-kg" v-model.number="kgInput" type="number" min="0" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="bmi-cm">Height (cm)</label><input id="bmi-cm" v-model.number="cmInput" type="number" min="0" step="any" inputmode="decimal" /></div>
                </div>
            </div>
            <div v-show="units === 'imperial'">
                <div class="field-row">
                    <div class="field"><label for="bmi-lb">Weight (lb)</label><input id="bmi-lb" v-model.number="lb" type="number" min="0" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="bmi-ft">Height (ft)</label><input id="bmi-ft" v-model.number="ft" type="number" min="0" step="1" inputmode="numeric" /></div>
                    <div class="field"><label for="bmi-in">+ inches</label><input id="bmi-in" v-model.number="inch" type="number" min="0" max="11" step="1" inputmode="numeric" /></div>
                </div>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Your BMI</h2>
            <p class="result-big">{{ valid ? bmiValue.toFixed(1) : '—' }}</p>
            <p class="result-sub">{{ valid ? category(bmiValue) : '' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Healthy weight range</span><b>{{ rangeText }}</b></div>
            </div>
        </div>
    </div>
    <div class="notice notice-info"><span>ℹ️</span><span>BMI is a screening measure, not a diagnosis. It cannot distinguish muscle from fat — talk to a doctor before drawing conclusions about your health.</span></div>
</template>
