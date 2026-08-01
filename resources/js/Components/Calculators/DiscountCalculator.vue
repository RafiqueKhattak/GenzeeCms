<script setup>
import { computed, ref } from 'vue';

const price = ref(2500);
const pctOff = ref(30);
const orig = ref(2000);
const finalPrice = ref(1500);

function fmt(x) {
    return `PKR ${x.toLocaleString('en-PK', { maximumFractionDigits: 2 })}`;
}

const payValid = computed(() => price.value > 0);
const pay = computed(() => price.value - (price.value * pctOff.value) / 100);
const save = computed(() => (price.value * pctOff.value) / 100);

const offValid = computed(() => orig.value > 0 && !isNaN(finalPrice.value));
const off = computed(() => ((orig.value - finalPrice.value) / orig.value) * 100);
</script>

<template>
    <div class="tool-card tool-card-stack">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <fieldset>
                <legend>Price after discount</legend>
                <div class="field-row">
                    <div class="field"><label for="dc-price">Original price (PKR)</label><input id="dc-price" v-model.number="price" type="number" min="0" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="dc-pct">Discount (%)</label><input id="dc-pct" v-model.number="pctOff" type="number" min="0" max="100" step="any" inputmode="decimal" /></div>
                    <div class="field"><span class="hint" aria-hidden="true">You pay</span><p class="result-big" style="font-size: 1.5rem; color: var(--accent-deep)" aria-live="polite">{{ payValid ? fmt(pay) : '—' }}</p></div>
                    <div class="field"><span class="hint" aria-hidden="true">You save</span><p class="result-big" style="font-size: 1.5rem; color: var(--accent-deep)" aria-live="polite">{{ payValid ? fmt(save) : '—' }}</p></div>
                </div>
            </fieldset>
            <fieldset>
                <legend>What percent off is this? (reverse)</legend>
                <div class="field-row">
                    <div class="field"><label for="dc-orig">Original price (PKR)</label><input id="dc-orig" v-model.number="orig" type="number" min="0" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="dc-final">Sale price (PKR)</label><input id="dc-final" v-model.number="finalPrice" type="number" step="any" inputmode="decimal" /></div>
                    <div class="field"><span class="hint" aria-hidden="true">Discount</span><p class="result-big" style="font-size: 1.5rem; color: var(--accent-deep)" aria-live="polite">{{ offValid ? `${Math.round(off * 100) / 100}% off` : '—' }}</p></div>
                </div>
            </fieldset>
        </form>
    </div>
</template>
