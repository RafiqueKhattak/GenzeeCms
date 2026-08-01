<script setup>
import { computed, ref } from 'vue';

const text = ref('');

function readingLabel(minutes) {
    if (minutes < 1 / 60) return '0 sec';
    if (minutes < 1) return `${Math.ceil(minutes * 60)} sec`;
    return `${Math.floor(minutes)} min ${Math.round((minutes % 1) * 60)} sec`;
}

const stats = computed(() => {
    const trimmed = text.value.trim();
    const words = trimmed ? trimmed.split(/\s+/).length : 0;
    const chars = text.value.length;
    const charsNoSpace = text.value.replace(/\s/g, '').length;
    let sentences = trimmed ? (trimmed.match(/[.!?…]+(\s|$)/g) || []).length : 0;
    if (trimmed && sentences === 0) sentences = 1;
    const paragraphs = trimmed ? trimmed.split(/\n\s*\n/).filter((p) => p.trim()).length : 0;
    const readingMinutes = words / 200;
    return { words, chars, charsNoSpace, sentences, paragraphs, readingMinutes };
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="wc-text">Your text</label>
                <textarea id="wc-text" v-model="text" rows="10" placeholder="Type or paste your text here…"></textarea>
                <p class="hint">Your text stays in your browser — it is never uploaded.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Words</h2>
            <p class="result-big">{{ stats.words.toLocaleString('en-US') }}</p>
            <div class="result-rows">
                <div class="result-row"><span>Characters</span><b>{{ stats.chars.toLocaleString('en-US') }}</b></div>
                <div class="result-row"><span>Characters (no spaces)</span><b>{{ stats.charsNoSpace.toLocaleString('en-US') }}</b></div>
                <div class="result-row"><span>Sentences</span><b>{{ stats.sentences.toLocaleString('en-US') }}</b></div>
                <div class="result-row"><span>Paragraphs</span><b>{{ stats.paragraphs.toLocaleString('en-US') }}</b></div>
                <div class="result-row"><span>Reading time</span><b>{{ readingLabel(stats.readingMinutes) }}</b></div>
            </div>
        </div>
    </div>
</template>
