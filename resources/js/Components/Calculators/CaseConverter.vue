<script setup>
import { ref } from 'vue';

const SMALL_WORDS = ['a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to', 'vs'];

function toTitle(text) {
    return text.toLowerCase().replace(/\b[\w']+/g, (w, i) => {
        if (i !== 0 && SMALL_WORDS.includes(w)) return w;
        return w.charAt(0).toUpperCase() + w.slice(1);
    });
}
function toSentence(text) {
    return text.toLowerCase().replace(/(^\s*\w|[.!?]\s+\w)/g, (c) => c.toUpperCase());
}
function toAlternating(text) {
    let out = '', upper = false;
    for (const ch of text) {
        if (/[a-zA-Z]/.test(ch)) {
            out += upper ? ch.toUpperCase() : ch.toLowerCase();
            upper = !upper;
        } else out += ch;
    }
    return out;
}
function toSlug(text) {
    return text.toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/[\s-]+/g, '-');
}

const MODES = {
    upper: (t) => t.toUpperCase(),
    lower: (t) => t.toLowerCase(),
    title: toTitle,
    sentence: toSentence,
    alternating: toAlternating,
    slug: toSlug,
};

const input = ref('');
const output = ref('');
const copyLabel = ref('Copy result');

function convert(mode) {
    output.value = MODES[mode](input.value);
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
                <label for="cc-in">Your text</label>
                <textarea id="cc-in" v-model="input" rows="6" placeholder="Type or paste text here…"></textarea>
            </div>
            <p>
                <button type="button" class="btn btn-secondary btn-sm" @click="convert('upper')">UPPERCASE</button>
                <button type="button" class="btn btn-secondary btn-sm" @click="convert('lower')">lowercase</button>
                <button type="button" class="btn btn-secondary btn-sm" @click="convert('title')">Title Case</button>
                <button type="button" class="btn btn-secondary btn-sm" @click="convert('sentence')">Sentence case</button>
                <button type="button" class="btn btn-secondary btn-sm" @click="convert('alternating')">aLtErNaTiNg</button>
                <button type="button" class="btn btn-secondary btn-sm" @click="convert('slug')">url-slug</button>
            </p>
            <div class="field">
                <label for="cc-out">Result</label>
                <textarea id="cc-out" v-model="output" rows="6" readonly></textarea>
            </div>
            <p><button type="button" class="btn" @click="copy">{{ copyLabel }}</button></p>
        </form>
    </div>
</template>
