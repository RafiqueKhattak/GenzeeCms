<script setup>
import { computed, ref } from 'vue';

const UNITS = {
    marla: 25.29285264,
    kanal: 505.8570528,
    'square foot': 0.09290304,
    'square yard': 0.83612736,
    'square metre': 1,
    acre: 4046.8564224,
    hectare: 10000,
};
const LABELS = [
    ['marla', 'Marla'],
    ['kanal', 'Kanal'],
    ['square foot', 'Square feet'],
    ['square yard', 'Square yard (gaz)'],
    ['square metre', 'Square metre'],
    ['acre', 'Acre'],
    ['hectare', 'Hectare'],
];

const value = ref(5);
const from = ref('marla');

function fmt(x) {
    if (x === 0) return '0';
    if (Math.abs(x) >= 1000) return x.toLocaleString('en-US', { maximumFractionDigits: 2 });
    return (Math.round(x * 10000) / 10000).toLocaleString('en-US', { maximumFractionDigits: 4 });
}

const results = computed(() => {
    if (isNaN(value.value)) return null;
    const sqm = value.value * UNITS[from.value];
    const out = {};
    for (const u in UNITS) out[u] = sqm / UNITS[u];
    return out;
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="la-value">Value</label>
                <input id="la-value" v-model.number="value" type="number" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="la-from">Unit</label>
                <select id="la-from" v-model="from">
                    <option v-for="[key, label] in LABELS" :key="key" :value="key">{{ label }}</option>
                </select>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Equivalent area</h2>
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
        </div>
    </div>
    <div class="notice"><span>⚠️</span><span>This tool uses the common <strong>272.25 sq ft = 1 marla</strong> convention. Some areas (parts of KP and older settlements) use a 225 sq ft marla — always confirm the square-foot area on the registry documents before any transaction.</span></div>
</template>
