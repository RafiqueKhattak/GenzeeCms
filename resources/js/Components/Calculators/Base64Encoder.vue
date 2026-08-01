<script setup>
import { ref } from 'vue';

const input = ref('');
const output = ref('');
const status = ref('');
const copyLabel = ref('Copy result');

function encode(str) {
    const bytes = new TextEncoder().encode(str);
    let bin = '';
    for (const b of bytes) bin += String.fromCharCode(b);
    return btoa(bin);
}
function decode(b64) {
    const bin = atob(b64.replace(/\s/g, ''));
    const bytes = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
    return new TextDecoder().decode(bytes);
}

function run(encoding) {
    if (!input.value.trim()) {
        output.value = '';
        status.value = '';
        return;
    }
    try {
        output.value = encoding ? encode(input.value) : decode(input.value);
        status.value = '';
    } catch {
        output.value = '';
        status.value = 'That input is not valid Base64 — check for missing characters.';
    }
}

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
                <label for="b64-in">Input</label>
                <textarea id="b64-in" v-model="input" rows="6" placeholder="Text to encode, or Base64 to decode…"></textarea>
            </div>
            <p>
                <button type="button" class="btn" @click="run(true)">Encode →</button>
                <button type="button" class="btn btn-secondary" @click="run(false)">← Decode</button>
            </p>
            <div class="field">
                <label for="b64-out">Output</label>
                <textarea id="b64-out" v-model="output" rows="6" readonly></textarea>
                <p class="hint" role="status">{{ status }}</p>
            </div>
            <p><button type="button" class="btn btn-ghost" @click="copy">{{ copyLabel }}</button></p>
        </form>
    </div>
</template>
