<script setup>
import { computed, ref } from 'vue';

const UNITS = {
    tola: 11.6638,
    gram: 1,
    'troy ounce': 31.1034768,
    masha: 11.6638 / 12,
    ratti: 11.6638 / 12 / 8,
    kilogram: 1000,
};
const LABELS = [
    ['tola', 'Tola'],
    ['gram', 'Gram'],
    ['troy ounce', 'Troy ounce'],
    ['masha', 'Masha'],
    ['ratti', 'Ratti'],
    ['kilogram', 'Kilogram'],
];

const value = ref(1);
const from = ref('tola');
const rate = ref(null);

function fmt(x) {
    if (x === 0) return '0';
    if (Math.abs(x) >= 1000) return x.toLocaleString('en-US', { maximumFractionDigits: 3 });
    return (Math.round(x * 100000) / 100000).toLocaleString('en-US', { maximumFractionDigits: 5 });
}

const results = computed(() => {
    if (isNaN(value.value)) return null;
    const grams = value.value * UNITS[from.value];
    const out = {};
    for (const u in UNITS) out[u] = grams / UNITS[u];
    return out;
});

const worth = computed(() => {
    if (!results.value || !rate.value || rate.value <= 0) return '—';
    return `PKR ${Math.round(results.value.gram * rate.value).toLocaleString('en-PK')}`;
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="gw-value">Weight</label>
                <input id="gw-value" v-model.number="value" type="number" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="gw-from">Unit</label>
                <select id="gw-from" v-model="from">
                    <option v-for="[key, label] in LABELS" :key="key" :value="key">{{ label }}</option>
                </select>
            </div>
            <div class="field">
                <label for="gw-rate">Gold rate per gram (PKR, optional)</label>
                <input id="gw-rate" v-model.number="rate" type="number" step="any" inputmode="decimal" placeholder="e.g. 24000" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Equivalent weight</h2>
            <div class="table-wrap" style="background: transparent; border: none">
                <table>
                    <tbody>
                        <tr v-for="[key, label] in LABELS" :key="key">
                            <th scope="row">{{ label }}</th>
                            <td class="num">{{ results ? fmt(results[key]) : '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="result-rows"><div class="result-row"><span>Worth at your rate</span><b>{{ worth }}</b></div></div>
        </div>
    </div>
</template>
