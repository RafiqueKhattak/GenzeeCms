<script setup>
import { computed, ref } from 'vue';

const GRADES = [
    ['A / A+', 4.0], ['A-', 3.7], ['B+', 3.3], ['B', 3.0], ['B-', 2.7],
    ['C+', 2.3], ['C', 2.0], ['C-', 1.7], ['D+', 1.3], ['D', 1.0], ['F', 0.0],
];

let nextId = 1;
const rows = ref([
    { id: nextId++, name: '', credits: 3, points: 4.0 },
    { id: nextId++, name: '', credits: 3, points: 4.0 },
    { id: nextId++, name: '', credits: 3, points: 4.0 },
]);
const prevCgpa = ref(null);
const prevCredits = ref(null);

function addRow() {
    rows.value.push({ id: nextId++, name: '', credits: 3, points: 4.0 });
}
function removeRow(id) {
    rows.value = rows.value.filter((r) => r.id !== id);
}

const semester = computed(() => {
    let credits = 0, points = 0;
    for (const r of rows.value) {
        const c = r.credits || 0;
        if (c > 0) {
            credits += c;
            points += c * r.points;
        }
    }
    return { credits, points, gpa: credits === 0 ? NaN : points / credits };
});

const cgpaOut = computed(() => {
    const p = parseFloat(prevCgpa.value);
    const h = parseFloat(prevCredits.value);
    if (!isNaN(p) && !isNaN(h) && h > 0) {
        const total = h + semester.value.credits;
        return total === 0 ? NaN : (p * h + semester.value.points) / total;
    }
    return semester.value.gpa;
});
</script>

<template>
    <div class="tool-card tool-card-stack">
        <form class="tool-form" autocomplete="off" @submit.prevent>
            <div class="table-wrap">
                <table>
                    <caption>This semester's courses</caption>
                    <thead>
                        <tr>
                            <th scope="col" style="width: 40%">Course</th>
                            <th scope="col">Credit hours</th>
                            <th scope="col">Grade</th>
                            <th scope="col"><span class="visually-hidden">Remove</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in rows" :key="row.id">
                            <td><input v-model="row.name" type="text" placeholder="Course name" aria-label="Course name" /></td>
                            <td><input v-model.number="row.credits" type="number" min="0" step="0.5" aria-label="Credit hours" /></td>
                            <td>
                                <select v-model.number="row.points" class="gpa-grade" aria-label="Grade">
                                    <option v-for="[label, pts] in GRADES" :key="label" :value="pts">{{ label }} ({{ pts.toFixed(1) }})</option>
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-ghost btn-sm" aria-label="Remove course" @click="removeRow(row.id)">✕</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p><button type="button" class="btn btn-secondary btn-sm" @click="addRow">+ Add course</button></p>
            <fieldset>
                <legend>CGPA (optional)</legend>
                <div class="field-row">
                    <div class="field"><label for="gpa-prev-cgpa">Previous CGPA</label><input id="gpa-prev-cgpa" v-model.number="prevCgpa" type="number" min="0" max="4" step="0.01" inputmode="decimal" placeholder="e.g. 3.20" /></div>
                    <div class="field"><label for="gpa-prev-credits">Previous credit hours completed</label><input id="gpa-prev-credits" v-model.number="prevCredits" type="number" min="0" step="0.5" inputmode="decimal" placeholder="e.g. 60" /></div>
                </div>
            </fieldset>
        </form>
        <div class="result-panel" aria-live="polite">
            <h2>Semester GPA</h2>
            <p class="result-big">{{ isNaN(semester.gpa) ? '—' : semester.gpa.toFixed(2) }}</p>
            <p class="result-sub">{{ semester.credits }} credit hours</p>
            <div class="result-rows">
                <div class="result-row"><span>Cumulative CGPA</span><b>{{ isNaN(cgpaOut) ? '—' : cgpaOut.toFixed(2) }}</b></div>
            </div>
        </div>
    </div>
</template>
