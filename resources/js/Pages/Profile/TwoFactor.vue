<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    enabled: { type: Boolean, required: true },
    setupSecret: { type: String, default: null },
    qrUri: { type: String, default: null },
    recoveryCodes: { type: Array, default: null },
});

const enableForm = useForm({ code: '' });
const disableForm = useForm({ current_password: '' });
const regenerateForm = useForm({ current_password: '' });

function enable() {
    enableForm.post(route('two-factor.enable'), { preserveScroll: true });
}

function disable() {
    if (!confirm('Turn off two-factor authentication for your account?')) return;
    disableForm.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => disableForm.reset(),
    });
}

function regenerate() {
    if (!confirm('Generate new recovery codes? Your old codes will stop working.')) return;
    regenerateForm.post(route('two-factor.recovery-codes'), {
        preserveScroll: true,
        onSuccess: () => regenerateForm.reset(),
    });
}

function formatSecret(secret) {
    return secret?.match(/.{1,4}/g)?.join(' ') ?? '';
}
</script>

<template>
    <Head title="Two-Factor Authentication" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Two-Factor Authentication</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl space-y-6 sm:px-6 lg:px-8">
                <div v-if="recoveryCodes?.length" class="rounded-lg border-2 border-amber-400 bg-amber-50 p-6 dark:bg-amber-900/30">
                    <h3 class="font-semibold text-amber-800 dark:text-amber-200">Save your recovery codes</h3>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        Each code works once and lets you in if you lose access to your authenticator app. They will
                        not be shown again — store them somewhere safe now.
                    </p>
                    <ul class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm text-amber-900 dark:text-amber-100">
                        <li v-for="code in recoveryCodes" :key="code">{{ code }}</li>
                    </ul>
                </div>

                <div v-if="enabled" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Two-factor authentication is on</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        You'll be asked for a code from your authenticator app every time you log in.
                    </p>

                    <form class="mt-4 flex items-center gap-3" @submit.prevent="regenerate">
                        <TextInput v-model="regenerateForm.current_password" type="password" placeholder="Current password" class="max-w-xs" />
                        <PrimaryButton type="submit" :disabled="regenerateForm.processing">Generate new recovery codes</PrimaryButton>
                    </form>
                    <InputError :message="regenerateForm.errors.current_password" class="mt-2" />

                    <form class="mt-6 flex items-center gap-3 border-t border-gray-100 pt-6 dark:border-gray-700" @submit.prevent="disable">
                        <TextInput v-model="disableForm.current_password" type="password" placeholder="Current password" class="max-w-xs" />
                        <button type="submit" class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/30" :disabled="disableForm.processing">
                            Turn off two-factor authentication
                        </button>
                    </form>
                    <InputError :message="disableForm.errors.current_password" class="mt-2" />
                </div>

                <div v-else class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Set up two-factor authentication</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Add this account to an authenticator app (Google Authenticator, Authy, 1Password, Bitwarden…)
                        using the key below, then enter the 6-digit code it shows to confirm.
                    </p>

                    <div class="mt-4 rounded-md bg-gray-50 p-4 dark:bg-gray-900">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Manual entry key</p>
                        <p class="mt-1 select-all font-mono text-lg text-gray-900 dark:text-gray-100">{{ formatSecret(setupSecret) }}</p>
                    </div>

                    <form class="mt-6" @submit.prevent="enable">
                        <InputLabel for="code" value="6-digit code from your app" />
                        <TextInput id="code" v-model="enableForm.code" inputmode="numeric" class="mt-1 w-full max-w-xs" autofocus />
                        <InputError :message="enableForm.errors.code" class="mt-2" />

                        <PrimaryButton type="submit" class="mt-4" :disabled="enableForm.processing">Enable two-factor authentication</PrimaryButton>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
