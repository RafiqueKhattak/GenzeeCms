<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, required: true },
});

const form = useForm({ type: 'tool', name: '', tagline: '' });

function create() {
    form.post(route('admin.categories.store'), { onSuccess: () => form.reset('name', 'tagline') });
}

function destroy(category) {
    if (confirm(`Delete "${category.name}"? Tools/posts keep their content but lose this category.`)) {
        router.delete(route('admin.categories.destroy', category.id));
    }
}

function byType(type) {
    return props.categories.filter((c) => c.type === type);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Categories" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Categories</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <form class="grid gap-3 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:grid-cols-4" @submit.prevent="create">
                    <select v-model="form.type" class="rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="tool">Tool</option>
                        <option value="blog">Blog</option>
                        <option value="news">News</option>
                    </select>
                    <TextInput v-model="form.name" placeholder="Category name" class="sm:col-span-2" required />
                    <PrimaryButton :disabled="form.processing">+ Add</PrimaryButton>
                </form>

                <div v-for="type in ['tool', 'blog', 'news']" :key="type" class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <h3 class="border-b border-gray-100 px-4 py-2 text-sm font-semibold capitalize text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ type }} categories</h3>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        <li v-for="cat in byType(type)" :key="cat.id" class="flex items-center justify-between px-4 py-2 text-sm">
                            <span>{{ cat.name }} <span class="text-xs text-gray-400">({{ (cat.tools_count ?? 0) + (cat.posts_count ?? 0) }} items)</span></span>
                            <button type="button" class="text-red-600 hover:underline dark:text-red-400" @click="destroy(cat)">Delete</button>
                        </li>
                        <li v-if="!byType(type).length" class="px-4 py-3 text-sm text-gray-400">No categories yet.</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
