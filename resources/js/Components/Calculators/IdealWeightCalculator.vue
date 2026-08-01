<script setup>
import { computed, ref } from 'vue';

const cm = ref(175);
const sex = ref('male');

function kg(x) {
    return `${x.toFixed(1)} kg`;
}

const valid = computed(() => cm.value >= 100);
const result = computed(() => {
    const male = sex.value === 'male';
    const inches = cm.value / 2.54;
    const over = Math.max(0, inches - 60);
    const m = cm.value / 100;
    return {
        devine: male ? 50 + 2.3 * over : 45.5 + 2.3 * over,
        robinson: male ? 52 + 1.9 * over : 49 + 1.7 * over,
        hamwi: male ? 48 + 2.7 * over : 45.5 + 2.2 * over,
        bmiMin: 18.5 * m * m,
        bmiMax: 24.9 * m * m,
    };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="iw-cm">Height (cm)</label>
                <input id="iw-cm" v-model.number="cm" type="number" min="100" max="250" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Sex">
                    <input id="iw-male" v-model="sex" type="radio" value="male" /><label for="iw-male">Male</label>
                    <input id="iw-female" v-model="sex" type="radio" value="female" /><label for="iw-female">Female</label>
                </span>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Healthy BMI range</h2>
            <p class="result-big">{{ valid ? `${result.bmiMin.toFixed(1)} – ${result.bmiMax.toFixed(1)} kg` : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Devine formula</span><b>{{ valid ? kg(result.devine) : '—' }}</b></div>
                <div class="result-row"><span>Robinson formula</span><b>{{ valid ? kg(result.robinson) : '—' }}</b></div>
                <div class="result-row"><span>Hamwi formula</span><b>{{ valid ? kg(result.hamwi) : '—' }}</b></div>
            </div>
        </div>
    </div>
    <div class="notice notice-info"><span>ℹ️</span><span>"Ideal" weight formulas are clinical estimation tools, not targets. A healthy weight for you depends on build, muscle and medical history — discuss goals with a doctor.</span></div>
</template>
