<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tool: { type: Object, default: null },
    categories: { type: Array, required: true },
    allTools: { type: Array, required: true },
});

const isEdit = !!props.tool;

const form = useForm({
    category_id: props.tool?.category_id ?? '',
    slug: props.tool?.slug ?? '',
    title: props.tool?.title ?? '',
    icon: props.tool?.icon ?? '',
    component: props.tool?.component ?? '',
    short_description: props.tool?.short_description ?? '',
    guide_content: props.tool?.guide_content ?? '',
    keywords: props.tool?.keywords?.join(', ') ?? '',
    meta_title: props.tool?.meta_title ?? '',
    meta_description: props.tool?.meta_description ?? '',
    og_image: props.tool?.og_image ?? '',
    status: props.tool?.status ?? 'draft',
    order: props.tool?.order ?? 0,
    faqs: props.tool?.faqs?.map((f) => ({ question: f.question, answer: f.answer })) ?? [{ question: '', answer: '' }],
    related: props.tool?.related?.map((r) => r.id) ?? [],
});

function addFaq() {
    form.faqs.push({ question: '', answer: '' });
}
function removeFaq(i) {
    form.faqs.splice(i, 1);
}

function submit() {
    if (isEdit) {
        form.put(route('admin.tools.update', props.tool.id));
    } else {
        form.post(route('admin.tools.store'));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="isEdit ? `Edit ${tool.title}` : 'New Tool'" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ isEdit ? `Edit ${tool.title}` : 'New tool' }}</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <form class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="title" value="Title" />
                            <TextInput id="title" v-model="form.title" class="mt-1 w-full" required />
                            <InputError :message="form.errors.title" />
                        </div>
                        <div>
                            <InputLabel for="slug" value="Slug (URL: /tools/{slug}/)" />
                            <TextInput id="slug" v-model="form.slug" class="mt-1 w-full" required />
                            <InputError :message="form.errors.slug" />
                        </div>
                        <div>
                            <InputLabel for="category_id" value="Category" />
                            <select id="category_id" v-model="form.category_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <option value="">— None —</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="icon" value="Icon / glyph (e.g. EMI, %)" />
                            <TextInput id="icon" v-model="form.icon" class="mt-1 w-full" />
                        </div>
                        <div>
                            <InputLabel for="component" value="Vue component name (e.g. AgeCalculator)" />
                            <TextInput id="component" v-model="form.component" class="mt-1 w-full" required />
                            <InputError :message="form.errors.component" />
                        </div>
                        <div>
                            <InputLabel for="status" value="Status" />
                            <select id="status" v-model="form.status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="short_description" value="Short description (shown under the H1)" />
                        <textarea id="short_description" v-model="form.short_description" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"></textarea>
                    </div>

                    <div>
                        <InputLabel value="Guide content (below the calculator)" />
                        <RichTextEditor v-model="form.guide_content" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="keywords" value="Keywords (comma separated)" />
                        <TextInput id="keywords" v-model="form.keywords" class="mt-1 w-full" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="meta_title" value="Meta title" />
                            <TextInput id="meta_title" v-model="form.meta_title" class="mt-1 w-full" />
                        </div>
                        <div>
                            <InputLabel for="og_image" value="OG image URL" />
                            <TextInput id="og_image" v-model="form.og_image" class="mt-1 w-full" />
                        </div>
                    </div>
                    <div>
                        <InputLabel for="meta_description" value="Meta description" />
                        <textarea id="meta_description" v-model="form.meta_description" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"></textarea>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <InputLabel value="FAQs" />
                            <SecondaryButton type="button" @click="addFaq">+ Add FAQ</SecondaryButton>
                        </div>
                        <div v-for="(faq, i) in form.faqs" :key="i" class="mb-3 rounded-md border border-gray-200 p-3 dark:border-gray-700">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 space-y-2">
                                    <TextInput v-model="faq.question" placeholder="Question" class="w-full" />
                                    <textarea v-model="faq.answer" rows="2" placeholder="Answer" class="w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"></textarea>
                                </div>
                                <button type="button" class="text-sm text-red-600 hover:underline" @click="removeFaq(i)">Remove</button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Related tools" />
                        <select v-model="form.related" multiple class="mt-1 h-40 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option v-for="t in allTools" :key="t.id" :value="t.id">{{ t.title }}</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Save changes' : 'Create tool' }}</PrimaryButton>
                        <Link :href="route('admin.tools.index')" class="text-sm text-gray-600 hover:underline dark:text-gray-400">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
