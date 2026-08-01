<script setup>
import { computed, ref } from 'vue';

const start = ref('2026-01-01');
const end = ref('2026-07-19');

function parse(v) {
    if (!v) return null;
    const [y, m, d] = v.split('-').map(Number);
    return new Date(y, m - 1, d);
}

const result = computed(() => {
    let a = parse(start.value);
    let b = parse(end.value);
    if (!a || !b) return null;
    if (a > b) [a, b] = [b, a];
    const totalDays = Math.round((b - a) / 86400000);
    let years = b.getFullYear() - a.getFullYear();
    let months = b.getMonth() - a.getMonth();
    let d = b.getDate() - a.getDate();
    if (d < 0) {
        months -= 1;
        d += new Date(b.getFullYear(), b.getMonth(), 0).getDate();
    }
    if (months < 0) {
        years -= 1;
        months += 12;
    }
    return {
        totalDays,
        weeks: Math.floor(totalDays / 7),
        remDays: totalDays % 7,
        years,
        months,
        days: d,
        totalMonths: years * 12 + months,
    };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field-row">
                <div class="field"><label for="dd-start">First date</label><input id="dd-start" v-model="start" type="date" /></div>
                <div class="field"><label for="dd-end">Second date</label><input id="dd-end" v-model="end" type="date" /></div>
            </div>
            <p class="hint">Order doesn't matter — the tool always measures the positive gap.</p>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Difference</h2>
            <p class="result-big">{{ result ? `${result.totalDays.toLocaleString('en-US')} days` : '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>In weeks</span><b>{{ result ? `${result.weeks} weeks, ${result.remDays} days` : '—' }}</b></div>
                <div class="result-row"><span>Years / months / days</span><b>{{ result ? `${result.years} years, ${result.months} months, ${result.days} days` : '—' }}</b></div>
                <div class="result-row"><span>In full months</span><b>{{ result ? `${result.totalMonths} full months` : '—' }}</b></div>
            </div>
        </div>
    </div>
</template>
