<script setup>
import { computed, ref } from 'vue';

const monthlyRent = ref(60000);
const homePrice = ref(15000000);
const downPaymentPct = ref(20);
const mortgageApr = ref(14);
const mortgageYears = ref(20);
const annualMaintenancePct = ref(1);
const annualAppreciationPct = ref(6);
const horizonYears = ref(10);
const rentAnnualIncreasePct = ref(8);

function fmt(x) {
    return `PKR ${Math.round(x).toLocaleString('en-PK')}`;
}

const downPayment = computed(() => homePrice.value * (downPaymentPct.value / 100));
const loanAmount = computed(() => homePrice.value - downPayment.value);

const mortgagePayment = computed(() => {
    const n = mortgageYears.value * 12;
    const r = mortgageApr.value / 100 / 12;
    if (n <= 0) return 0;
    if (r === 0) return loanAmount.value / n;
    return (loanAmount.value * r) / (1 - Math.pow(1 + r, -n));
});

// Total cost of renting over the horizon, with rent rising each year.
const totalRentCost = computed(() => {
    let total = 0;
    let rent = monthlyRent.value;
    for (let y = 0; y < horizonYears.value; y++) {
        total += rent * 12;
        rent *= 1 + rentAnnualIncreasePct.value / 100;
    }
    return total;
});

// Remaining mortgage balance after `months` payments — needed because the
// buyer may still owe money at the end of the comparison horizon if it's
// shorter than the mortgage term itself.
function remainingBalance(months) {
    const n = mortgageYears.value * 12;
    const r = mortgageApr.value / 100 / 12;
    const k = Math.min(months, n);
    if (r === 0) return Math.max(0, loanAmount.value - mortgagePayment.value * k);
    const balance = loanAmount.value * Math.pow(1 + r, k) - mortgagePayment.value * ((Math.pow(1 + r, k) - 1) / r);
    return Math.max(0, balance);
}

// Net cost of buying: cash paid out (down payment + mortgage payments +
// maintenance) minus net equity at the end of the horizon (the home's
// appreciated value minus whatever mortgage balance is still owed).
const totalBuyCost = computed(() => {
    const monthsInHorizon = horizonYears.value * 12;
    const paymentsPaid = mortgagePayment.value * Math.min(monthsInHorizon, mortgageYears.value * 12);
    const maintenance = homePrice.value * (annualMaintenancePct.value / 100) * horizonYears.value;
    const futureValue = homePrice.value * Math.pow(1 + annualAppreciationPct.value / 100, horizonYears.value);
    const owedAtEnd = remainingBalance(monthsInHorizon);
    const netEquity = futureValue - owedAtEnd;
    return downPayment.value + paymentsPaid + maintenance - netEquity;
});

const valid = computed(() => homePrice.value > 0 && monthlyRent.value >= 0);
const cheaperOption = computed(() => (totalRentCost.value <= totalBuyCost.value ? 'Renting' : 'Buying'));
const difference = computed(() => Math.abs(totalRentCost.value - totalBuyCost.value));
</script>

<template>
    <div class="tool-card">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="field">
                <label for="rb-rent">Current monthly rent (PKR)</label>
                <input id="rb-rent" v-model.number="monthlyRent" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="rb-price">Home price if buying (PKR)</label>
                <input id="rb-price" v-model.number="homePrice" type="number" min="0" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="rb-down">Down payment: <output>{{ downPaymentPct }}%</output></label>
                <input id="rb-down" v-model.number="downPaymentPct" type="range" min="0" max="100" step="5" />
            </div>
            <div class="field">
                <label for="rb-apr">Mortgage interest rate (%)</label>
                <input id="rb-apr" v-model.number="mortgageApr" type="number" min="0" max="40" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="rb-horizon">Years you plan to stay</label>
                <input id="rb-horizon" v-model.number="horizonYears" type="number" min="1" max="40" step="1" inputmode="numeric" />
            </div>
            <div class="field">
                <label for="rb-appreciation">Expected annual home appreciation (%)</label>
                <input id="rb-appreciation" v-model.number="annualAppreciationPct" type="number" min="0" max="30" step="any" inputmode="decimal" />
            </div>
            <div class="field">
                <label for="rb-rent-increase">Expected annual rent increase (%)</label>
                <input id="rb-rent-increase" v-model.number="rentAnnualIncreasePct" type="number" min="0" max="30" step="any" inputmode="decimal" />
            </div>
        </form>
        <div class="result-panel" aria-live="polite">
            <template v-if="!valid">
                <h2>Cheaper option</h2>
                <p class="result-big">—</p>
            </template>
            <template v-else>
                <h2>{{ cheaperOption }} looks cheaper over {{ horizonYears }} years</h2>
                <p class="result-big">{{ fmt(difference) }} difference</p>
                <div class="result-rows">
                    <div class="result-row"><span>Total cost of renting</span><b>{{ fmt(totalRentCost) }}</b></div>
                    <div class="result-row"><span>Net cost of buying (after home value)</span><b>{{ fmt(totalBuyCost) }}</b></div>
                </div>
            </template>
        </div>
    </div>
</template>
