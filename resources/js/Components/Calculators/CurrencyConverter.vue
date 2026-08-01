<script setup>
import { onMounted, ref, watch } from 'vue';

const CURRENCIES = [
    ['USD', 'US Dollar'], ['EUR', 'Euro'], ['GBP', 'British Pound'], ['PKR', 'Pakistani Rupee'],
    ['INR', 'Indian Rupee'], ['AED', 'UAE Dirham'], ['SAR', 'Saudi Riyal'], ['CAD', 'Canadian Dollar'],
    ['AUD', 'Australian Dollar'], ['JPY', 'Japanese Yen'], ['CNY', 'Chinese Yuan'], ['CHF', 'Swiss Franc'],
    ['SGD', 'Singapore Dollar'], ['NZD', 'New Zealand Dollar'], ['ZAR', 'South African Rand'], ['TRY', 'Turkish Lira'],
    ['MYR', 'Malaysian Ringgit'], ['THB', 'Thai Baht'], ['IDR', 'Indonesian Rupiah'], ['PHP', 'Philippine Peso'],
    ['BDT', 'Bangladeshi Taka'], ['NGN', 'Nigerian Naira'], ['EGP', 'Egyptian Pound'], ['KRW', 'South Korean Won'],
    ['HKD', 'Hong Kong Dollar'], ['QAR', 'Qatari Riyal'], ['KWD', 'Kuwaiti Dinar'], ['OMR', 'Omani Rial'],
    ['BHD', 'Bahraini Dinar'], ['MXN', 'Mexican Peso'], ['BRL', 'Brazilian Real'],
];

const API_BASE = 'https://open.er-api.com/v6/latest/';

const amount = ref(100);
const from = ref('USD');
const to = ref('PKR');
const result = ref(null);
const rateText = ref('—');
const inverseText = ref('—');
const status = ref('');

const cache = {};
let requestCounter = 0;

function fmt(x) {
    if (x === null || x === undefined || !isFinite(x)) return '—';
    const abs = Math.abs(x);
    const digits = abs >= 100 ? 2 : abs >= 1 ? 4 : 6;
    return x.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: digits });
}

function fetchRates(base) {
    if (cache[base]) return Promise.resolve(cache[base]);
    return fetch(API_BASE + encodeURIComponent(base))
        .then((res) => {
            if (!res.ok) throw new Error(`Request failed (${res.status})`);
            return res.json();
        })
        .then((data) => {
            if (data.result !== 'success' || !data.rates) throw new Error('Unexpected response');
            const entry = { rates: data.rates, time: data.time_last_update_utc || '' };
            cache[base] = entry;
            return entry;
        });
}

function update() {
    if (typeof window === 'undefined') return;
    const requestId = ++requestCounter;
    status.value = 'Fetching live rates…';
    fetchRates(from.value)
        .then((entry) => {
            if (requestId !== requestCounter) return;
            const rate = entry.rates[to.value];
            if (!rate) throw new Error(`No rate available for ${to.value}`);
            const amt = isFinite(amount.value) ? amount.value : 0;
            result.value = `${fmt(amt * rate)} ${to.value}`;
            rateText.value = `1 ${from.value} = ${fmt(rate)} ${to.value}`;
            inverseText.value = `1 ${to.value} = ${fmt(1 / rate)} ${from.value}`;
            status.value = entry.time ? `Rates last updated ${entry.time}` : 'Live reference rates';
        })
        .catch(() => {
            result.value = null;
            rateText.value = '—';
            inverseText.value = '—';
            status.value = "Couldn't fetch live rates — check your connection and try again.";
        });
}

function swap() {
    [from.value, to.value] = [to.value, from.value];
}

watch([amount, from, to], update);
onMounted(update);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="cc-amount">Amount</label>
                <input id="cc-amount" v-model.number="amount" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field field-row">
                <div class="field">
                    <label for="cc-from">From</label>
                    <select id="cc-from" v-model="from">
                        <option v-for="[code, name] in CURRENCIES" :key="code" :value="code">{{ code }} — {{ name }}</option>
                    </select>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" aria-label="Swap currencies" @click="swap">⇄</button>
                <div class="field">
                    <label for="cc-to">To</label>
                    <select id="cc-to" v-model="to">
                        <option v-for="[code, name] in CURRENCIES" :key="code" :value="code">{{ code }} — {{ name }}</option>
                    </select>
                </div>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Converted amount</h2>
            <p class="result-big">{{ result ?? '—' }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Rate</span><b>{{ rateText }}</b></div>
                <div class="result-row"><span>Inverse</span><b>{{ inverseText }}</b></div>
            </div>
            <p class="result-sub" role="status">{{ status }}</p>
        </div>
    </div>
</template>
