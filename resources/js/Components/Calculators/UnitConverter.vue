<script setup>
import { computed, ref, watch } from 'vue';

const UNITS = {
    length: { units: { millimetre: 0.001, centimetre: 0.01, metre: 1, kilometre: 1000, inch: 0.0254, foot: 0.3048, yard: 0.9144, mile: 1609.344 } },
    weight: { units: { gram: 0.001, kilogram: 1, tonne: 1000, ounce: 0.028349523125, pound: 0.45359237, tola: 0.0116638 } },
    area: { units: { 'square foot': 0.09290304, 'square yard': 0.83612736, 'square metre': 1, marla: 25.2929, kanal: 505.857, acre: 4046.8564224, hectare: 10000 } },
};
const TEMP_UNITS = ['celsius', 'fahrenheit', 'kelvin'];

const category = ref('length');
const value = ref(5);
const from = ref('metre');
const to = ref('kilometre');

function unitNames(cat) {
    return cat === 'temperature' ? TEMP_UNITS : Object.keys(UNITS[cat].units);
}

watch(category, (cat) => {
    const names = unitNames(cat);
    from.value = names[0];
    to.value = names[Math.min(1, names.length - 1)];
});

function convertTemp(v, f, t) {
    let celsius;
    if (f === 'celsius') celsius = v;
    else if (f === 'fahrenheit') celsius = ((v - 32) * 5) / 9;
    else celsius = v - 273.15;
    if (t === 'celsius') return celsius;
    if (t === 'fahrenheit') return (celsius * 9) / 5 + 32;
    return celsius + 273.15;
}

const result = computed(() => {
    if (isNaN(value.value) || !from.value || !to.value) return null;
    const out = category.value === 'temperature'
        ? convertTemp(value.value, from.value, to.value)
        : (value.value * UNITS[category.value].units[from.value]) / UNITS[category.value].units[to.value];
    const rounded = Math.abs(out) >= 1
        ? out.toLocaleString('en-US', { maximumFractionDigits: 6 })
        : out.toPrecision(6).replace(/\.?0+$/, '');
    return { rounded, label: `${value.value} ${from.value} =` };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="uc-category">Category</label>
                <select id="uc-category" v-model="category">
                    <option value="length">Length</option>
                    <option value="weight">Weight</option>
                    <option value="temperature">Temperature</option>
                    <option value="area">Area</option>
                </select>
            </div>
            <div class="field">
                <label for="uc-value">Value</label>
                <input id="uc-value" v-model.number="value" type="number" step="any" inputmode="decimal" />
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="uc-from">From</label>
                    <select id="uc-from" v-model="from">
                        <option v-for="u in unitNames(category)" :key="u" :value="u">{{ u.charAt(0).toUpperCase() + u.slice(1) }}</option>
                    </select>
                </div>
                <div class="field">
                    <label for="uc-to">To</label>
                    <select id="uc-to" v-model="to">
                        <option v-for="u in unitNames(category)" :key="u" :value="u">{{ u.charAt(0).toUpperCase() + u.slice(1) }}</option>
                    </select>
                </div>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Result</h2>
            <p class="result-sub">{{ result?.label }}</p>
            <p class="result-big">{{ result ? result.rounded : '—' }}</p>
            <p class="result-sub">{{ to }}</p>
        </div>
    </div>
</template>
