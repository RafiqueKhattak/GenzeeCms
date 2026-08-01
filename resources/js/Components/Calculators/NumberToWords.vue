<script setup>
import { computed, ref } from 'vue';

const ONES = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
const TENS = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

function twoDigits(n) {
    if (n >= 20) return TENS[Math.floor(n / 10)] + (n % 10 ? '-' + ONES[n % 10] : '');
    return ONES[n] || '';
}
function threeDigits(n) {
    const out = [];
    if (n >= 100) {
        out.push(ONES[Math.floor(n / 100)], 'hundred');
        n %= 100;
    }
    if (n > 0) out.push(twoDigits(n));
    return out.join(' ');
}
function cap(s) {
    return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
}
function international(num) {
    if (num === 0) return 'Zero';
    const neg = num < 0;
    num = Math.abs(Math.trunc(num));
    const scales = ['', 'thousand', 'million', 'billion', 'trillion', 'quadrillion'];
    const groups = [];
    while (num > 0) {
        groups.push(num % 1000);
        num = Math.floor(num / 1000);
    }
    const parts = [];
    for (let i = groups.length - 1; i >= 0; i--) {
        if (groups[i] === 0) continue;
        parts.push(threeDigits(groups[i]) + (scales[i] ? ' ' + scales[i] : ''));
    }
    return (neg ? 'Minus ' : '') + cap(parts.join(' ').trim());
}
function indian(num) {
    if (num === 0) return 'Zero';
    const neg = num < 0;
    num = Math.abs(Math.trunc(num));
    const last3 = num % 1000;
    let rest = Math.floor(num / 1000);
    const scales = ['thousand', 'lakh', 'crore', 'arab', 'kharab'];
    const parts = [];
    let idx = 0;
    while (rest > 0) {
        const chunk = rest % 100;
        if (chunk > 0) parts.unshift(twoDigits(chunk) + ' ' + scales[idx]);
        rest = Math.floor(rest / 100);
        idx++;
    }
    if (last3 > 0) parts.push(threeDigits(last3));
    return (neg ? 'Minus ' : '') + cap(parts.join(' ').trim());
}

const input = ref('1234567');
const copyLabels = ref({ intl: 'Copy', indian: 'Copy' });

const result = computed(() => {
    const raw = input.value.replace(/,/g, '').trim();
    if (raw === '' || isNaN(Number(raw))) return null;
    const num = Number(raw);
    if (Math.abs(num) > 1e15) return { tooLarge: true };
    const whole = Math.abs(Math.trunc(num));
    return {
        intl: international(num),
        indianText: indian(num),
        currency: `Rupees ${indian(whole).replace(/^Minus /, '')} only`,
    };
});

function copy(field) {
    const text = field === 'intl' ? result.value?.intl : result.value?.indianText;
    if (!text) return;
    navigator.clipboard?.writeText(text).then(() => {
        copyLabels.value[field] = 'Copied!';
        setTimeout(() => (copyLabels.value[field] = 'Copy'), 1200);
    });
}
</script>

<template>
    <div class="tool-card tool-card-stack">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="nw-value">Number</label>
                <input id="nw-value" v-model="input" type="text" inputmode="decimal" placeholder="e.g. 1234567" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h3>International</h3>
            <p class="result-big" style="font-size: 1.25rem">{{ result ? (result.tooLarge ? 'Number too large' : result.intl) : '—' }}</p>
            <p><button type="button" class="btn btn-on-dark btn-sm" @click="copy('intl')">{{ copyLabels.intl }}</button></p>
            <h3>Indian / Pakistani (lakh, crore)</h3>
            <p class="result-big" style="font-size: 1.25rem">{{ result ? (result.tooLarge ? 'Number too large' : result.indianText) : '—' }}</p>
            <p><button type="button" class="btn btn-on-dark btn-sm" @click="copy('indian')">{{ copyLabels.indian }}</button></p>
            <div class="result-rows"><div class="result-row"><span>Cheque line</span><b style="font-weight: 600">{{ result && !result.tooLarge ? result.currency : '—' }}</b></div></div>
        </div>
    </div>
</template>
