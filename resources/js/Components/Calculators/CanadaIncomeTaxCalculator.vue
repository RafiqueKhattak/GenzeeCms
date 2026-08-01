<script setup>
import { computed, ref } from 'vue';

const BRACKETS = [
    { upTo: 58523, rate: 0.14 },
    { upTo: 117045, rate: 0.205 },
    { upTo: 181440, rate: 0.26 },
    { upTo: 258482, rate: 0.29 },
    { upTo: Infinity, rate: 0.33 },
];
const BASIC_PERSONAL_AMOUNT = 16452;
const LOWEST_RATE = 0.14;
const CPP_EXEMPTION = 3500;
const CPP_YMPE = 74600;
const CPP_RATE = 0.0595;
const CPP2_MAX = 85000;
const CPP2_RATE = 0.04;
const EI_MAX_INSURABLE = 68900;
const EI_RATE = 0.0163;

const income = ref(80000);
const period = ref('annual');

function taxOnBrackets(inc) {
    let tax = 0, lower = 0;
    for (const b of BRACKETS) {
        if (inc <= lower) break;
        tax += (Math.min(inc, b.upTo) - lower) * b.rate;
        lower = b.upTo;
        if (inc <= b.upTo) break;
    }
    return tax;
}

function federalIncomeTax(inc) {
    if (inc <= 0) return 0;
    return Math.max(0, taxOnBrackets(inc) - BASIC_PERSONAL_AMOUNT * LOWEST_RATE);
}

function cppContribution(inc) {
    if (inc <= CPP_EXEMPTION) return 0;
    const base = Math.max(0, Math.min(inc, CPP_YMPE) - CPP_EXEMPTION) * CPP_RATE;
    const enhanced = inc > CPP_YMPE ? (Math.min(inc, CPP2_MAX) - CPP_YMPE) * CPP2_RATE : 0;
    return base + enhanced;
}

function eiContribution(inc) {
    return Math.min(inc, EI_MAX_INSURABLE) * EI_RATE;
}

function breakdown(inc) {
    const rows = [];
    let lower = 0;
    for (const b of BRACKETS) {
        if (inc <= lower) break;
        const amount = Math.min(inc, b.upTo) - lower;
        rows.push({ from: lower, to: Math.min(inc, b.upTo), rate: b.rate, tax: amount * b.rate });
        lower = b.upTo;
        if (inc <= b.upTo) break;
    }
    return rows;
}

function fmt(x) {
    return `C$${Math.round(x).toLocaleString('en-CA')}`;
}

const annual = computed(() => (period.value === 'monthly' ? income.value * 12 : income.value));
const div = computed(() => (period.value === 'monthly' ? 12 : 1));
const suffix = computed(() => (period.value === 'monthly' ? '/month' : '/year'));
const tax = computed(() => federalIncomeTax(annual.value));
const cpp = computed(() => cppContribution(annual.value));
const ei = computed(() => eiContribution(annual.value));
const takeHome = computed(() => annual.value - tax.value - cpp.value - ei.value);
const effRate = computed(() => (annual.value > 0 ? (tax.value / annual.value) * 100 : 0));
const rows = computed(() => breakdown(annual.value));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ca-income">Income</label>
                <input id="ca-income" v-model.number="income" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Income period">
                    <input id="ca-annual" v-model="period" type="radio" value="annual" /><label for="ca-annual">Annual</label>
                    <input id="ca-monthly" v-model="period" type="radio" value="monthly" /><label for="ca-monthly">Monthly</label>
                </span>
            </div>
            <div class="notice notice-info"><span>ℹ️</span><span><strong>Federal tax only.</strong> Add your province's own income tax to get your real total — this calculator does not include it.</span></div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Take-home pay</h2>
            <p class="result-big">{{ fmt(takeHome / div) }}{{ suffix }}</p>
            <p class="result-sub">after federal tax, CPP/CPP2 and EI</p>
            <div class="result-rows">
                <div class="result-row"><span>Federal income tax</span><b>{{ fmt(tax / div) }}{{ suffix }}</b></div>
                <div class="result-row"><span>CPP / CPP2</span><b>{{ fmt(cpp / div) }}{{ suffix }}</b></div>
                <div class="result-row"><span>EI</span><b>{{ fmt(ei / div) }}{{ suffix }}</b></div>
                <div class="result-row"><span>Effective federal rate</span><b>{{ effRate.toFixed(1) }}%</b></div>
            </div>
        </div>
    </div>
    <details class="fold no-print">
        <summary>Show federal bracket breakdown</summary>
        <div class="fold-body">
            <div class="table-wrap">
                <table>
                    <thead><tr><th scope="col">Band (annual)</th><th scope="col" class="num">Rate</th><th scope="col" class="num">Tax in band</th></tr></thead>
                    <tbody>
                        <tr v-for="(row, i) in rows" :key="i">
                            <td>{{ fmt(row.from) }} – {{ fmt(row.to) }}</td>
                            <td class="num">{{ (row.rate * 100).toFixed(1) }}%</td>
                            <td class="num">{{ fmt(row.tax) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </details>
</template>
