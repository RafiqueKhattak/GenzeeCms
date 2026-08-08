<script setup>
import { computed, ref } from 'vue';

const hourlyRate = ref(500);
const regularHours = ref(160);
const overtimeHours = ref(12);
const overtimeMultiplier = ref(1.5);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => hourlyRate.value > 0);
const regularPay = computed(() => hourlyRate.value * regularHours.value);
const overtimeRate = computed(() => hourlyRate.value * overtimeMultiplier.value);
const overtimePay = computed(() => overtimeRate.value * overtimeHours.value);
const totalPay = computed(() => regularPay.value + overtimePay.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ot-rate">Regular hourly rate (PKR)</label>
                <input id="ot-rate" v-model.number="hourlyRate" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ot-regular">Regular hours this period</label>
                <input id="ot-regular" v-model.number="regularHours" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ot-overtime">Overtime hours</label>
                <input id="ot-overtime" v-model.number="overtimeHours" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="ot-multiplier">Overtime multiplier: <output>{{ overtimeMultiplier }}x</output></label>
                <input id="ot-multiplier" v-model.number="overtimeMultiplier" type="range" min="1" max="3" step="0.25" />
                <p class="hint">1.5x ("time and a half") is the most common rate; some employers pay 2x on holidays.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Total pay</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Total pay this period</h2>
                <p class="result-big">{{ fmt(totalPay) }}</p>
                <div class="result-rows">
                    <div class="result-row"><span>Regular pay</span><b>{{ fmt(regularPay) }}</b></div>
                    <div class="result-row"><span>Overtime pay ({{ fmt(overtimeRate) }}/hr)</span><b>{{ fmt(overtimePay) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
