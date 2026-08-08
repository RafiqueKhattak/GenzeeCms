<script setup>
import { computed, ref } from 'vue';

const income = ref(150000);
const needsPct = ref(50);
const wantsPct = ref(30);
// Savings/debt share is whatever's left, so the three always sum to 100.
const savingsPct = computed(() => Math.max(0, 100 - needsPct.value - wantsPct.value));

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => income.value > 0);
const needs = computed(() => (income.value * needsPct.value) / 100);
const wants = computed(() => (income.value * wantsPct.value) / 100);
const savings = computed(() => (income.value * savingsPct.value) / 100);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="bg-income">Monthly take-home income (PKR)</label>
                <input id="bg-income" v-model.number="income" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="bg-needs">Needs: <output>{{ needsPct }}%</output></label>
                <input id="bg-needs" v-model.number="needsPct" type="range" min="0" max="100" step="5" />
                <p class="hint">Rent, groceries, utilities, minimum debt payments — the non-negotiables.</p>
            </div>
            <div class="field">
                <label for="bg-wants">Wants: <output>{{ wantsPct }}%</output></label>
                <input id="bg-wants" v-model.number="wantsPct" type="range" min="0" max="100" step="5" />
                <p class="hint">Eating out, subscriptions, hobbies — the classic 50/30/20 rule starts here at 30%.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Your monthly split</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Your monthly split</h2>
                <p class="result-big">{{ fmt(savings) }}</p>
                <p class="result-sub">left for savings & debt payoff ({{ savingsPct }}%)</p>
                <div class="result-rows">
                    <div class="result-row"><span>Needs ({{ needsPct }}%)</span><b>{{ fmt(needs) }}</b></div>
                    <div class="result-row"><span>Wants ({{ wantsPct }}%)</span><b>{{ fmt(wants) }}</b></div>
                    <div class="result-row"><span>Savings & debt ({{ savingsPct }}%)</span><b>{{ fmt(savings) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
