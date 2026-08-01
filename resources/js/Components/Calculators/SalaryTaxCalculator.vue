<script setup>
import { computed, ref } from 'vue';

const TAX_CONFIG = {
    slabs: [
        { upTo: 600000, base: 0, rate: 0.0, over: 0 },
        { upTo: 1200000, base: 0, rate: 0.01, over: 600000 },
        { upTo: 2200000, base: 6000, rate: 0.11, over: 1200000 },
        { upTo: 3200000, base: 116000, rate: 0.2, over: 2200000 },
        { upTo: 4100000, base: 316000, rate: 0.25, over: 3200000 },
        { upTo: 5600000, base: 541000, rate: 0.29, over: 4100000 },
        { upTo: 7000000, base: 976000, rate: 0.32, over: 5600000 },
        { upTo: Infinity, base: 1424000, rate: 0.35, over: 7000000 },
    ],
};

const salary = ref(100000);
const period = ref('monthly');

function annualTax(income) {
    if (income <= 0) return 0;
    for (const s of TAX_CONFIG.slabs) {
        if (income <= s.upTo) return s.base + (income - s.over) * s.rate;
    }
    return 0;
}

function breakdown(income) {
    const rows = [];
    for (const s of TAX_CONFIG.slabs) {
        const lower = s.over;
        const upper = Math.min(income, s.upTo);
        if (income <= lower) break;
        const amount = upper - lower;
        rows.push({ from: lower, to: s.upTo, rate: s.rate, amount, tax: amount * s.rate });
    }
    return rows;
}

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

function rangeLabel(row) {
    return row.to === Infinity ? `Above ${fmt(row.from)}` : `${fmt(row.from + (row.from ? 1 : 0))} – ${fmt(row.to)}`;
}

const annual = computed(() => (period.value === 'monthly' ? salary.value * 12 : salary.value));
const valid = computed(() => annual.value > 0);
const tax = computed(() => annualTax(annual.value));
const rows = computed(() => (valid.value ? breakdown(annual.value) : []));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="tax-salary">Your salary (PKR)</label>
                <input id="tax-salary" v-model.number="salary" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Salary period">
                    <input id="tax-monthly" v-model="period" type="radio" value="monthly" /><label for="tax-monthly">Monthly</label>
                    <input id="tax-annual" v-model="period" type="radio" value="annual" /><label for="tax-annual">Annual</label>
                </span>
            </div>
            <div class="notice"><span>⚠️</span><span><strong>Based on Finance Act 2026 (FY 2026-27) rates for salaried individuals. Verify with FBR for your specific case.</strong> The surcharge on salaried individuals has been abolished.</span></div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Monthly income tax</h2>
            <p class="result-big">{{ valid ? fmt(tax / 12) : '—' }}</p>
            <p class="result-sub">Take-home: {{ valid ? `${fmt((annual - tax) / 12)} / month` : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Annual tax</span><b>{{ valid ? fmt(tax) : '—' }}</b></div>
                <div class="result-row"><span>Effective rate</span><b>{{ valid ? `${(tax / annual * 100).toFixed(2)}%` : '—' }}</b></div>
            </div>
        </div>
    </div>
    <div v-if="valid" class="table-wrap">
        <table>
            <caption>Slab-by-slab breakdown of your annual tax</caption>
            <thead><tr><th scope="col">Income slab (annual)</th><th scope="col" class="num">Rate</th><th scope="col" class="num">Amount in slab</th><th scope="col" class="num">Tax</th></tr></thead>
            <tbody>
                <tr v-for="(row, i) in rows" :key="i">
                    <td>{{ rangeLabel(row) }}</td>
                    <td class="num">{{ (row.rate * 100).toFixed(0) }}%</td>
                    <td class="num">{{ fmt(row.amount) }}</td>
                    <td class="num">{{ fmt(row.tax) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
