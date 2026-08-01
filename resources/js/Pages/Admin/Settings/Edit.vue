<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    site_name: props.settings.site_name ?? '',
    site_tagline: props.settings.site_tagline ?? '',
    site_url: props.settings.site_url ?? '',
    meta_title_suffix: props.settings.meta_title_suffix ?? '',
    meta_description: props.settings.meta_description ?? '',
    og_image: props.settings.og_image ?? '',
    google_analytics_id: props.settings.google_analytics_id ?? '',
    google_site_verification: props.settings.google_site_verification ?? '',
    adsense_publisher_id: props.settings.adsense_publisher_id ?? '',
    ads_txt_content: props.settings.ads_txt_content ?? '',
    contact_email: props.settings.contact_email ?? '',
    logo: null,
    favicon: null,
});

function submit() {
    form.post(route('admin.settings.update'), { forceFormData: true, preserveScroll: true });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Settings" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Site Settings</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <form class="space-y-8 rounded-lg bg-white p-6 shadow dark:bg-gray-800" @submit.prevent="submit">
                    <section class="space-y-4">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Identity</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Site name" />
                                <TextInput v-model="form.site_name" class="mt-1 w-full" required />
                            </div>
                            <div>
                                <InputLabel value="Site URL" />
                                <TextInput v-model="form.site_url" class="mt-1 w-full" required />
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Tagline" />
                            <TextInput v-model="form.site_tagline" class="mt-1 w-full" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Logo (image file)" />
                                <input type="file" accept="image/*" class="mt-1" @change="form.logo = $event.target.files[0]" />
                            </div>
                            <div>
                                <InputLabel value="Favicon (.ico or image)" />
                                <input type="file" accept="image/*" class="mt-1" @change="form.favicon = $event.target.files[0]" />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-gray-100 pt-6 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">SEO defaults</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Meta title suffix" />
                                <TextInput v-model="form.meta_title_suffix" class="mt-1 w-full" />
                            </div>
                            <div>
                                <InputLabel value="Default OG image URL" />
                                <TextInput v-model="form.og_image" class="mt-1 w-full" />
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Default meta description" />
                            <textarea v-model="form.meta_description" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"></textarea>
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-gray-100 pt-6 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Analytics &amp; verification</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Google Analytics ID" />
                                <TextInput v-model="form.google_analytics_id" class="mt-1 w-full" placeholder="G-XXXXXXXXXX" />
                            </div>
                            <div>
                                <InputLabel value="Google site verification code" />
                                <TextInput v-model="form.google_site_verification" class="mt-1 w-full" />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-gray-100 pt-6 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">AdSense</h3>
                        <div>
                            <InputLabel value="AdSense publisher ID" />
                            <TextInput v-model="form.adsense_publisher_id" class="mt-1 w-full" placeholder="pub-XXXXXXXXXXXXXXXX" />
                        </div>
                        <div>
                            <InputLabel value="ads.txt content" />
                            <textarea v-model="form.ads_txt_content" rows="3" class="mt-1 w-full rounded-md border-gray-300 font-mono text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"></textarea>
                            <p class="mt-1 text-xs text-gray-500">Served verbatim at /ads.txt. Add your publisher line here once AdSense approves the site.</p>
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-gray-100 pt-6 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Contact</h3>
                        <div>
                            <InputLabel value="Contact email" />
                            <TextInput v-model="form.contact_email" type="email" class="mt-1 w-full" />
                        </div>
                    </section>

                    <PrimaryButton :disabled="form.processing">Save settings</PrimaryButton>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
