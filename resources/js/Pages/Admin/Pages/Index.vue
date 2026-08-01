<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pages: { type: Array, required: true },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pages" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Static Pages</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        <li v-for="page in pages" :key="page.id" class="flex items-center justify-between px-4 py-3">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ page.title }}</p>
                                <p class="text-xs text-gray-400">/{{ page.slug }}/</p>
                            </div>
                            <Link :href="route('admin.pages.edit', page.id)" class="text-sm text-indigo-600 hover:underline dark:text-indigo-400">Edit</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
