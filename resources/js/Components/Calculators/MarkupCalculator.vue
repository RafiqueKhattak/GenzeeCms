<script setup>
import { computed, ref } from 'vue';

const cost = ref(700);
const markupPct = ref(30);

function fmt(x) {
    return `PKR ${x.toLocaleString('en-PK', { maximumFractionDigits: 2 })}`;
}

const valid = computed(() => cost.value > 0);
const sellingPrice = computed(() => cost.value * (1 + markupPct.value / 100));
const profit = computed(() => sellingPrice.value - cost.value);
// The resulting profit margin (profit as % of selling price) — not the same
// number as the markup %, which is the whole point of this calculator.
const resultingMargin = computed(() => (sellingPrice.value > 0 ? (profit.value / sellingPrice.value) * 100 : 0));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="mk-cost">Cost price (PKR)</label>
                <input id="mk-cost" v-model.number="cost" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="mk-markup">Markup: <output>{{ markupPct }}%</output></label>
                <input id="mk-markup" v-model.number="markupPct" type="range" min="0" max="200" step="1" />
                <p class="hint">Markup is added on top of cost — a 50% markup on a 700 cost gives a 1,050 selling price.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Selling price</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Selling price</h2>
                <p class="result-big">{{ fmt(sellingPrice) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Profit per unit</span><b>{{ fmt(profit) }}</b></div>
                    <div class="result-row"><span>Resulting profit margin</span><b>{{ resultingMargin.toFixed(1) }}%</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
