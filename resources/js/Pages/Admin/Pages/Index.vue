<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pages: { type: Array, required: true },
});

function seoBadgeClass(score) {
    if (score >= 80) return 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200';
    if (score >= 50) return 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200';
    return 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200';
}
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
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Title</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">SEO</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Views</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="page in pages" :key="page.id">
                                <td class="px-4 py-2 text-sm">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ page.title }}</p>
                                    <p class="text-xs text-gray-400">/{{ page.slug }}/</p>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="seoBadgeClass(page.seo_score)">{{ page.seo_score }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ page.views }}</td>
                                <td class="px-4 py-2 text-right text-sm">
                                    <Link :href="route('admin.pages.edit', page.id)" class="text-indigo-600 hover:underline dark:text-indigo-400">Edit</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
