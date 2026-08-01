<script setup>
import { computed, ref } from 'vue';

const target = ref(1000000);
const start = ref(0);
const monthly = ref(20000);
const rate = ref(12);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

function monthsToGoal(t, s, m, annualPct) {
    if (s >= t) return 0;
    const i = annualPct / 100 / 12;
    if (m <= 0 && (i <= 0 || s <= 0)) return Infinity;
    if (i === 0) return Math.ceil((t - s) / m);
    const num = t * i + m;
    const den = s * i + m;
    if (den <= 0 || num / den <= 0) return Infinity;
    return Math.ceil(Math.log(num / den) / Math.log(1 + i));
}

const n = computed(() => monthsToGoal(target.value, start.value, monthly.value, rate.value));
const contributed = computed(() => start.value + monthly.value * n.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="sg-target">Savings target (PKR)</label>
                <input id="sg-target" v-model.number="target" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="sg-start">Already saved (PKR)</label>
                <input id="sg-start" v-model.number="start" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="sg-monthly">Monthly saving (PKR)</label>
                <input id="sg-monthly" v-model.number="monthly" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="sg-rate">Expected annual return (%)</label>
                <input id="sg-rate" v-model.number="rate" type="number" min="0" max="50" step="any" inputmode="decimal" />
                <p class="hint">Use 0 for cash under the mattress; a savings account or fund rate otherwise.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="target <= 0">
                <h2>Time to reach your goal</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else-if="!isFinite(n)">
                <h2>Time to reach your goal</h2>
                <p class="result-big">Never</p>
                <p class="result-sub">With no monthly saving and no growth, the target can't be reached.</p>
            </template>
            <template v-else-if="n === 0">
                <h2>Time to reach your goal</h2>
                <p class="result-big">Already there!</p>
                <p class="result-sub">Your starting amount meets the target.</p>
                <div class="result-rows">
                    <div class="result-row"><span>You'll contribute</span><b>{{ fmt(start) }}</b></div>
                    <div class="result-row"><span>Growth does the rest</span><b>{{ fmt(0) }}</b></div>
                </div>
            </template>
            <template v-else>
                <h2>Time to reach your goal</h2>
                <p class="result-big">{{ Math.floor(n / 12) ? `${Math.floor(n / 12)}y ` : '' }}{{ n % 12 }}m</p>
                <p class="result-sub">{{ n }} monthly deposits of {{ fmt(monthly) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>You'll contribute</span><b>{{ fmt(contributed) }}</b></div>
                    <div class="result-row"><span>Growth does the rest</span><b>{{ fmt(Math.max(0, target - contributed)) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
