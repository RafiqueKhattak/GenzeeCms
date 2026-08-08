<script setup>
import { computed, ref } from 'vue';

const currentSalary = ref(100000);
const mode = ref('percent');
const incrementPct = ref(10);
const newSalaryInput = ref(110000);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => currentSalary.value > 0);

const newSalary = computed(() => {
    if (mode.value === 'percent') {
        return currentSalary.value * (1 + incrementPct.value / 100);
    }
    return newSalaryInput.value;
});

const increaseAmount = computed(() => newSalary.value - currentSalary.value);
const increasePct = computed(() => (valid.value ? (increaseAmount.value / currentSalary.value) * 100 : 0));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="si-current">Current monthly salary (PKR)</label>
                <input id="si-current" v-model.number="currentSalary" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="si-mode">Calculate</label>
                <select id="si-mode" v-model="mode">
                    <option value="percent">New salary from a % increase</option>
                    <option value="amount">% increase from a new salary</option>
                </select>
            </div>
            <div v-if="mode === 'percent'" class="field">
                <label for="si-pct">Increment: <output>{{ incrementPct }}%</output></label>
                <input id="si-pct" v-model.number="incrementPct" type="range" min="0" max="100" step="1" />
            </div>
            <div v-else class="field">
                <label for="si-new">New monthly salary (PKR)</label>
                <input id="si-new" v-model.number="newSalaryInput" type="number" min="0" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Result</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else-if="mode === 'percent'">
                <h2>New salary</h2>
                <p class="result-big">{{ fmt(newSalary) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Increase amount</span><b>{{ fmt(increaseAmount) }}</b></div>
                </div>
            </template>
            <template v-else>
                <h2>Percentage increase</h2>
                <p class="result-big">{{ increasePct.toFixed(2) }}%</p>
                <div class="result-rows">
                    <div class="result-row"><span>Increase amount</span><b>{{ fmt(increaseAmount) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
