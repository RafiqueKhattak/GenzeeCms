<script setup>
import { ref, watch } from 'vue';

const input = ref('');
const mode = ref('tojson');
const delim = ref(',');
const headers = ref(true);
const output = ref('');
const status = ref('');
const copyLabel = ref('Copy result');

function parseCsv(text, d) {
    const rows = [];
    let row = [];
    let field = '';
    let inQuotes = false;
    for (let i = 0; i < text.length; i++) {
        const c = text.charAt(i);
        if (inQuotes) {
            if (c === '"') {
                if (text.charAt(i + 1) === '"') {
                    field += '"';
                    i++;
                } else inQuotes = false;
            } else field += c;
        } else if (c === '"') {
            inQuotes = true;
        } else if (c === d) {
            row.push(field);
            field = '';
        } else if (c === '\n' || c === '\r') {
            if (c === '\r' && text.charAt(i + 1) === '\n') i++;
            row.push(field);
            field = '';
            rows.push(row);
            row = [];
        } else {
            field += c;
        }
    }
    if (field !== '' || row.length) {
        row.push(field);
        rows.push(row);
    }
    return rows.filter((r) => !(r.length === 1 && r[0] === ''));
}

function csvToJson(text, d, hasHeaders) {
    const rows = parseCsv(text, d);
    if (!rows.length) return [];
    if (!hasHeaders) return rows;
    const hdrs = rows[0];
    return rows.slice(1).map((r) => {
        const obj = {};
        hdrs.forEach((h, i) => (obj[h] = r[i] !== undefined ? r[i] : ''));
        return obj;
    });
}

function escapeField(v, d) {
    const s = String(v === null || v === undefined ? '' : v);
    if (s.includes('"') || s.includes(d) || /[\r\n]/.test(s)) return `"${s.replace(/"/g, '""')}"`;
    return s;
}

function jsonToCsv(data, d) {
    if (!Array.isArray(data)) throw new Error('JSON must be an array');
    if (!data.length) return '';
    if (Array.isArray(data[0])) {
        return data.map((r) => r.map((v) => escapeField(v, d)).join(d)).join('\n');
    }
    const hdrs = [];
    data.forEach((obj) => Object.keys(obj).forEach((k) => { if (!hdrs.includes(k)) hdrs.push(k); }));
    const lines = [hdrs.map((h) => escapeField(h, d)).join(d)];
    data.forEach((obj) => lines.push(hdrs.map((h) => escapeField(obj[h], d)).join(d)));
    return lines.join('\n');
}

function convert() {
    const toJson = mode.value === 'tojson';
    const d = delim.value === 'tab' ? '\t' : delim.value;
    if (!input.value.trim()) {
        output.value = '';
        status.value = '';
        return;
    }
    try {
        if (toJson) {
            output.value = JSON.stringify(csvToJson(input.value, d, headers.value), null, 2);
        } else {
            output.value = jsonToCsv(JSON.parse(input.value), d);
        }
        status.value = '';
    } catch (e) {
        output.value = '';
        status.value = toJson ? 'Could not parse that CSV — check the delimiter setting.' : `Invalid JSON: ${e.message}`;
    }
}

watch([input, mode, delim, headers], convert);

function copy() {
    if (!output.value) return;
    navigator.clipboard?.writeText(output.value).then(() => {
        copyLabel.value = 'Copied!';
        setTimeout(() => (copyLabel.value = 'Copy result'), 1500);
    });
}
</script>

<template>
    <div class="tool-card tool-card-stack">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="cj-in">Input</label>
                <textarea id="cj-in" v-model="input" rows="8" placeholder="name,city&#10;Ali,Karachi&#10;Sara,Lahore"></textarea>
            </div>
            <div class="field-row">
                <div class="field">
                    <span class="segmented" role="group" aria-label="Direction">
                        <input id="cj-tojson" v-model="mode" type="radio" value="tojson" /><label for="cj-tojson">CSV → JSON</label>
                        <input id="cj-tocsv" v-model="mode" type="radio" value="tocsv" /><label for="cj-tocsv">JSON → CSV</label>
                    </span>
                </div>
                <div class="field">
                    <label for="cj-delim">Delimiter</label>
                    <select id="cj-delim" v-model="delim">
                        <option value=",">Comma (,)</option>
                        <option value=";">Semicolon (;)</option>
                        <option value="tab">Tab</option>
                    </select>
                </div>
                <div class="field">
                    <label class="choice" style="margin-top: 1.6rem"><input v-model="headers" type="checkbox" /> First row is headers</label>
                </div>
            </div>
            <div class="field">
                <label for="cj-out">Output</label>
                <textarea id="cj-out" v-model="output" rows="8" readonly></textarea>
                <p class="hint" role="status">{{ status }}</p>
            </div>
            <p><button type="button" class="btn" @click="copy">{{ copyLabel }}</button></p>
        </form>
    </div>
</template>
