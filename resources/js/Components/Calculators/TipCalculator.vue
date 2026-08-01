<script setup>
import { computed, ref } from 'vue';

const bill = ref(4800);
const pctVal = ref(10);
const people = ref(1);

function fmt(x) {
    return `PKR ${x.toLocaleString('en-PK', { maximumFractionDigits: 0 })}`;
}

const valid = computed(() => bill.value > 0);
const result = computed(() => {
    const t = (bill.value * pctVal.value) / 100;
    const total = bill.value + t;
    const n = Math.max(1, people.value || 1);
    return { tip: t, total, perPerson: total / n };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="tip-bill">Bill amount (PKR)</label>
                <input id="tip-bill" v-model.number="bill" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="tip-pct">Tip: <output>{{ pctVal }}%</output></label>
                <input id="tip-pct" v-model.number="pctVal" type="range" min="0" max="30" step="1" />
            </div>
            <div class="field">
                <label for="tip-people">Split between (people)</label>
                <input id="tip-people" v-model.number="people" type="number" min="1" max="100" step="1" inputmode="numeric" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Each person pays</h2>
            <p class="result-big">{{ valid ? fmt(result.perPerson) : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Tip amount</span><b>{{ valid ? fmt(result.tip) : '—' }}</b></div>
                <div class="result-row"><span>Total with tip</span><b>{{ valid ? fmt(result.total) : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
