<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    filters: { type: Object, required: true },
    suggestions: { type: Object, required: true },
    counts: { type: Object, required: true },
    newsApiConfigured: { type: Boolean, required: true },
    lastFetchedAt: { type: String, default: null },
});

const addForm = useForm({ keyword: '', suggested_type: 'blog', context: '' });

function add() {
    addForm.post(route('admin.keywords.store'), {
        preserveScroll: true,
        onSuccess: () => addForm.reset(),
    });
}

function fetchNews() {
    router.post(route('admin.keywords.fetch'), {}, { preserveScroll: true });
}

function setStatus(suggestion, status) {
    router.put(route('admin.keywords.update', suggestion.id), { status }, { preserveScroll: true });
}

function remove(suggestion) {
    if (confirm(`Remove "${suggestion.keyword}" from the list?`)) {
        router.delete(route('admin.keywords.destroy', suggestion.id), { preserveScroll: true });
    }
}

function show(status) {
    router.get(route('admin.keywords.index'), { status }, { preserveState: true, replace: true });
}

/** Prefills the post editor from a suggestion, and marks it used on arrival. */
function createFrom(suggestion) {
    router.get(route('admin.posts.create'), {
        type: suggestion.suggested_type === 'tool' ? 'blog' : suggestion.suggested_type,
        keyword_id: suggestion.id,
    });
}

function relevanceClass(score) {
    if (score >= 45) return 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200';
    if (score >= 25) return 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200';
    return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Keyword ideas" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Keyword ideas</h2>
                <div class="flex gap-2">
                    <a
                        :href="route('admin.keywords.export', { status: filters.status })"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Export to Excel
                    </a>
                    <button
                        type="button"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        :disabled="!newsApiConfigured"
                        :title="newsApiConfigured ? '' : 'Set NEWS_API_KEY in .env to enable'"
                        @click="fetchNews"
                    >
                        Fetch latest headlines
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div v-if="!newsApiConfigured" class="rounded-md bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                    <strong>Automatic fetching is off.</strong> Add a free API key from
                    <span class="font-mono">newsapi.org</span> as <span class="font-mono">NEWS_API_KEY</span> in your
                    <span class="font-mono">.env</span> to pull headlines automatically. You can still add keywords
                    manually below — for example from Google Trends.
                </div>

                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-1 font-semibold text-gray-900 dark:text-gray-100">Add a keyword manually</h3>
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                        Spotted something on Google Trends worth covering? Add it here and it joins the queue below.
                    </p>
                    <form class="flex flex-wrap items-end gap-3" @submit.prevent="add">
                        <div class="min-w-[16rem] flex-1">
                            <label class="block text-xs font-medium uppercase text-gray-500">Keyword or headline</label>
                            <input v-model="addForm.keyword" type="text" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium uppercase text-gray-500">Type</label>
                            <select v-model="addForm.suggested_type" class="mt-1 rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <option value="blog">Blog</option>
                                <option value="news">News</option>
                                <option value="tool">Tool idea</option>
                            </select>
                        </div>
                        <button type="submit" :disabled="addForm.processing" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-gray-200 dark:text-gray-800">Add</button>
                    </form>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <button v-for="s in ['new', 'used', 'dismissed']" :key="s" type="button"
                        class="rounded-md px-3 py-1.5 capitalize"
                        :class="filters.status === s ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-700 dark:border-gray-600 dark:text-gray-300'"
                        @click="show(s)">
                        {{ s }} ({{ counts[s] }})
                    </button>
                    <span v-if="lastFetchedAt" class="ml-auto text-xs text-gray-400">
                        Last fetched: {{ lastFetchedAt.slice(0, 16).replace('T', ' ') }}
                    </span>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Keyword / headline</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Fit</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Source</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="s in suggestions.data" :key="s.id">
                                <td class="px-4 py-3 text-sm">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ s.keyword }}</p>
                                    <p v-if="s.context" class="mt-0.5 text-xs text-gray-500">{{ s.context }}</p>
                                    <p v-if="s.used_post" class="mt-1 text-xs text-green-600 dark:text-green-400">
                                        Used in: {{ s.used_post.title }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs" :class="relevanceClass(s.relevance)">{{ s.relevance }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm capitalize text-gray-600 dark:text-gray-300">{{ s.suggested_type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <a v-if="s.source_url" :href="s.source_url" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline dark:text-indigo-400">{{ s.source }}</a>
                                    <span v-else>{{ s.source }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                    <button v-if="s.status === 'new'" type="button" class="text-indigo-600 hover:underline dark:text-indigo-400" @click="createFrom(s)">Write this</button>
                                    <button v-if="s.status === 'new'" type="button" class="ml-3 text-gray-500 hover:underline" @click="setStatus(s, 'dismissed')">Dismiss</button>
                                    <button v-if="s.status !== 'new'" type="button" class="text-gray-500 hover:underline" @click="setStatus(s, 'new')">Restore</button>
                                    <button type="button" class="ml-3 text-red-600 hover:underline dark:text-red-400" @click="remove(s)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!suggestions.data.length">
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                    Nothing here yet. Add a keyword above{{ newsApiConfigured ? ' or fetch the latest headlines' : '' }}.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="suggestions.links" />

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    "Fit" scores how well a keyword matches this site's subject area (money, tax, calculators,
                    generations). Trending feeds are mostly sport and celebrity news — anything scoring low is
                    off-topic and would dilute the site's focus rather than help it rank.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
