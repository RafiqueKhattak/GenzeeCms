<script setup>
import { computed, ref } from 'vue';

const CONFIG = {
    goldRatePerGram: 24000,
    silverRatePerGram: 300,
    nisabGoldGrams: 87.48,
    nisabSilverGrams: 612.36,
    zakatRate: 0.025,
};

const cash = ref(0);
const receivables = ref(0);
const goldGrams = ref(0);
const goldRate = ref(CONFIG.goldRatePerGram);
const silverGrams = ref(0);
const silverRate = ref(CONFIG.silverRatePerGram);
const inventory = ref(0);
const liabilities = ref(0);
const nisabBasis = ref('silver');

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const result = computed(() => {
    const assets = cash.value + goldGrams.value * goldRate.value + silverGrams.value * silverRate.value + inventory.value + receivables.value;
    const net = assets - liabilities.value;
    const nisab = nisabBasis.value === 'gold'
        ? CONFIG.nisabGoldGrams * goldRate.value
        : CONFIG.nisabSilverGrams * silverRate.value;
    const payable = net >= nisab && net > 0;
    const due = payable ? net * CONFIG.zakatRate : 0;
    return { assets, net, nisab, due, payable };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field-row">
                <div class="field">
                    <label for="zk-cash">Cash &amp; bank balances (PKR)</label>
                    <input id="zk-cash" v-model.number="cash" type="number" min="0" step="any" inputmode="decimal" />
                </div>
                <div class="field">
                    <label for="zk-receivables">Money owed to you (PKR)</label>
                    <input id="zk-receivables" v-model.number="receivables" type="number" min="0" step="any" inputmode="decimal" />
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="zk-gold-grams">Gold (grams)</label>
                    <input id="zk-gold-grams" v-model.number="goldGrams" type="number" min="0" step="any" inputmode="decimal" />
                </div>
                <div class="field">
                    <label for="zk-gold-rate">Gold rate per gram (PKR)</label>
                    <input id="zk-gold-rate" v-model.number="goldRate" type="number" min="0" step="any" inputmode="decimal" />
                    <p class="hint">Editable — check today's market rate.</p>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="zk-silver-grams">Silver (grams)</label>
                    <input id="zk-silver-grams" v-model.number="silverGrams" type="number" min="0" step="any" inputmode="decimal" />
                </div>
                <div class="field">
                    <label for="zk-silver-rate">Silver rate per gram (PKR)</label>
                    <input id="zk-silver-rate" v-model.number="silverRate" type="number" min="0" step="any" inputmode="decimal" />
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="zk-inventory">Business inventory (PKR)</label>
                    <input id="zk-inventory" v-model.number="inventory" type="number" min="0" step="any" inputmode="decimal" />
                </div>
                <div class="field">
                    <label for="zk-liabilities">Liabilities / debts due (PKR)</label>
                    <input id="zk-liabilities" v-model.number="liabilities" type="number" min="0" step="any" inputmode="decimal" />
                </div>
            </div>
            <fieldset>
                <legend>Nisab basis</legend>
                <label class="choice"><input v-model="nisabBasis" type="radio" value="silver" /> Silver (612.36 g) — lower threshold, safer for giving</label>
                <label class="choice"><input v-model="nisabBasis" type="radio" value="gold" /> Gold (87.48 g)</label>
            </fieldset>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Zakat due (2.5%)</h2>
            <p class="result-big">{{ result.payable ? fmt(result.due) : 'PKR 0' }}</p>
            <p class="result-sub">{{ result.payable ? 'Your net wealth is above the nisab — zakat is due.' : 'Your net wealth is below the nisab — no zakat is due.' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Total assets</span><b>{{ fmt(result.assets) }}</b></div>
                <div class="result-row"><span>Net zakatable wealth</span><b>{{ fmt(result.net) }}</b></div>
                <div class="result-row"><span>Nisab threshold</span><b>{{ fmt(result.nisab) }}</b></div>
            </div>
        </div>
    </div>
</template>
