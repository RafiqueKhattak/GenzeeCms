<script setup>
import { computed, ref } from 'vue';

const cashSavings = ref(150000);
const investments = ref(200000);
const propertyValue = ref(0);
const otherAssets = ref(50000);

const loans = ref(100000);
const creditCardDebt = ref(20000);
const otherLiabilities = ref(0);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const totalAssets = computed(() => cashSavings.value + investments.value + propertyValue.value + otherAssets.value);
const totalLiabilities = computed(() => loans.value + creditCardDebt.value + otherLiabilities.value);
const netWorth = computed(() => totalAssets.value - totalLiabilities.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="nw-cash">Cash & savings (PKR)</label>
                <input id="nw-cash" v-model.number="cashSavings" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="nw-invest">Investments (stocks, funds, crypto) (PKR)</label>
                <input id="nw-invest" v-model.number="investments" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="nw-property">Property / vehicles value (PKR)</label>
                <input id="nw-property" v-model.number="propertyValue" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="nw-other-assets">Other assets (PKR)</label>
                <input id="nw-other-assets" v-model.number="otherAssets" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="nw-loans">Loans (student, car, personal) (PKR)</label>
                <input id="nw-loans" v-model.number="loans" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="nw-cc">Credit card debt (PKR)</label>
                <input id="nw-cc" v-model.number="creditCardDebt" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="nw-other-liab">Other liabilities (PKR)</label>
                <input id="nw-other-liab" v-model.number="otherLiabilities" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Your net worth</h2>
            <p class="result-big" :style="{ color: netWorth < 0 ? '#dc2626' : undefined }">{{ fmt(netWorth) }}</p>
            <p class="result-sub">{{ netWorth < 0 ? "You owe more than you own right now — that's normal early on, and tracking it is the first step to flipping it." : 'What you own, minus what you owe' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Total assets</span><b>{{ fmt(totalAssets) }}</b></div>
                <div class="result-row"><span>Total liabilities</span><b>{{ fmt(totalLiabilities) }}</b></div>
            </div>
        </div>
    </div>
</template>
