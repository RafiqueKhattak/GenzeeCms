<script setup>
import { computed, ref } from 'vue';

const itemValue = ref(200);
const exchangeRate = ref(280);
const customsDutyPct = ref(20);
const salesTaxPct = ref(18);
const otherChargesPct = ref(5);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => itemValue.value >= 0 && exchangeRate.value > 0);

const baseValuePkr = computed(() => itemValue.value * exchangeRate.value);
const customsDuty = computed(() => (baseValuePkr.value * customsDutyPct.value) / 100);
// Sales tax is commonly applied on (value + duty), not on value alone —
// modeling it "duty-inclusive" gives a more realistic landed cost estimate.
const salesTax = computed(() => ((baseValuePkr.value + customsDuty.value) * salesTaxPct.value) / 100);
const otherCharges = computed(() => (baseValuePkr.value * otherChargesPct.value) / 100);
const totalLandedCost = computed(() => baseValuePkr.value + customsDuty.value + salesTax.value + otherCharges.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="id-value">Item value (foreign currency, e.g. USD)</label>
                <input id="id-value" v-model.number="itemValue" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="id-rate">Exchange rate (PKR per unit)</label>
                <input id="id-rate" v-model.number="exchangeRate" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="id-duty">Customs duty (%)</label>
                <input id="id-duty" v-model.number="customsDutyPct" type="number" min="0" max="200" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="id-sales">Sales tax (%)</label>
                <input id="id-sales" v-model.number="salesTaxPct" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="id-other">Other charges — handling, regulatory (%)</label>
                <input id="id-other" v-model.number="otherChargesPct" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Estimated total landed cost</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Estimated total landed cost</h2>
                <p class="result-big">{{ fmt(totalLandedCost) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Item value in PKR</span><b>{{ fmt(baseValuePkr) }}</b></div>
                    <div class="result-row"><span>Customs duty</span><b>{{ fmt(customsDuty) }}</b></div>
                    <div class="result-row"><span>Sales tax</span><b>{{ fmt(salesTax) }}</b></div>
                    <div class="result-row"><span>Other charges</span><b>{{ fmt(otherCharges) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
