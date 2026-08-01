<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    users: { type: Array, required: true },
});

function destroy(user) {
    if (confirm(`Delete "${user.name}"?`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Users" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Users</h2>
                <Link :href="route('admin.users.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ New user</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Role</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Active</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ user.email }}</td>
                                <td class="px-4 py-2 text-sm capitalize text-gray-600 dark:text-gray-300">{{ user.role }}</td>
                                <td class="px-4 py-2 text-sm">{{ user.is_active ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-2 text-right text-sm">
                                    <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:underline dark:text-indigo-400">Edit</Link>
                                    <button type="button" class="ml-3 text-red-600 hover:underline dark:text-red-400" @click="destroy(user)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
