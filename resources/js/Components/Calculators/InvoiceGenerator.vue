<script setup>
import { computed, onMounted, ref } from 'vue';

const number = ref('INV-001');
const date = ref('');
const currency = ref('PKR');
const taxPct = ref(0);
const from = ref('');
const to = ref('');
const notes = ref('');

let nextId = 1;
const items = ref([{ id: nextId++, desc: '', qty: 1, rate: 0 }]);

function addRow() {
    items.value.push({ id: nextId++, desc: '', qty: 1, rate: 0 });
}
function removeRow(id) {
    items.value = items.value.filter((i) => i.id !== id);
}

function fmt(x) {
    return `${currency.value} ${x.toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
function amount(item) {
    return (item.qty || 0) * (item.rate || 0);
}

const totals = computed(() => {
    const subtotal = items.value.reduce((sum, it) => sum + amount(it), 0);
    const tax = (subtotal * (taxPct.value || 0)) / 100;
    return { subtotal, tax, total: subtotal + tax };
});

function print() {
    window.print();
}

onMounted(() => {
    date.value = new Date().toISOString().slice(0, 10);
});
</script>

<template>
    <div class="tool-card tool-card-stack">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field-row">
                <div class="field"><label for="inv-number">Invoice number</label><input id="inv-number" v-model="number" type="text" /></div>
                <div class="field"><label for="inv-date">Invoice date</label><input id="inv-date" v-model="date" type="date" /></div>
                <div class="field"><label for="inv-currency">Currency</label><input id="inv-currency" v-model="currency" type="text" maxlength="5" /></div>
                <div class="field"><label for="inv-tax">Tax rate (%)</label><input id="inv-tax" v-model.number="taxPct" type="number" min="0" max="100" step="any" inputmode="decimal" /></div>
            </div>
            <div class="field-row">
                <div class="field"><label for="inv-from">From (your business)</label><textarea id="inv-from" v-model="from" rows="3" placeholder="Your name / business&#10;Address&#10;Phone / email"></textarea></div>
                <div class="field"><label for="inv-to">Bill to (client)</label><textarea id="inv-to" v-model="to" rows="3" placeholder="Client name&#10;Address&#10;Phone / email"></textarea></div>
            </div>
            <div class="table-wrap line-items">
                <table>
                    <caption>Line items</caption>
                    <thead><tr><th scope="col" style="width: 45%">Description</th><th scope="col">Qty</th><th scope="col">Rate</th><th scope="col" class="num">Amount</th><th scope="col"><span class="visually-hidden">Remove</span></th></tr></thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td><input v-model="item.desc" type="text" aria-label="Item description" placeholder="Description" /></td>
                            <td><input v-model.number="item.qty" type="number" aria-label="Quantity" min="0" step="any" /></td>
                            <td><input v-model.number="item.rate" type="number" aria-label="Rate" min="0" step="any" /></td>
                            <td class="num">{{ amount(item).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                            <td><button type="button" class="btn btn-ghost btn-sm" aria-label="Remove row" @click="removeRow(item.id)">✕</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p><button type="button" class="btn btn-secondary btn-sm" @click="addRow">+ Add item</button></p>
            <div class="field">
                <label for="inv-notes">Notes / payment terms</label>
                <textarea id="inv-notes" v-model="notes" rows="2" placeholder="e.g. Payment within 14 days. Bank: … Account: …"></textarea>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Totals</h2>
            <div class="result-rows">
                <div class="result-row"><span>Subtotal</span><b>{{ fmt(totals.subtotal) }}</b></div>
                <div class="result-row"><span>Tax ({{ taxPct }}%)</span><b>{{ fmt(totals.tax) }}</b></div>
                <div class="result-row invoice-grand"><span>Total due</span><b>{{ fmt(totals.total) }}</b></div>
            </div>
            <p><button type="button" class="btn btn-on-dark" @click="print">🖨 Print / Save as PDF</button></p>
        </div>
    </div>
    <section class="invoice-sheet" aria-label="Invoice preview (printable)">
        <div class="invoice-head">
            <div>
                <h2 style="margin: 0">INVOICE</h2>
                <p style="margin: 0; color: var(--ink-muted)"># <span>{{ number }}</span><br />Date: <span>{{ date }}</span></p>
            </div>
        </div>
        <div class="invoice-parties">
            <div><h3 style="margin-top: 0">From</h3><p style="white-space: pre-line">{{ from }}</p></div>
            <div><h3 style="margin-top: 0">Bill to</h3><p style="white-space: pre-line">{{ to }}</p></div>
        </div>
        <p class="hint no-print">The line items and totals above are included when you print. Use your browser's <strong>Print → Save as PDF</strong> for a file you can email.</p>
        <p style="white-space: pre-line; color: var(--ink-muted)">{{ notes }}</p>
    </section>
</template>
