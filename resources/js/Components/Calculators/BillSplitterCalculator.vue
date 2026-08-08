<script setup>
import { computed, ref } from 'vue';

const people = ref([
    { name: 'Person 1', paid: 5000 },
    { name: 'Person 2', paid: 0 },
    { name: 'Person 3', paid: 1500 },
]);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

function addPerson() {
    people.value.push({ name: `Person ${people.value.length + 1}`, paid: 0 });
}
function removePerson(i) {
    people.value.splice(i, 1);
}

const total = computed(() => people.value.reduce((sum, p) => sum + (p.paid || 0), 0));
const fairShare = computed(() => (people.value.length > 0 ? total.value / people.value.length : 0));

// Positive balance = owed money (paid more than their share);
// negative = owes money (paid less than their share).
const balances = computed(() =>
    people.value.map((p) => ({ name: p.name, balance: (p.paid || 0) - fairShare.value }))
);

// Simple greedy settlement: match the biggest debtor with the biggest
// creditor repeatedly until everyone is settled — minimizes number of
// transactions needed, which is the standard approach for this problem.
const settlements = computed(() => {
    const creditors = balances.value.filter((b) => b.balance > 0.5).map((b) => ({ ...b }));
    const debtors = balances.value.filter((b) => b.balance < -0.5).map((b) => ({ ...b, balance: -b.balance }));
    const result = [];

    let ci = 0;
    let di = 0;
    while (ci < creditors.length && di < debtors.length) {
        const amount = Math.min(creditors[ci].balance, debtors[di].balance);
        result.push({ from: debtors[di].name, to: creditors[ci].name, amount });
        creditors[ci].balance -= amount;
        debtors[di].balance -= amount;
        if (creditors[ci].balance < 0.5) ci++;
        if (debtors[di].balance < 0.5) di++;
    }
    return result;
});
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div v-for="(person, i) in people" :key="i" class="field">
                <label :for="`bs-name-${i}`">Person {{ i + 1 }}</label>
                <div class="split-row">
                    <input :id="`bs-name-${i}`" v-model="person.name" type="text" placeholder="Name" />
                    <input v-model.number="person.paid" type="number" min="0" step="any" inputmode="decimal" placeholder="Amount paid" />
                    <button type="button" class="btn btn-secondary btn-sm" aria-label="Remove person" @click="removePerson(i)">✕</button>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" @click="addPerson">+ Add person</button>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Fair share per person</h2>
            <p class="result-big">{{ fmt(fairShare) }}</p>
            <p class="result-sub">Total bill: {{ fmt(total) }} split {{ people.length }} ways</p>
            <div v-if="settlements.length" class="result-rows">
                <div v-for="(s, i) in settlements" :key="i" class="result-row">
                    <span>{{ s.from }} owes {{ s.to }}</span>
                    <b>{{ fmt(s.amount) }}</b>
                </div>
            </div>
            <p v-else class="result-sub">Everyone's already even — no payments needed.</p>
        </div>
    </div>
</template>

<style scoped>
.split-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 0.5rem;
    align-items: center;
}
</style>
