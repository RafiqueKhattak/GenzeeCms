<script setup>
import { computed, ref } from 'vue';

const fixedCosts = ref(500000);
const pricePerUnit = ref(1200);
const variableCostPerUnit = ref(700);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const contributionMargin = computed(() => pricePerUnit.value - variableCostPerUnit.value);
const valid = computed(() => fixedCosts.value >= 0 && contributionMargin.value > 0);

const breakEvenUnits = computed(() => (valid.value ? Math.ceil(fixedCosts.value / contributionMargin.value) : 0));
const breakEvenRevenue = computed(() => breakEvenUnits.value * pricePerUnit.value);
const contributionMarginPct = computed(() => (pricePerUnit.value > 0 ? (contributionMargin.value / pricePerUnit.value) * 100 : 0));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="be-fixed">Fixed costs, per period (PKR)</label>
                <input id="be-fixed" v-model.number="fixedCosts" type="number" min="0" step="any" inputmode="decimal" />
                <p class="hint">Rent, salaries, insurance — costs that don't change with how much you sell.</p>
            </div>
            <div class="field">
                <label for="be-price">Selling price per unit (PKR)</label>
                <input id="be-price" v-model.number="pricePerUnit" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="be-variable">Variable cost per unit (PKR)</label>
                <input id="be-variable" v-model.number="variableCostPerUnit" type="number" min="0" step="any" inputmode="decimal" />
                <p class="hint">Materials, packaging, per-unit shipping — costs that scale directly with units sold.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Break-even point</h2>
                <p class="result-big">—</p>
                <p class="result-sub">Selling price must be higher than variable cost per unit.</p>
            </template>
            <template v-else>
                <h2>Break-even point</h2>
                <p class="result-big">{{ breakEvenUnits.toLocaleString('en-PK') }} units</p>
                <p class="result-sub">{{ fmt(breakEvenRevenue) }} in revenue, to cover all costs with zero profit</p>
                <div class="result-rows">
                    <div class="result-row"><span>Contribution margin per unit</span><b>{{ fmt(contributionMargin) }}</b></div>
                    <div class="result-row"><span>Contribution margin %</span><b>{{ contributionMarginPct.toFixed(1) }}%</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
