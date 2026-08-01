<script setup>
import { computed, ref } from 'vue';

const ACTIVITY = { sedentary: 1.2, light: 1.375, moderate: 1.55, active: 1.725, veryActive: 1.9 };

const kg = ref(70);
const cm = ref(175);
const age = ref(30);
const sex = ref('male');
const activity = ref('light');

function kcal(x) {
    return `${Math.round(x).toLocaleString('en-US')} kcal`;
}

const valid = computed(() => kg.value > 0 && cm.value > 0 && age.value > 0);
const bmr = computed(() => 10 * kg.value + 6.25 * cm.value - 5 * age.value + (sex.value === 'male' ? 5 : -161));
const tdee = computed(() => bmr.value * (ACTIVITY[activity.value] || 1.2));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field-row">
                <div class="field"><label for="cal-kg">Weight (kg)</label><input id="cal-kg" v-model.number="kg" type="number" min="0" step="any" inputmode="decimal" /></div>
                <div class="field"><label for="cal-cm">Height (cm)</label><input id="cal-cm" v-model.number="cm" type="number" min="0" step="any" inputmode="decimal" /></div>
                <div class="field"><label for="cal-age">Age (years)</label><input id="cal-age" v-model.number="age" type="number" min="10" max="100" step="1" inputmode="numeric" /></div>
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Sex">
                    <input id="cal-male" v-model="sex" type="radio" value="male" /><label for="cal-male">Male</label>
                    <input id="cal-female" v-model="sex" type="radio" value="female" /><label for="cal-female">Female</label>
                </span>
            </div>
            <div class="field">
                <label for="cal-activity">Activity level</label>
                <select id="cal-activity" v-model="activity">
                    <option value="sedentary">Sedentary (desk job, little exercise)</option>
                    <option value="light">Light (exercise 1–3 days/week)</option>
                    <option value="moderate">Moderate (exercise 3–5 days/week)</option>
                    <option value="active">Active (exercise 6–7 days/week)</option>
                    <option value="veryActive">Very active (physical job + training)</option>
                </select>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Maintenance calories (TDEE)</h2>
            <p class="result-big">{{ valid ? kcal(tdee) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>BMR (at complete rest)</span><b>{{ valid ? kcal(bmr) : '—' }}</b></div>
                <div class="result-row"><span>To lose ~0.5 kg/week</span><b>{{ valid ? kcal(Math.max(1200, tdee - 500)) : '—' }}</b></div>
                <div class="result-row"><span>To gain ~0.5 kg/week</span><b>{{ valid ? kcal(tdee + 500) : '—' }}</b></div>
            </div>
        </div>
    </div>
    <div class="notice notice-info"><span>ℹ️</span><span>These are population-average estimates, not medical advice. Individual needs vary — consult a doctor or dietitian before major diet changes.</span></div>
</template>
