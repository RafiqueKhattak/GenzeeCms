<script setup>
import { computed, ref } from 'vue';

const UNITS = {
    thousand: 1e3,
    lakh: 1e5,
    million: 1e6,
    crore: 1e7,
    arab: 1e9,
    billion: 1e9,
    kharab: 1e11,
    trillion: 1e12,
};
const LABELS = [
    ['thousand', 'Thousand'],
    ['lakh', 'Lakh'],
    ['million', 'Million'],
    ['crore', 'Crore'],
    ['arab', 'Arab'],
    ['billion', 'Billion'],
    ['kharab', 'Kharab'],
    ['trillion', 'Trillion'],
];

const value = ref(2.5);
const unit = ref('crore');

function fmt(x) {
    if (x === 0) return '0';
    if (Math.abs(x) >= 1 && Number.isInteger(x)) return x.toLocaleString('en-US');
    if (Math.abs(x) >= 1000) return x.toLocaleString('en-US', { maximumFractionDigits: 2 });
    return (Math.round(x * 1000000) / 1000000).toLocaleString('en-US', { maximumFractionDigits: 6 });
}

const results = computed(() => {
    if (isNaN(value.value)) return null;
    const plain = value.value * UNITS[unit.value];
    const out = { plain };
    for (const u in UNITS) out[u] = plain / UNITS[u];
    return out;
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="lc-value">Value</label>
                <input id="lc-value" v-model.number="value" type="number" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="lc-unit">Unit</label>
                <select id="lc-unit" v-model="unit">
                    <option v-for="[key, label] in LABELS" :key="key" :value="key">{{ label }}</option>
                </select>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>In figures</h2>
            <p class="result-big" style="font-size: 1.6rem">{{ results ? fmt(results.plain) : '—' }}</p>
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
</template>
