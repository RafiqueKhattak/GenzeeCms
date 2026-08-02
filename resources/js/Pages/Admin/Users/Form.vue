<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: { type: Object, default: null },
});

const isEdit = !!props.user;

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    role: props.user?.role ?? 'editor',
    is_active: props.user?.is_active ?? true,
    password: '',
});

function submit() {
    if (isEdit) {
        form.put(route('admin.users.update', props.user.id));
    } else {
        form.post(route('admin.users.store'));
    }
}

function sendResetLink() {
    router.post(route('admin.users.send-reset-link', props.user.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="isEdit ? `Edit ${user.name}` : 'New User'" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ isEdit ? `Edit ${user.name}` : 'New user' }}</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <form class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800" @submit.prevent="submit">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" class="mt-1 w-full" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 w-full" required />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel value="Role" />
                        <select v-model="form.role" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div v-if="isEdit" class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.is_active" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </div>
                    <div>
                        <InputLabel for="password" :value="isEdit ? 'New password (leave blank to keep current)' : 'Password'" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 w-full" :required="!isEdit" />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Save changes' : 'Create user' }}</PrimaryButton>
                        <Link :href="route('admin.users.index')" class="text-sm text-gray-600 hover:underline dark:text-gray-400">Cancel</Link>
                    </div>
                </form>

                <div v-if="isEdit" class="mt-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Password reset</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Emails {{ user.email }} a link to set their own new password, instead of you setting one for
                        them above.
                    </p>
                    <button
                        type="button"
                        class="mt-3 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        @click="sendResetLink"
                    >
                        Send password reset link
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
