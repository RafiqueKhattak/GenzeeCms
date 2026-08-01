<script setup>
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

defineProps({
    media: { type: Object, required: true },
});

const form = useForm({ file: null });

function upload(e) {
    form.file = e.target.files[0];
    if (!form.file) return;
    form.post(route('admin.media.store'), {
        forceFormData: true,
        onSuccess: () => form.reset(),
    });
}

function destroy(item) {
    if (confirm('Delete this file?')) {
        router.delete(route('admin.media.destroy', item.id));
    }
}

function copy(url) {
    navigator.clipboard?.writeText(window.location.origin + url);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Media Library" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Media Library</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload image or video</label>
                    <input type="file" accept="image/*,video/*" class="mt-2" :disabled="form.processing" @change="upload" />
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 md:grid-cols-6">
                    <div v-for="item in media.data" :key="item.id" class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="flex h-24 items-center justify-center bg-gray-100 dark:bg-gray-900">
                            <img v-if="item.type === 'image'" :src="`/storage/${item.path}`" class="h-full w-full object-cover" />
                            <video v-else :src="`/storage/${item.path}`" class="h-full w-full object-cover" muted></video>
                        </div>
                        <div class="p-2 text-xs">
                            <button type="button" class="text-indigo-600 hover:underline dark:text-indigo-400" @click="copy(`/storage/${item.path}`)">Copy URL</button>
                            <button type="button" class="ml-2 text-red-600 hover:underline dark:text-red-400" @click="destroy(item)">Delete</button>
                        </div>
                    </div>
                    <p v-if="!media.data.length" class="col-span-full text-sm text-gray-500">No media uploaded yet.</p>
                </div>

                <Pagination :links="media.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
