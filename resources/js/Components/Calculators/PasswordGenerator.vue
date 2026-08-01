<script setup>
import { computed, onMounted, ref } from 'vue';

const SETS = {
    lower: 'abcdefghijklmnopqrstuvwxyz',
    upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    digits: '0123456789',
    symbols: '!@#$%^&*()-_=+[]{};:,.?/',
};

const length = ref(16);
const lower = ref(true);
const upper = ref(true);
const digits = ref(true);
const symbols = ref(true);
const password = ref('');
const copyLabel = ref('Copy');
const mounted = ref(false);

function cryptoRand() {
    const buf = new Uint32Array(1);
    window.crypto.getRandomValues(buf);
    return buf[0];
}
function randomIndex(max, randFn) {
    const limit = Math.floor(4294967296 / max) * max;
    let x;
    do {
        x = randFn();
    } while (x >= limit);
    return x % max;
}
function opts() {
    return { lower: lower.value, upper: upper.value, digits: digits.value, symbols: symbols.value };
}
function generate(len, o, randFn) {
    let pool = '';
    const required = [];
    ['lower', 'upper', 'digits', 'symbols'].forEach((k) => {
        if (o[k]) {
            pool += SETS[k];
            required.push(SETS[k]);
        }
    });
    if (!pool || len < 1) return '';
    const chars = [];
    required.slice(0, len).forEach((set) => chars.push(set.charAt(randomIndex(set.length, randFn))));
    while (chars.length < len) chars.push(pool.charAt(randomIndex(pool.length, randFn)));
    for (let i = chars.length - 1; i > 0; i--) {
        const j = randomIndex(i + 1, randFn);
        [chars[i], chars[j]] = [chars[j], chars[i]];
    }
    return chars.join('');
}
function entropy(len, o) {
    let pool = 0;
    ['lower', 'upper', 'digits', 'symbols'].forEach((k) => {
        if (o[k]) pool += SETS[k].length;
    });
    return pool ? len * (Math.log(pool) / Math.LN2) : 0;
}
function strengthLabel(bits) {
    if (bits < 40) return 'Weak';
    if (bits < 60) return 'Fair';
    if (bits < 80) return 'Strong';
    return 'Very strong';
}

function regenerate() {
    if (typeof window === 'undefined') return;
    password.value = generate(length.value, opts(), cryptoRand);
}

const bits = computed(() => entropy(length.value, opts()));
const strengthText = computed(() => (password.value ? `${strengthLabel(bits.value)} (~${Math.round(bits.value)} bits)` : '—'));
const meterWidth = computed(() => `${Math.min(100, (bits.value / 100) * 100)}%`);
const meterColor = computed(() => (bits.value < 40 ? '#e07b6c' : bits.value < 60 ? '#ffd166' : '#7ee2b8'));

onMounted(() => {
    mounted.value = true;
    regenerate();
});

function copy() {
    if (!password.value) return;
    navigator.clipboard?.writeText(password.value).then(() => {
        copyLabel.value = 'Copied!';
        setTimeout(() => (copyLabel.value = 'Copy'), 1500);
    });
}
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="pw-length">Length: <output>{{ length }}</output> characters</label>
                <input id="pw-length" v-model.number="length" type="range" min="6" max="64" @input="regenerate" />
            </div>
            <fieldset>
                <legend>Include characters</legend>
                <label class="choice"><input v-model="lower" type="checkbox" @change="regenerate" /> Lowercase (a–z)</label>
                <label class="choice"><input v-model="upper" type="checkbox" @change="regenerate" /> Uppercase (A–Z)</label>
                <label class="choice"><input v-model="digits" type="checkbox" @change="regenerate" /> Digits (0–9)</label>
                <label class="choice"><input v-model="symbols" type="checkbox" @change="regenerate" /> Symbols (!@#$%…)</label>
            </fieldset>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Your password</h2>
            <p class="password-out">{{ mounted ? (password || 'Select at least one character type') : '—' }}</p>
            <div class="meter" role="img" aria-label="Password strength meter"><span :style="{ width: meterWidth, background: meterColor }"></span></div>
            <p class="result-sub">Strength: <b>{{ mounted ? strengthText : '—' }}</b></p>
            <p>
                <button type="button" class="btn btn-on-dark" @click="copy">{{ copyLabel }}</button>
                <button type="button" class="btn btn-secondary" style="border-color: #fff; color: #fff" @click="regenerate">↻ New password</button>
            </p>
        </div>
    </div>
</template>
