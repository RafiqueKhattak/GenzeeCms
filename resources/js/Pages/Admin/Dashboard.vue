<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: { type: Object, required: true },
    recentActivity: { type: Array, required: true },
    recentPosts: { type: Array, required: true },
});

const cards = (stats) => [
    { label: 'Published tools', value: `${stats.toolsPublished} / ${stats.tools}` },
    { label: 'Blog posts', value: stats.blogPosts },
    { label: 'News posts', value: stats.newsPosts },
    { label: 'Drafts', value: stats.drafts },
    { label: 'Scheduled', value: stats.scheduled },
    { label: 'Team members', value: stats.users },
];
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                    <div v-for="card in cards(stats)" :key="card.label" class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ card.value }}</p>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Recently updated posts</h3>
                        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                            <li v-for="post in recentPosts" :key="post.id" class="flex items-center justify-between py-2 text-sm">
                                <Link :href="route('admin.posts.edit', post.id)" class="text-indigo-600 hover:underline dark:text-indigo-400">{{ post.title }}</Link>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs capitalize text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ post.type }} · {{ post.status }}</span>
                            </li>
                            <li v-if="!recentPosts.length" class="py-2 text-sm text-gray-500">No posts yet.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Recent activity</h3>
                        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                            <li v-for="log in recentActivity" :key="log.id" class="py-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ log.user?.name ?? 'System' }}</span> {{ log.description }}
                            </li>
                            <li v-if="!recentActivity.length" class="py-2 text-sm text-gray-500">No activity yet.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link :href="route('admin.tools.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ New tool</Link>
                    <Link :href="route('admin.posts.create', { type: 'blog' })" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ New blog post</Link>
                    <Link :href="route('admin.posts.create', { type: 'news' })" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ New news item</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
