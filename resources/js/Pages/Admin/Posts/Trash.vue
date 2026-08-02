<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    posts: { type: Object, required: true },
});

function restore(post) {
    router.post(route('admin.posts.restore', post.id));
}

function forceDelete(post) {
    if (confirm(`Permanently delete "${post.title}"? This cannot be undone.`)) {
        router.delete(route('admin.posts.force-delete', post.id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Posts — Trash" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Posts — Trash</h2>
                <Link :href="route('admin.posts.index')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Back to Posts</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Trashed posts are hidden from the public site, but aren't permanently gone until you delete them
                    here.
                </p>

                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Title</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Author</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Deleted</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="post in posts.data" :key="post.id">
                                <td class="px-4 py-2 text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ post.title }}</span>
                                    <div class="text-xs text-gray-400">/{{ post.type }}/{{ post.slug }}/</div>
                                </td>
                                <td class="px-4 py-2 text-sm capitalize text-gray-600 dark:text-gray-300">{{ post.type }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ post.author?.name ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ post.deleted_at?.slice(0, 10) }}</td>
                                <td class="px-4 py-2 text-right text-sm">
                                    <button type="button" class="text-indigo-600 hover:underline dark:text-indigo-400" @click="restore(post)">Restore</button>
                                    <button type="button" class="ml-3 text-red-600 hover:underline dark:text-red-400" @click="forceDelete(post)">Delete permanently</button>
                                </td>
                            </tr>
                            <tr v-if="!posts.data.length">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Trash is empty.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="posts.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
