<script setup>
import { computed, ref } from 'vue';

const format = ref('image/jpeg');
const quality = ref(85);
const status = ref('');
const before = ref('—');
const after = ref('—');
const previewSrc = ref(null);
const downloadHref = ref(null);
const downloadName = ref('image.jpg');

let file = null;
const lossy = computed(() => format.value !== 'image/png');

function fmtSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1048576).toFixed(2)} MB`;
}

function convert() {
    if (!file) return;
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
        URL.revokeObjectURL(url);
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx = canvas.getContext('2d');
        if (format.value === 'image/jpeg') {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
        ctx.drawImage(img, 0, 0);
        canvas.toBlob(
            (blob) => {
                if (!blob) {
                    status.value = 'Conversion failed — try another image.';
                    return;
                }
                const ext = format.value === 'image/png' ? 'png' : format.value === 'image/webp' ? 'webp' : 'jpg';
                before.value = `${fmtSize(file.size)} (${file.type.split('/')[1] || '?'})`;
                after.value = `${fmtSize(blob.size)} (${ext})`;
                previewSrc.value = URL.createObjectURL(blob);
                downloadHref.value = previewSrc.value;
                downloadName.value = `${(file.name || 'image').replace(/\.[^.]+$/, '')}.${ext}`;
                status.value = '';
            },
            format.value,
            lossy.value ? quality.value / 100 : undefined,
        );
    };
    img.onerror = () => {
        status.value = 'Could not read that file — choose a PNG, JPG or WebP image.';
    };
    img.src = url;
}

function onFileChange(e) {
    file = e.target.files[0] || null;
    if (file) convert();
}
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="if-file">Choose an image</label>
                <input id="if-file" type="file" accept="image/*" @change="onFileChange" />
            </div>
            <div class="field">
                <label for="if-format">Convert to</label>
                <select id="if-format" v-model="format" @change="convert">
                    <option value="image/jpeg">JPG</option>
                    <option value="image/png">PNG</option>
                    <option value="image/webp">WebP</option>
                </select>
            </div>
            <div class="field" :style="{ opacity: lossy ? 1 : 0.4 }">
                <label for="if-quality">Quality: <output>{{ quality }}%</output></label>
                <input id="if-quality" v-model.number="quality" type="range" min="10" max="100" @input="convert" />
                <p class="hint">Applies to JPG and WebP; PNG is always lossless.</p>
            </div>
            <p role="status">{{ status }}</p>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Result</h2>
            <div class="result-rows" style="margin-top: 0">
                <div class="result-row"><span>Original</span><b>{{ before }}</b></div>
                <div class="result-row"><span>Converted</span><b>{{ after }}</b></div>
            </div>
            <div class="preview-box" style="background: #fff">
                <img v-if="previewSrc" :src="previewSrc" alt="Converted image preview" style="max-height: 16rem" />
                <template v-else>Converted preview appears here</template>
            </div>
            <p><a v-if="downloadHref" class="btn btn-on-dark" :href="downloadHref" :download="downloadName">⬇ Download converted image</a></p>
        </div>
    </div>
</template>
