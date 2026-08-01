<script setup>
import { computed, ref } from 'vue';

const cost = ref(700);
const price = ref(1000);
const tCost = ref(600);
const tMargin = ref(40);

function fmt(x) {
    return `PKR ${x.toLocaleString('en-PK', { maximumFractionDigits: 2 })}`;
}
function pct(x) {
    return isNaN(x) || !isFinite(x) ? '—' : `${Math.round(x * 100) / 100}%`;
}

const valid = computed(() => cost.value > 0 && price.value > 0);
const profit = computed(() => price.value - cost.value);
const margin = computed(() => (price.value !== 0 ? (profit.value / price.value) * 100 : NaN));
const markup = computed(() => (cost.value !== 0 ? (profit.value / cost.value) * 100 : NaN));

const targetValid = computed(() => tCost.value > 0 && !isNaN(tMargin.value) && tMargin.value < 100);
const targetPrice = computed(() => tCost.value / (1 - tMargin.value / 100));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <fieldset>
                <legend>Analyse a price</legend>
                <div class="field-row">
                    <div class="field"><label for="pm-cost">Cost (PKR)</label><input id="pm-cost" v-model.number="cost" type="number" min="0" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="pm-price">Selling price (PKR)</label><input id="pm-price" v-model.number="price" type="number" min="0" step="any" inputmode="decimal" /></div>
                </div>
            </fieldset>
            <fieldset>
                <legend>Price for a target margin</legend>
                <div class="field-row">
                    <div class="field"><label for="pm-tcost">Cost (PKR)</label><input id="pm-tcost" v-model.number="tCost" type="number" min="0" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="pm-tmargin">Target margin (%)</label><input id="pm-tmargin" v-model.number="tMargin" type="number" min="0" max="99" step="any" inputmode="decimal" /></div>
                </div>
                <p class="hint">Required price: <b aria-live="polite">{{ targetValid ? fmt(targetPrice) : '—' }}</b></p>
            </fieldset>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Profit per unit</h2>
            <p class="result-big">{{ valid ? fmt(profit) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Profit margin (of price)</span><b>{{ valid ? pct(margin) : '—' }}</b></div>
                <div class="result-row"><span>Markup (on cost)</span><b>{{ valid ? pct(markup) : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
