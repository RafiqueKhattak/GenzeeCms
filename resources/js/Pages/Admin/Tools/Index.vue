<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    tools: { type: Object, required: true },
    filters: { type: Object, required: true },
});

const search = useForm({ search: props.filters.search ?? '' });

watch(
    () => search.search,
    (value) => {
        router.get(route('admin.tools.index'), { search: value }, { preserveState: true, replace: true });
    },
);

function destroy(tool) {
    if (confirm(`Delete "${tool.title}"? This cannot be undone.`)) {
        router.delete(route('admin.tools.destroy', tool.id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tools" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Tools</h2>
                <Link :href="route('admin.tools.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ New tool</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4">
                    <input v-model="search.search" type="text" placeholder="Search tools…" class="w-full max-w-sm rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Title</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Component</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="tool in tools.data" :key="tool.id">
                                <td class="px-4 py-2 text-sm">
                                    <Link :href="route('admin.tools.edit', tool.id)" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">{{ tool.title }}</Link>
                                    <div class="text-xs text-gray-400">/{{ tool.slug }}/</div>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ tool.category?.name ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="rounded-full px-2 py-0.5 text-xs" :class="tool.status === 'published' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'">{{ tool.status }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ tool.component }}</td>
                                <td class="px-4 py-2 text-right text-sm">
                                    <Link :href="route('admin.tools.edit', tool.id)" class="text-indigo-600 hover:underline dark:text-indigo-400">Edit</Link>
                                    <button type="button" class="ml-3 text-red-600 hover:underline dark:text-red-400" @click="destroy(tool)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!tools.data.length">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No tools found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="tools.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
