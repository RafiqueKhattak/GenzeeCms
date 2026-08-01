<script setup>
import { onMounted, ref, watch } from 'vue';

const text = ref('https://genzeelogics.com');
const size = ref(256);
const boxEl = ref(null);
const boxMessage = ref('');
const downloadHref = ref(null);
let libraryPromise = null;

function loadLibrary() {
    if (window.QRCode) return Promise.resolve();
    if (libraryPromise) return libraryPromise;
    libraryPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
        script.integrity = 'sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==';
        script.crossOrigin = 'anonymous';
        script.referrerPolicy = 'no-referrer';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
    return libraryPromise;
}

function render() {
    if (typeof window === 'undefined' || !boxEl.value) return;
    const value = text.value.trim();
    boxEl.value.innerHTML = '';
    downloadHref.value = null;
    boxMessage.value = '';
    if (!value) {
        boxMessage.value = 'Type something above to generate a QR code.';
        return;
    }
    loadLibrary()
        .then(() => {
            if (typeof window.QRCode === 'undefined') {
                boxMessage.value = 'QR library failed to load. Please check your connection and reload.';
                return;
            }
            try {
                // eslint-disable-next-line no-new
                new window.QRCode(boxEl.value, {
                    text: value,
                    width: size.value,
                    height: size.value,
                    correctLevel: window.QRCode.CorrectLevel.M,
                });
            } catch {
                boxMessage.value = 'Text is too long for a QR code — try something shorter.';
                return;
            }
            setTimeout(() => {
                const img = boxEl.value.querySelector('img');
                const canvas = boxEl.value.querySelector('canvas');
                downloadHref.value = (img && img.src) || (canvas && canvas.toDataURL('image/png')) || null;
            }, 60);
        })
        .catch(() => {
            boxMessage.value = 'QR library failed to load. Please check your connection and reload.';
        });
}

let timer = null;
watch(text, () => {
    clearTimeout(timer);
    timer = setTimeout(render, 250);
});
watch(size, render);
onMounted(render);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="qr-text">Text or URL</label>
                <textarea id="qr-text" v-model="text" rows="4" placeholder="https://example.com or any text…"></textarea>
            </div>
            <div class="field">
                <label for="qr-size">Size</label>
                <select id="qr-size" v-model.number="size">
                    <option :value="192">Small (192 px)</option>
                    <option :value="256">Medium (256 px)</option>
                    <option :value="384">Large (384 px)</option>
                    <option :value="512">Extra large (512 px)</option>
                </select>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Your QR code</h2>
            <div ref="boxEl" class="preview-box" style="background: #fff; color: var(--ink-muted)">{{ boxMessage }}</div>
            <p><a v-if="downloadHref" class="btn btn-on-dark" :href="downloadHref" download="qr-code.png">⬇ Download PNG</a></p>
        </div>
    </div>
</template>
