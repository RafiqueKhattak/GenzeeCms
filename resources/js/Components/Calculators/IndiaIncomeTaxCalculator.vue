<script setup>
import { computed, ref } from 'vue';

const TAX_CONFIG = {
    standardDeduction: 75000,
    slabs: [
        { upTo: 300000, rate: 0.0 },
        { upTo: 700000, rate: 0.05 },
        { upTo: 1000000, rate: 0.1 },
        { upTo: 1200000, rate: 0.15 },
        { upTo: 1500000, rate: 0.2 },
        { upTo: Infinity, rate: 0.3 },
    ],
    cessTaxableIncome: 1000000,
    cessRate: 0.04,
};

const income = ref(500000);
const period = ref('annual');

function incomeTax(annualIncome) {
    if (annualIncome <= 0) return 0;
    const taxable = Math.max(0, annualIncome - TAX_CONFIG.standardDeduction);
    let tax = 0, lower = 0;
    for (const s of TAX_CONFIG.slabs) {
        const upper = Math.min(taxable, s.upTo);
        if (upper > lower) tax += (upper - lower) * s.rate;
        lower = s.upTo;
        if (taxable <= s.upTo) break;
    }
    return tax;
}

function cess(taxable) {
    return taxable <= TAX_CONFIG.cessTaxableIncome ? 0 : (taxable - TAX_CONFIG.cessTaxableIncome) * TAX_CONFIG.cessRate;
}

function breakdown(annualIncome) {
    const taxable = Math.max(0, annualIncome - TAX_CONFIG.standardDeduction);
    const rows = [{ label: 'Standard Deduction', amount: Math.min(annualIncome, TAX_CONFIG.standardDeduction), tax: null }];
    let lower = 0;
    TAX_CONFIG.slabs.forEach((s, i) => {
        const upper = Math.min(taxable, s.upTo);
        if (upper > lower) {
            rows.push({ label: `Slab ${i + 1} @ ${(s.rate * 100).toFixed(0)}%`, amount: upper - lower, tax: (upper - lower) * s.rate });
        }
        lower = s.upTo;
    });
    return rows;
}

function fmt(x) {
    return `₹${Math.round(x).toLocaleString('en-IN')}`;
}

const annual = computed(() => (period.value === 'monthly' ? income.value * 12 : income.value));
const valid = computed(() => annual.value > 0);
const taxable = computed(() => Math.max(0, annual.value - TAX_CONFIG.standardDeduction));
const tax = computed(() => incomeTax(annual.value));
const cessAmount = computed(() => cess(taxable.value));
const totalTax = computed(() => tax.value + cessAmount.value);
const takeHome = computed(() => annual.value - totalTax.value);
const rows = computed(() => (valid.value ? breakdown(annual.value) : []));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="india-income">Your income (₹)</label>
                <input id="india-income" v-model.number="income" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Income period">
                    <input id="india-annual" v-model="period" type="radio" value="annual" /><label for="india-annual">Annual</label>
                    <input id="india-monthly" v-model="period" type="radio" value="monthly" /><label for="india-monthly">Monthly</label>
                </span>
            </div>
            <div class="notice"><span>⚠️</span><span><strong>Individuals under 60 years only — new regime.</strong> Does not include surcharge (applies above ₹50 lakh), Section 80C/80D deductions, pension contributions, HRA or other allowances. Always verify with the official income tax calculator or your chartered accountant.</span></div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Take-home pay</h2>
            <p class="result-big">{{ valid ? fmt(takeHome) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Income Tax (annual)</span><b>{{ valid ? fmt(tax) : '—' }}</b></div>
                <div class="result-row"><span>Health &amp; Education Cess</span><b>{{ valid ? fmt(cessAmount) : '—' }}</b></div>
                <div class="result-row"><span>Effective rate</span><b>{{ valid ? `${(totalTax / annual * 100).toFixed(2)}%` : '—' }}</b></div>
            </div>
        </div>
    </div>
    <div v-if="valid" class="table-wrap">
        <table>
            <caption>Income tax slab-by-slab breakdown</caption>
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
