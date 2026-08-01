<script setup>
import { computed, ref } from 'vue';

const principal = ref(100000);
const rate = ref(10);
const years = ref(10);
const freq = ref(1);

function compound(P, r, t, m) {
    return P * Math.pow(1 + r / 100 / m, m * t);
}

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const t = computed(() => Math.min(80, Math.max(0, Math.round(years.value))));
const valid = computed(() => principal.value > 0 && t.value > 0);
const finalAmount = computed(() => (valid.value ? compound(principal.value, rate.value, t.value, freq.value) : 0));
const rows = computed(() => {
    if (!valid.value) return [];
    const out = [];
    for (let y = 1; y <= t.value; y++) {
        const amount = compound(principal.value, rate.value, y, freq.value);
        out.push({ year: y, amount, interest: amount - principal.value });
    }
    return out;
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ci-principal">Principal amount (PKR)</label>
                <input id="ci-principal" v-model.number="principal" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ci-rate">Annual interest rate (%)</label>
                <input id="ci-rate" v-model.number="rate" type="number" min="0" max="100" step="any" inputmode="decimal" />
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="ci-years">Time period (years)</label>
                    <input id="ci-years" v-model.number="years" type="number" min="1" max="80" step="1" inputmode="numeric" />
                </div>
                <div class="field">
                    <label for="ci-freq">Compounding frequency</label>
                    <select id="ci-freq" v-model.number="freq">
                        <option :value="1">Yearly</option>
                        <option :value="2">Half-yearly</option>
                        <option :value="4">Quarterly</option>
                        <option :value="12">Monthly</option>
                        <option :value="365">Daily</option>
                    </select>
                </div>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Final amount</h2>
            <p class="result-big">{{ valid ? fmt(finalAmount) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Principal</span><b>{{ valid ? fmt(principal) : '—' }}</b></div>
                <div class="result-row"><span>Total interest earned</span><b>{{ valid ? fmt(finalAmount - principal) : '—' }}</b></div>
            </div>
        </div>
    </div>
    <details v-if="valid" class="fold no-print">
        <summary>Show year-by-year growth table</summary>
        <div class="fold-body">
            <div class="table-wrap">
                <table>
                    <thead><tr><th scope="col">Year</th><th scope="col" class="num">Balance</th><th scope="col" class="num">Interest so far</th></tr></thead>
                    <tbody>
                        <tr v-for="row in rows" :key="row.year">
                            <td>{{ row.year }}</td>
                            <td class="num">{{ fmt(row.amount) }}</td>
                            <td class="num">{{ fmt(row.interest) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </details>
</template>
