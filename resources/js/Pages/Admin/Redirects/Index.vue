<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

defineProps({
    redirects: { type: Array, required: true },
});

const form = useForm({ from_path: '', to_path: '', status_code: 301 });

function create() {
    if (form.status_code === 410) form.to_path = '';
    form.post(route('admin.redirects.store'), { onSuccess: () => form.reset('from_path', 'to_path') });
}

function destroy(redirect) {
    if (confirm(`Delete redirect from "${redirect.from_path}"?`)) {
        router.delete(route('admin.redirects.destroy', redirect.id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Redirects" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Redirects</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    If you ever change or remove a URL that Google has indexed, add a redirect here so visitors and
                    search engines land on the new page instead of a 404. Use <strong>410 (gone)</strong> instead of a
                    redirect when the content is retired for good with no replacement — it tells Google to drop the
                    URL from its index rather than keep re-checking it.
                </p>

                <form class="grid gap-3 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:grid-cols-5" @submit.prevent="create">
                    <div class="sm:col-span-2">
                        <TextInput v-model="form.from_path" placeholder="/old-path/" class="w-full" required />
                        <InputError :message="form.errors.from_path" />
                    </div>
                    <div class="sm:col-span-2">
                        <TextInput
                            v-if="form.status_code !== 410"
                            v-model="form.to_path"
                            placeholder="/new-path/ or https://external.com/"
                            class="w-full"
                            required
                        />
                        <div v-else class="flex h-full items-center text-sm italic text-gray-400">No destination — page is gone.</div>
                        <InputError :message="form.errors.to_path" />
                    </div>
                    <select v-model.number="form.status_code" class="rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option :value="301">301 (permanent)</option>
                        <option :value="302">302 (temporary)</option>
                        <option :value="410">410 (gone)</option>
                    </select>
                    <PrimaryButton class="sm:col-span-5 justify-self-start" :disabled="form.processing">+ Add redirect</PrimaryButton>
                </form>

                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">From</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">To</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Code</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="r in redirects" :key="r.id">
                                <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">{{ r.from_path }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ r.to_path ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ r.status_code }}</td>
                                <td class="px-4 py-2 text-right text-sm">
                                    <button type="button" class="text-red-600 hover:underline dark:text-red-400" @click="destroy(r)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!redirects.length">
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No redirects yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
