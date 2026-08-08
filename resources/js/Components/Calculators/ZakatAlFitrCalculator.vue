<script setup>
import { computed, ref } from 'vue';

const ratePerPerson = ref(340);
const familyMembers = ref(5);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const valid = computed(() => ratePerPerson.value >= 0 && familyMembers.value >= 0);
const totalDue = computed(() => ratePerPerson.value * familyMembers.value);
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="zf-rate">Fitrana rate per person (PKR)</label>
                <input id="zf-rate" v-model.number="ratePerPerson" type="number" min="0" step="any" inputmode="decimal" />
                <p class="hint">This is announced fresh each Ramadan by local religious authorities, based on the price of a staple food (commonly wheat/flour). Check the current year's announced rate — do not reuse last year's figure.</p>
            </div>
            <div class="field">
                <label for="zf-members">Number of family members</label>
                <input id="zf-members" v-model.number="familyMembers" type="number" min="0" max="50" step="1" inputmode="numeric" />
                <p class="hint">Include everyone in your household you're financially responsible for, including yourself.</p>
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Total Zakat al-Fitr (Fitrana) due</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>Total Zakat al-Fitr (Fitrana) due</h2>
                <p class="result-big">{{ fmt(totalDue) }}</p>
                <p class="result-sub">{{ familyMembers }} people at {{ fmt(ratePerPerson) }} each</p>
            </template>
        </div>
    </div>
</template>
