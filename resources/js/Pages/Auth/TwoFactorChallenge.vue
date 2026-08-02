<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const useRecoveryCode = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

function submit() {
    form.post(route('two-factor.store'), {
        onFinish: () => form.reset(),
    });
}

function toggleMode() {
    useRecoveryCode.value = !useRecoveryCode.value;
    form.reset();
    form.clearErrors();
}
</script>

<template>
    <GuestLayout>
        <Head title="Two-factor authentication" />

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <template v-if="!useRecoveryCode">Enter the 6-digit code from your authenticator app.</template>
            <template v-else>Enter one of your unused recovery codes.</template>
        </p>

        <form @submit.prevent="submit">
            <div v-if="!useRecoveryCode">
                <InputLabel for="code" value="Authentication code" />
                <TextInput
                    id="code"
                    v-model="form.code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    class="mt-1 block w-full"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" value="Recovery code" />
                <TextInput id="recovery_code" v-model="form.recovery_code" class="mt-1 block w-full" autofocus />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <button
                    type="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                    @click="toggleMode"
                >
                    {{ useRecoveryCode ? 'Use authenticator code instead' : 'Use a recovery code instead' }}
                </button>

                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Verify</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
