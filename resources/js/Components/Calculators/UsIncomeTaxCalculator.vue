<script setup>
import { computed, ref } from 'vue';

const TAX_CONFIG = {
    standardDeduction: 14600,
    brackets: [
        { upTo: 11600, rate: 0.1 },
        { upTo: 47150, rate: 0.12 },
        { upTo: 100525, rate: 0.22 },
        { upTo: 191950, rate: 0.24 },
        { upTo: 243725, rate: 0.32 },
        { upTo: 609350, rate: 0.35 },
        { upTo: Infinity, rate: 0.37 },
    ],
};

const income = ref(50000);
const period = ref('annual');

function federalIncomeTax(annualIncome) {
    if (annualIncome <= 0) return 0;
    const taxable = Math.max(0, annualIncome - TAX_CONFIG.standardDeduction);
    let tax = 0, lower = 0;
    for (const b of TAX_CONFIG.brackets) {
        const upper = Math.min(taxable, b.upTo);
        if (upper > lower) tax += (upper - lower) * b.rate;
        lower = b.upTo;
        if (taxable <= b.upTo) break;
    }
    return tax;
}

function ficaTaxes(annualIncome) {
    if (annualIncome <= 0) return 0;
    const socialSecurity = Math.min(annualIncome, 168600) * 0.062;
    const medicare = annualIncome * 0.0145;
    return socialSecurity + medicare;
}

function breakdown(annualIncome) {
    const taxable = Math.max(0, annualIncome - TAX_CONFIG.standardDeduction);
    const rows = [{ label: 'Standard Deduction', amount: Math.min(annualIncome, TAX_CONFIG.standardDeduction), tax: null }];
    let lower = 0;
    TAX_CONFIG.brackets.forEach((b) => {
        const upper = Math.min(taxable, b.upTo);
        if (upper > lower) {
            rows.push({ label: `${(b.rate * 100).toFixed(0)}% bracket`, amount: upper - lower, tax: (upper - lower) * b.rate });
        }
        lower = b.upTo;
    });
    return rows;
}

function fmt(x) {
    return `$${Math.round(x).toLocaleString('en-US')}`;
}

const annual = computed(() => (period.value === 'monthly' ? income.value * 12 : income.value));
const valid = computed(() => annual.value > 0);
const fed = computed(() => federalIncomeTax(annual.value));
const fica = computed(() => ficaTaxes(annual.value));
const totalTax = computed(() => fed.value + fica.value);
const takeHome = computed(() => annual.value - totalTax.value);
const rows = computed(() => (valid.value ? breakdown(annual.value) : []));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="us-income">Your income ($)</label>
                <input id="us-income" v-model.number="income" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Income period">
                    <input id="us-annual" v-model="period" type="radio" value="annual" /><label for="us-annual">Annual</label>
                    <input id="us-monthly" v-model="period" type="radio" value="monthly" /><label for="us-monthly">Monthly</label>
                </span>
            </div>
            <div class="notice"><span>⚠️</span><span><strong>Single filer, W-2 wages only, 2024 rates.</strong> Does not include state tax, capital gains, credits (EITC, CTC), itemized deductions, or self-employment tax. Consult IRS.gov or a tax professional for your full tax picture.</span></div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Take-home pay</h2>
            <p class="result-big">{{ valid ? fmt(takeHome) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Federal Income Tax (annual)</span><b>{{ valid ? fmt(fed) : '—' }}</b></div>
                <div class="result-row"><span>FICA Taxes (Social Security + Medicare)</span><b>{{ valid ? fmt(fica) : '—' }}</b></div>
                <div class="result-row"><span>Effective tax rate</span><b>{{ valid ? `${(totalTax / annual * 100).toFixed(2)}%` : '—' }}</b></div>
            </div>
        </div>
    </div>
    <div v-if="valid" class="table-wrap">
        <table>
            <caption>Federal tax bracket-by-bracket breakdown</caption>
            <thead><tr><th scope="col">Band</th><th scope="col" class="num">Amount in band</th><th scope="col" class="num">Tax</th></tr></thead>
            <tbody>
                <tr v-for="(row, i) in rows" :key="i">
                    <td>{{ row.label }}</td>
                    <td class="num">{{ fmt(row.amount) }}</td>
                    <td class="num">{{ row.tax === null ? '—' : fmt(row.tax) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
