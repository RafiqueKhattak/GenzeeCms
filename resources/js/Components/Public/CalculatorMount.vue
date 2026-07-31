<script setup>
import { calculatorRegistry } from '@/Components/Calculators/registry.js';
import ComingSoon from '@/Components/Calculators/ComingSoon.vue';
import { computed, defineAsyncComponent } from 'vue';

const props = defineProps({
    component: { type: String, required: true },
    title: { type: String, required: true },
});

const resolved = computed(() => {
    const loader = calculatorRegistry[props.component];
    return loader ? defineAsyncComponent(loader) : null;
});
</script>

<template>
    <component :is="resolved" v-if="resolved" />
    <ComingSoon v-else :title="title" />
</template>
