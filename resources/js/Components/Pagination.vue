<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, required: true },
});
</script>

<template>
    <nav v-if="links.length > 3" class="mt-4 flex flex-wrap gap-1">
        <template v-for="(link, i) in links" :key="i">
            <span
                v-if="!link.url"
                class="rounded-md px-3 py-1 text-sm text-gray-400"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="link.active ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
            />
        </template>
    </nav>
</template>
