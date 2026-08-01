<script setup>
import { computed, ref } from 'vue';

const a1 = ref(15);
const b1 = ref(2400);
const a2 = ref(45);
const b2 = ref(180);
const a3 = ref(200);
const b3 = ref(250);

function show(x, suffix = '') {
    if (isNaN(x) || !isFinite(x)) return '—';
    return `${(Math.round(x * 10000) / 10000).toLocaleString('en-US')}${suffix}`;
}

const out1 = computed(() => show((a1.value / 100) * b1.value));
const out2 = computed(() => show(b2.value === 0 ? NaN : (a2.value / b2.value) * 100, '%'));
const changeVal = computed(() => (a3.value === 0 ? NaN : ((b3.value - a3.value) / Math.abs(a3.value)) * 100));
const out3 = computed(() => {
    const c = changeVal.value;
    const label = show(Math.abs(c), '%');
    return label + (isNaN(c) ? '' : c >= 0 ? ' increase' : ' decrease');
});
</script>

<template>
    <div class="tool-card tool-card-stack">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <fieldset>
                <legend>What is X% of Y?</legend>
                <div class="field-row">
                    <div class="field"><label for="pc-a1">X (%)</label><input id="pc-a1" v-model.number="a1" type="number" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="pc-b1">Y</label><input id="pc-b1" v-model.number="b1" type="number" step="any" inputmode="decimal" /></div>
                    <div class="field"><span class="hint" aria-hidden="true">Result</span><p class="result-big" style="font-size: 1.5rem; color: var(--accent-deep)" aria-live="polite">{{ out1 }}</p></div>
                </div>
            </fieldset>
            <fieldset>
                <legend>X is what percent of Y?</legend>
                <div class="field-row">
                    <div class="field"><label for="pc-a2">X</label><input id="pc-a2" v-model.number="a2" type="number" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="pc-b2">Y</label><input id="pc-b2" v-model.number="b2" type="number" step="any" inputmode="decimal" /></div>
                    <div class="field"><span class="hint" aria-hidden="true">Result</span><p class="result-big" style="font-size: 1.5rem; color: var(--accent-deep)" aria-live="polite">{{ out2 }}</p></div>
                </div>
            </fieldset>
            <fieldset>
                <legend>Percentage change from X to Y</legend>
                <div class="field-row">
                    <div class="field"><label for="pc-a3">From (X)</label><input id="pc-a3" v-model.number="a3" type="number" step="any" inputmode="decimal" /></div>
                    <div class="field"><label for="pc-b3">To (Y)</label><input id="pc-b3" v-model.number="b3" type="number" step="any" inputmode="decimal" /></div>
                    <div class="field"><span class="hint" aria-hidden="true">Result</span><p class="result-big" style="font-size: 1.5rem; color: var(--accent-deep)" aria-live="polite">{{ out3 }}</p></div>
                </div>
            </fieldset>
        </form>
    </div>
</template>
