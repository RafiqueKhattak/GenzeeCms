<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    logs: { type: Object, required: true },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Activity Log" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Activity Log</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        <li v-for="log in logs.data" :key="log.id" class="px-4 py-3 text-sm">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ log.user?.name ?? 'System' }}</span>
                            <span class="text-gray-600 dark:text-gray-300"> {{ log.description }}</span>
                            <span class="ml-2 text-xs text-gray-400">{{ new Date(log.created_at).toLocaleString() }}</span>
                        </li>
                        <li v-if="!logs.data.length" class="px-4 py-6 text-center text-sm text-gray-500">No activity yet.</li>
                    </ul>
                </div>
                <Pagination :links="logs.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
