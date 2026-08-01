<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    page: { type: Object, required: true },
});

const form = useForm({
    title: props.page.title,
    body: props.page.body,
    meta_title: props.page.meta_title ?? '',
    meta_description: props.page.meta_description ?? '',
});

function submit() {
    form.put(route('admin.pages.update', props.page.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit ${page.title}`" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Edit {{ page.title }}</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <form class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800" @submit.prevent="submit">
                    <div>
                        <InputLabel for="title" value="Title" />
                        <TextInput id="title" v-model="form.title" class="mt-1 w-full" required />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div>
                        <InputLabel value="Body" />
                        <RichTextEditor v-model="form.body" class="mt-1" />
                        <InputError :message="form.errors.body" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="meta_title" value="Meta title" />
                            <TextInput id="meta_title" v-model="form.meta_title" class="mt-1 w-full" />
                        </div>
                        <div>
                            <InputLabel for="meta_description" value="Meta description" />
                            <TextInput id="meta_description" v-model="form.meta_description" class="mt-1 w-full" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="form.processing">Save changes</PrimaryButton>
                        <Link :href="route('admin.pages.index')" class="text-sm text-gray-600 hover:underline dark:text-gray-400">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
