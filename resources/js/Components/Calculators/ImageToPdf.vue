<script setup>
import { ref } from 'vue';

const files = ref([]);
const status = ref('');
const building = ref(false);
let libraryPromise = null;

function loadLibrary() {
    if (window.jspdf) return Promise.resolve();
    if (libraryPromise) return libraryPromise;
    libraryPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        script.integrity = 'sha512-qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==';
        script.crossOrigin = 'anonymous';
        script.referrerPolicy = 'no-referrer';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
    return libraryPromise;
}

function readAsDataURL(file) {
    return new Promise((resolve, reject) => {
        const r = new FileReader();
        r.onload = () => resolve(r.result);
        r.onerror = reject;
        r.readAsDataURL(file);
    });
}
function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}

function onFilesChange(e) {
    files.value = Array.from(e.target.files);
    status.value = '';
}

async function build() {
    if (!files.value.length) return;
    building.value = true;
    status.value = 'Building PDF…';
    try {
        await loadLibrary();
        if (typeof window.jspdf === 'undefined') {
            status.value = 'PDF library failed to load — check your connection and reload.';
            building.value = false;
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ unit: 'pt', format: 'a4' });
        const pageW = 595.28, pageH = 841.89, margin = 24;
        for (let i = 0; i < files.value.length; i++) {
            const dataUrl = await readAsDataURL(files.value[i]);
            const img = await loadImage(dataUrl);
            if (i > 0) doc.addPage();
            const maxW = pageW - margin * 2;
            const maxH = pageH - margin * 2;
            const scale = Math.min(maxW / img.naturalWidth, maxH / img.naturalHeight, 1);
            const w = img.naturalWidth * scale;
            const h = img.naturalHeight * scale;
            const type = /png$/i.test(files.value[i].type) ? 'PNG' : 'JPEG';
            doc.addImage(dataUrl, type, (pageW - w) / 2, (pageH - h) / 2, w, h);
        }
        doc.save('images.pdf');
        status.value = 'Done — your PDF has downloaded.';
    } catch (e) {
        status.value = `Could not build the PDF: ${e.message || 'unsupported image'}`;
    }
    building.value = false;
}
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="ip-files">Choose images (you can select several)</label>
                <input id="ip-files" type="file" accept="image/png,image/jpeg" multiple @change="onFilesChange" />
                <p class="hint">Pages follow the selection order shown below.</p>
            </div>
            <div class="field">
                <div class="preview-box" style="min-height: 6rem; text-align: left">
                    <template v-if="!files.length">No images selected yet.</template>
                    <ol v-else>
                        <li v-for="(f, i) in files" :key="i">{{ f.name }} ({{ (f.size / 1024).toFixed(0) }} KB)</li>
                    </ol>
                </div>
            </div>
            <p role="status">{{ status }}</p>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Build your PDF</h2>
            <p class="result-sub">Each image is scaled to fit an A4 page with a small margin, keeping its proportions. The PDF downloads straight to your device.</p>
            <p><button type="button" class="btn btn-on-dark" :disabled="!files.length || building" @click="build">📄 Create PDF</button></p>
        </div>
    </div>
</template>
