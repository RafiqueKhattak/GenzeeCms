<script setup>
import { computed, ref } from 'vue';

// Decimal (SI, what storage manufacturers advertise) vs binary (what
// operating systems actually report) — both are "real", they just disagree
// by roughly 7% per step, which is why a "1TB" drive shows less in Windows.
const DECIMAL_UNITS = { bit: 0.125, byte: 1, KB: 1e3, MB: 1e6, GB: 1e9, TB: 1e12, PB: 1e15 };
const BINARY_UNITS = { bit: 0.125, byte: 1, KiB: 1024, MiB: 1024 ** 2, GiB: 1024 ** 3, TiB: 1024 ** 4, PiB: 1024 ** 5 };

const mode = ref('decimal');
const value = ref(1);
const from = ref('GB');
const to = ref('MB');

const units = computed(() => (mode.value === 'decimal' ? DECIMAL_UNITS : BINARY_UNITS));
const unitNames = computed(() => Object.keys(units.value));

const result = computed(() => {
    const bytes = value.value * (units.value[from.value] ?? 0);
    const converted = bytes / (units.value[to.value] ?? 1);
    return converted;
});

function fmt(x) {
    if (!isFinite(x)) return '—';
    return x.toLocaleString('en-US', { maximumFractionDigits: 6 });
}
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ds-mode">Unit system</label>
                <select
                    id="ds-mode"
                    v-model="mode"
                    @change="
                        from = mode === 'decimal' ? 'GB' : 'GiB';
                        to = mode === 'decimal' ? 'MB' : 'MiB';
                    "
                >
                    <option value="decimal">Decimal (KB, MB, GB — what storage is sold in)</option>
                    <option value="binary">Binary (KiB, MiB, GiB — what your OS reports)</option>
                </select>
            </div>
            <div class="field">
                <label for="ds-value">Value</label>
                <input id="ds-value" v-model.number="value" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ds-from">From</label>
                <select id="ds-from" v-model="from">
                    <option v-for="u in unitNames" :key="u" :value="u">{{ u }}</option>
                </select>
            </div>
            <div class="field">
                <label for="ds-to">To</label>
                <select id="ds-to" v-model="to">
                    <option v-for="u in unitNames" :key="u" :value="u">{{ u }}</option>
                </select>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Result</h2>
            <p class="result-big">{{ fmt(result) }} {{ to }}</p>
            <p class="result-sub">{{ value }} {{ from }} converted</p>
        </div>
    </div>
</template>
