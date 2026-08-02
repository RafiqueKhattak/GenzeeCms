<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    posts: { type: Object, required: true },
    filters: { type: Object, required: true },
});

const search = ref(props.filters.search ?? '');
const type = ref(props.filters.type ?? '');
const status = ref(props.filters.status ?? '');

watch([search, type, status], ([s, t, st]) => {
    router.get(route('admin.posts.index'), { search: s, type: t, status: st }, { preserveState: true, replace: true });
});

function destroy(post) {
    if (confirm(`Move "${post.title}" to trash?`)) {
        router.delete(route('admin.posts.destroy', post.id));
    }
}

const selected = ref([]);
const allSelected = computed(() => props.posts.data.length > 0 && selected.value.length === props.posts.data.length);

function toggleAll() {
    selected.value = allSelected.value ? [] : props.posts.data.map((p) => p.id);
}

function bulkAction(action) {
    if (!selected.value.length) return;
    if (action === 'delete' && !confirm(`Move ${selected.value.length} post(s) to trash?`)) return;

    router.post(
        route('admin.posts.bulk'),
        { ids: selected.value, action },
        { preserveScroll: true, onSuccess: () => (selected.value = []) },
    );
}

function seoBadgeClass(score) {
    if (score >= 80) return 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200';
    if (score >= 50) return 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200';
    return 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200';
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Posts" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Posts</h2>
                <div class="flex gap-2">
                    <Link :href="route('admin.posts.trash')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Trash</Link>
                    <Link :href="route('admin.posts.create', { type: 'blog' })" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Blog post</Link>
                    <Link :href="route('admin.posts.create', { type: 'news' })" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ News item</Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <input v-model="search" type="text" placeholder="Search posts…" class="w-full max-w-sm rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                    <select v-model="type" class="rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All types</option>
                        <option value="blog">Blog</option>
                        <option value="news">News</option>
                    </select>
                    <select v-model="status" class="rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All statuses</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="published">Published</option>
                    </select>

                    <div v-if="selected.length" class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ selected.length }} selected</span>
                        <button type="button" class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" @click="bulkAction('publish')">Publish</button>
                        <button type="button" class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" @click="bulkAction('draft')">Set draft</button>
                        <button type="button" class="rounded-md border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/30" @click="bulkAction('delete')">Trash</button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="w-8 px-4 py-2"><input type="checkbox" :checked="allSelected" @change="toggleAll" /></th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Title</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Author</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Published</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">SEO</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Views</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="post in posts.data" :key="post.id">
                                <td class="px-4 py-2"><input type="checkbox" :value="post.id" v-model="selected" /></td>
                                <td class="px-4 py-2 text-sm">
                                    <Link :href="route('admin.posts.edit', post.id)" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">{{ post.title }}</Link>
                                    <div class="text-xs text-gray-400">/{{ post.type }}/{{ post.slug }}/</div>
                                </td>
                                <td class="px-4 py-2 text-sm capitalize text-gray-600 dark:text-gray-300">{{ post.type }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ post.author?.name ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="rounded-full px-2 py-0.5 text-xs" :class="post.status === 'published' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' : post.status === 'scheduled' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'">{{ post.status }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ post.published_at?.slice(0, 10) ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="seoBadgeClass(post.seo_score)">{{ post.seo_score }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ post.views }}</td>
                                <td class="px-4 py-2 text-right text-sm">
                                    <Link :href="route('admin.posts.edit', post.id)" class="text-indigo-600 hover:underline dark:text-indigo-400">Edit</Link>
                                    <button type="button" class="ml-3 text-red-600 hover:underline dark:text-red-400" @click="destroy(post)">Trash</button>
                                </td>
                            </tr>
                            <tr v-if="!posts.data.length">
                                <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">No posts found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="posts.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
