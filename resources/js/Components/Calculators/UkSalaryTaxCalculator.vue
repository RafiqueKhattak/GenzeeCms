<script setup>
import { computed, ref } from 'vue';

const TAX_CONFIG = {
    personalAllowance: 12570,
    paTaperStart: 100000,
    bands: [
        { upTo: 37700, rate: 0.2 },
        { upTo: 125140, rate: 0.4 },
        { upTo: Infinity, rate: 0.45 },
    ],
    ni: { primaryThreshold: 12570, upperEarningsLimit: 50270, mainRate: 0.08, upperRate: 0.02 },
};

const salary = ref(35000);
const period = ref('annual');

function personalAllowance(income) {
    const pa = TAX_CONFIG.personalAllowance;
    if (income <= TAX_CONFIG.paTaperStart) return pa;
    return Math.max(0, pa - Math.floor((income - TAX_CONFIG.paTaperStart) / 2));
}

function incomeTax(income) {
    if (income <= 0) return 0;
    const pa = personalAllowance(income);
    const taxable = Math.max(0, income - pa);
    let tax = 0, lower = 0;
    for (const b of TAX_CONFIG.bands) {
        const upper = Math.min(taxable, b.upTo);
        if (upper > lower) tax += (upper - lower) * b.rate;
        lower = b.upTo;
        if (taxable <= b.upTo) break;
    }
    return tax;
}

function nationalInsurance(income) {
    if (income <= TAX_CONFIG.ni.primaryThreshold) return 0;
    const mainBand = Math.min(income, TAX_CONFIG.ni.upperEarningsLimit) - TAX_CONFIG.ni.primaryThreshold;
    const upperBand = Math.max(0, income - TAX_CONFIG.ni.upperEarningsLimit);
    return mainBand * TAX_CONFIG.ni.mainRate + upperBand * TAX_CONFIG.ni.upperRate;
}

function breakdown(income) {
    const pa = personalAllowance(income);
    const taxable = Math.max(0, income - pa);
    const rows = [{ label: 'Personal Allowance (0%)', amount: Math.min(income, pa), tax: 0 }];
    const names = ['Basic rate', 'Higher rate', 'Additional rate'];
    let lower = 0;
    TAX_CONFIG.bands.forEach((b, i) => {
        const upper = Math.min(taxable, b.upTo);
        if (upper > lower) {
            rows.push({ label: `${names[i]} (${(b.rate * 100).toFixed(0)}%)`, amount: upper - lower, tax: (upper - lower) * b.rate });
        }
        lower = b.upTo;
    });
    return rows;
}

function fmt(x) {
    return `£${Math.round(x).toLocaleString('en-GB')}`;
}

const annual = computed(() => (period.value === 'monthly' ? salary.value * 12 : salary.value));
const valid = computed(() => annual.value > 0);
const tax = computed(() => incomeTax(annual.value));
const ni = computed(() => nationalInsurance(annual.value));
const takeHome = computed(() => annual.value - tax.value - ni.value);
const rows = computed(() => (valid.value ? breakdown(annual.value) : []));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ukt-salary">Your salary (£)</label>
                <input id="ukt-salary" v-model.number="salary" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Salary period">
                    <input id="ukt-annual" v-model="period" type="radio" value="annual" /><label for="ukt-annual">Annual</label>
                    <input id="ukt-monthly" v-model="period" type="radio" value="monthly" /><label for="ukt-monthly">Monthly</label>
                </span>
            </div>
            <div class="notice"><span>⚠️</span><span><strong>England, Wales &amp; Northern Ireland rates only — Scotland sets its own bands.</strong> Excludes student loan repayments, pension contributions and salary sacrifice.</span></div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Take-home pay</h2>
            <p class="result-big">{{ valid ? fmt(takeHome / 12) : '—' }}</p>
            <p class="result-sub">Annual: {{ valid ? fmt(takeHome) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Income Tax (annual)</span><b>{{ valid ? fmt(tax) : '—' }}</b></div>
                <div class="result-row"><span>National Insurance (annual)</span><b>{{ valid ? fmt(ni) : '—' }}</b></div>
                <div class="result-row"><span>Effective rate</span><b>{{ valid ? `${((tax + ni) / annual * 100).toFixed(1)}%` : '—' }}</b></div>
            </div>
        </div>
    </div>
    <div v-if="valid" class="table-wrap">
        <table>
            <caption>Income Tax band-by-band breakdown</caption>
            <thead><tr><th scope="col">Band</th><th scope="col" class="num">Amount in band</th><th scope="col" class="num">Tax</th></tr></thead>
            <tbody>
                <tr v-for="(row, i) in rows" :key="i">
                    <td>{{ row.label }}</td>
                    <td class="num">{{ fmt(row.amount) }}</td>
                    <td class="num">{{ fmt(row.tax) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
