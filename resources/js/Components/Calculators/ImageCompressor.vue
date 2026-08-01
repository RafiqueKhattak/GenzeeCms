<script setup>
import { ref } from 'vue';

const quality = ref(75);
const maxWidth = ref(1920);
const status = ref('');
const before = ref('—');
const after = ref('—');
const saved = ref('—');
const dims = ref('—');
const previewSrc = ref(null);
const downloadHref = ref(null);
const downloadName = ref('image-compressed.jpg');

let originalFile = null;

function fmtSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1048576).toFixed(2)} MB`;
}

function compress() {
    if (!originalFile) return;
    const q = quality.value / 100;
    const maxW = maxWidth.value || 0;

    const img = new Image();
    const url = URL.createObjectURL(originalFile);
    img.onload = () => {
        URL.revokeObjectURL(url);
        let w = img.naturalWidth;
        let h = img.naturalHeight;
        if (maxW > 0 && w > maxW) {
            h = Math.round((h * maxW) / w);
            w = maxW;
        }
        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);
        canvas.toBlob(
            (blob) => {
                if (!blob) {
                    status.value = 'Compression failed — try another image.';
                    return;
                }
                const savings = originalFile.size > 0 ? (1 - blob.size / originalFile.size) * 100 : 0;
                before.value = fmtSize(originalFile.size);
                after.value = fmtSize(blob.size);
                saved.value = savings > 0 ? `${savings.toFixed(0)}% smaller` : 'No saving at this quality';
                dims.value = `${w} × ${h} px`;
                previewSrc.value = URL.createObjectURL(blob);
                downloadHref.value = previewSrc.value;
                downloadName.value = `${(originalFile.name || 'image').replace(/\.[^.]+$/, '')}-compressed.jpg`;
                status.value = '';
            },
            'image/jpeg',
            q,
        );
    };
    img.onerror = () => {
        status.value = 'Could not read that file — please choose a JPG, PNG or WebP image.';
    };
    img.src = url;
}

function onFileChange(e) {
    const f = e.target.files[0];
    if (!f) return;
    originalFile = f;
    status.value = 'Compressing…';
    compress();
}
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ic-file">Choose an image</label>
                <input id="ic-file" type="file" accept="image/*" @change="onFileChange" />
            </div>
            <div class="field">
                <label for="ic-quality">Quality: <output>{{ quality }}%</output></label>
                <input id="ic-quality" v-model.number="quality" type="range" min="10" max="95" @input="compress" />
                <p class="hint">Lower quality = smaller file. 70–80% is usually indistinguishable from the original.</p>
            </div>
            <div class="field">
                <label for="ic-maxwidth">Maximum width (px)</label>
                <select id="ic-maxwidth" v-model.number="maxWidth" @change="compress">
                    <option :value="0">Keep original size</option>
                    <option :value="1920">1920 (Full HD)</option>
                    <option :value="1280">1280</option>
                    <option :value="1024">1024</option>
                    <option :value="800">800</option>
                </select>
            </div>
            <p role="status">{{ status }}</p>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Result</h2>
            <div class="result-rows">
                <div class="result-row"><span>Original size</span><b>{{ before }}</b></div>
                <div class="result-row"><span>Compressed size</span><b>{{ after }}</b></div>
                <div class="result-row"><span>Saving</span><b>{{ saved }}</b></div>
                <div class="result-row"><span>Output dimensions</span><b>{{ dims }}</b></div>
            </div>
            <div class="preview-box" style="background: #fff">
                <img v-if="previewSrc" :src="previewSrc" alt="Compressed preview" style="max-height: 18rem" />
                <template v-else>Compressed preview appears here</template>
            </div>
            <p><a v-if="downloadHref" class="btn btn-on-dark" :href="downloadHref" :download="downloadName">⬇ Download compressed image</a></p>
        </div>
    </div>
</template>
