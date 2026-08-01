<script setup>
import { computed, ref } from 'vue';

const DAY = 86400000;
const lmp = ref('');
const cycle = ref(28);

function fmtDate(d) {
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

const result = computed(() => {
    if (!lmp.value) return null;
    const [y, m, d] = lmp.value.split('-').map(Number);
    const lmpDate = new Date(y, m - 1, d);
    const adjust = (cycle.value || 28) - 28;
    const due = new Date(lmpDate.getTime() + (280 + adjust) * DAY);
    const now = new Date();
    const days = Math.floor((now - lmpDate) / DAY);
    const weeks = Math.floor(days / 7);
    const remDays = days % 7;
    if (days < 0 || weeks > 43) {
        return { due: fmtDate(due), invalid: true };
    }
    const trimester = weeks < 13 ? 'First trimester' : weeks < 27 ? 'Second trimester' : 'Third trimester';
    return { due: fmtDate(due), week: `${weeks} weeks, ${remDays} days`, trimester };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="dd-lmp">First day of last menstrual period (LMP)</label>
                <input id="dd-lmp" v-model="lmp" type="date" />
            </div>
            <div class="field">
                <label for="dd-cycle">Average cycle length (days)</label>
                <input id="dd-cycle" v-model.number="cycle" type="number" min="20" max="45" step="1" inputmode="numeric" />
                <p class="hint">If your cycles run long or short, the due date shifts by the same number of days.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Estimated due date</h2>
            <p class="result-big">{{ result ? result.due : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Currently</span><b>{{ result ? (result.invalid ? 'Check the date entered' : result.week) : '—' }}</b></div>
                <div class="result-row"><span>Trimester</span><b>{{ result && !result.invalid ? result.trimester : '—' }}</b></div>
            </div>
        </div>
    </div>
    <div class="notice notice-info"><span>ℹ️</span><span>An estimate only — around 4% of babies arrive on their exact due date. Your doctor's ultrasound dating takes precedence over any calculator.</span></div>
</template>
