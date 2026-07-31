<script setup>
import { computed, ref } from 'vue';

const dob = ref('');

function ageBetween(birth, now) {
    let years = now.getFullYear() - birth.getFullYear();
    let months = now.getMonth() - birth.getMonth();
    let days = now.getDate() - birth.getDate();
    if (days < 0) {
        months -= 1;
        days += new Date(now.getFullYear(), now.getMonth(), 0).getDate();
    }
    if (months < 0) {
        years -= 1;
        months += 12;
    }
    return { years, months, days };
}

function nextBirthday(birth, now) {
    let next = new Date(now.getFullYear(), birth.getMonth(), birth.getDate());
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    if (next < today) next = new Date(now.getFullYear() + 1, birth.getMonth(), birth.getDate());
    return Math.round((next - today) / 86400000);
}

const result = computed(() => {
    if (!dob.value) return null;
    const [y, m, d] = dob.value.split('-').map(Number);
    const birth = new Date(y, m - 1, d);
    const now = new Date();
    if (birth > now) return { future: true };
    const a = ageBetween(birth, now);
    const totalDays = Math.floor((now - birth) / 86400000);
    const nb = nextBirthday(birth, now);
    return {
        main: `${a.years}y ${a.months}m ${a.days}d`,
        days: `That is about ${totalDays.toLocaleString('en-US')} days lived.`,
        next: nb === 0 ? 'Today — happy birthday! 🎉' : `${nb} days`,
    };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="age-dob">Date of birth</label>
                <input id="age-dob" v-model="dob" type="date" />
                <p class="hint">Your age is computed against today's date on your device.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Your exact age</h2>
            <p class="result-big">{{ result?.future ? 'Date is in the future' : (result?.main ?? '—') }}</p>
            <p class="result-sub">{{ result && !result.future ? result.days : '' }}</p>
            <div class="result-rows">
                <div class="result-row">
                    <span>Next birthday in</span>
                    <b>{{ result && !result.future ? result.next : '—' }}</b>
                </div>
            </div>
        </div>
    </div>
</template>
