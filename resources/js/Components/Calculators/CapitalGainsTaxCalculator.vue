<script setup>
import { computed, ref } from 'vue';

const purchasePrice = ref(500000);
const salePrice = ref(700000);
const otherCosts = ref(10000);
const taxRate = ref(15);
const isLongTerm = ref(true);
const longTermDiscountPct = ref(25);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => purchasePrice.value >= 0 && salePrice.value >= 0);
const gain = computed(() => Math.max(0, salePrice.value - purchasePrice.value - otherCosts.value));

// Many jurisdictions tax long-held assets at a reduced effective rate — this
// models that as a simple discount off the stated rate, since the exact
// mechanism (a flat lower rate vs. a rate reduction) varies by country.
const effectiveRate = computed(() => {
    if (!isLongTerm.value) return taxRate.value;
    return taxRate.value * (1 - longTermDiscountPct.value / 100);
});

const taxOwed = computed(() => (gain.value * effectiveRate.value) / 100);
const netProceeds = computed(() => salePrice.value - otherCosts.value - taxOwed.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="cg-purchase">Purchase price (PKR)</label>
                <input id="cg-purchase" v-model.number="purchasePrice" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cg-sale">Sale price (PKR)</label>
                <input id="cg-sale" v-model.number="salePrice" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cg-costs">Other costs — fees, commission (PKR)</label>
                <input id="cg-costs" v-model.number="otherCosts" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="cg-rate">Capital gains tax rate (%)</label>
                <input id="cg-rate" v-model.number="taxRate" type="number" min="0" max="60" step="any" inputmode="decimal" />
                <p class="hint">Check your country's actual rate — it varies widely and often depends on the asset type.</p>
            </div>
            <div class="field checkbox-field">
                <label><input v-model="isLongTerm" type="checkbox" /> Held long-term (many jurisdictions tax this lower)</label>
            </div>
            <div v-if="isLongTerm" class="field">
                <label for="cg-discount">Long-term rate reduction: <output>{{ longTermDiscountPct }}%</output></label>
                <input id="cg-discount" v-model.number="longTermDiscountPct" type="range" min="0" max="100" step="5" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Tax owed</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Estimated tax owed</h2>
                <p class="result-big">{{ fmt(taxOwed) }}</p>
                <p class="result-sub">On a capital gain of {{ fmt(gain) }}, at an effective {{ effectiveRate.toFixed(1) }}%</p>
                <div class="result-rows">
                    <div class="result-row"><span>Net proceeds after tax & costs</span><b>{{ fmt(netProceeds) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>

<style scoped>
.checkbox-field label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}
</style>
