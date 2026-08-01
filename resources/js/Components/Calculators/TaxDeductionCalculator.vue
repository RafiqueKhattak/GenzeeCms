<script setup>
import { computed, ref } from 'vue';

const SLABS = [
    { upTo: 600000, base: 0, rate: 0.0, over: 0 },
    { upTo: 1200000, base: 0, rate: 0.01, over: 600000 },
    { upTo: 2200000, base: 6000, rate: 0.11, over: 1200000 },
    { upTo: 3200000, base: 116000, rate: 0.2, over: 2200000 },
    { upTo: 4100000, base: 316000, rate: 0.25, over: 3200000 },
    { upTo: 5600000, base: 541000, rate: 0.29, over: 4100000 },
    { upTo: 7000000, base: 976000, rate: 0.32, over: 5600000 },
    { upTo: Infinity, base: 1424000, rate: 0.35, over: 7000000 },
];

const salary = ref(250000);
const period = ref('monthly');
const pension = ref(0);
const medical = ref(0);
const other = ref(0);

function annualTax(income) {
    if (income <= 0) return 0;
    for (const s of SLABS) {
        if (income <= s.upTo) return s.base + (income - s.over) * s.rate;
    }
    return 0;
}

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const gross = computed(() => (period.value === 'monthly' ? salary.value * 12 : salary.value));
const valid = computed(() => gross.value > 0);
const result = computed(() => {
    const totalDeductions = Math.min(pension.value + medical.value + other.value, gross.value);
    const taxable = gross.value - totalDeductions;
    const taxBefore = annualTax(gross.value);
    const taxAfter = annualTax(taxable);
    return { taxable, taxBefore, taxAfter, saved: taxBefore - taxAfter };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="td-salary">Gross salary (PKR)</label>
                <input id="td-salary" v-model.number="salary" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <span class="segmented" role="group" aria-label="Salary period">
                    <input id="td-monthly" v-model="period" type="radio" value="monthly" /><label for="td-monthly">Monthly</label>
                    <input id="td-annual" v-model="period" type="radio" value="annual" /><label for="td-annual">Annual</label>
                </span>
            </div>
            <div class="field">
                <label for="td-pension">Pension / provident fund contribution (annual, PKR)</label>
                <input id="td-pension" v-model.number="pension" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="td-medical">Exempt medical allowance (annual, PKR)</label>
                <input id="td-medical" v-model.number="medical" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="td-other">Other deductions / exempt amounts (annual, PKR)</label>
                <input id="td-other" v-model.number="other" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="notice"><span>⚠️</span><span>Deduction eligibility and limits are set by the Income Tax Ordinance and change with budgets. This tool shows the arithmetic — confirm what you can actually claim with FBR or a tax adviser.</span></div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Annual tax after deductions</h2>
            <p class="result-big">{{ valid ? fmt(result.taxAfter) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Taxable income</span><b>{{ valid ? fmt(result.taxable) : '—' }}</b></div>
                <div class="result-row"><span>Tax without deductions</span><b>{{ valid ? fmt(result.taxBefore) : '—' }}</b></div>
                <div class="result-row"><span>Tax saved</span><b>{{ valid ? fmt(result.saved) : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
