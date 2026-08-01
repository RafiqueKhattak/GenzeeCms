<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PolicyCheckPanel from '@/Components/PolicyCheckPanel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    post: { type: Object, default: null },
    defaultType: { type: String, default: 'blog' },
    categories: { type: Array, required: true },
    tags: { type: Array, required: true },
});

const isEdit = !!props.post;
const uploading = ref(false);

const form = useForm({
    type: props.post?.type ?? props.defaultType,
    category_id: props.post?.category_id ?? '',
    slug: props.post?.slug ?? '',
    title: props.post?.title ?? '',
    excerpt: props.post?.excerpt ?? '',
    body: props.post?.body ?? '',
    featured_image: props.post?.featured_image ?? '',
    meta_title: props.post?.meta_title ?? '',
    meta_description: props.post?.meta_description ?? '',
    canonical_override: props.post?.canonical_override ?? '',
    og_image: props.post?.og_image ?? '',
    status: props.post?.status ?? 'draft',
    published_at: props.post?.published_at?.slice(0, 16) ?? '',
    tags: props.post?.tags?.map((t) => t.name) ?? [],
});

const tagsText = ref(form.tags.join(', '));

function submit() {
    form.tags = tagsText.value.split(',').map((t) => t.trim()).filter(Boolean);
    if (isEdit) {
        form.put(route('admin.posts.update', props.post.id));
    } else {
        form.post(route('admin.posts.store'));
    }
}

function uploadFeaturedImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    uploading.value = true;
    const data = new FormData();
    data.append('file', file);
    window.axios
        .post(route('admin.media.store'), data, { headers: { Accept: 'application/json' } })
        .then(({ data: media }) => {
            form.featured_image = `/storage/${media.path}`;
        })
        .finally(() => (uploading.value = false));
}

const categoriesForType = () => props.categories.filter((c) => c.type === form.type);
const tagsArray = computed(() => tagsText.value.split(',').map((t) => t.trim()).filter(Boolean));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="isEdit ? `Edit ${post.title}` : 'New Post'" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ isEdit ? `Edit ${post.title}` : 'New post' }}</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-6xl gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
                <form class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800 lg:col-span-2" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Type" />
                            <select v-model="form.type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <option value="blog">Blog</option>
                                <option value="news">News</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="category_id" value="Category" />
                            <select id="category_id" v-model="form.category_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <option value="">— None —</option>
                                <option v-for="cat in categoriesForType()" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="title" value="Title" />
                            <TextInput id="title" v-model="form.title" class="mt-1 w-full" required />
                            <InputError :message="form.errors.title" />
                        </div>
                        <div>
                            <InputLabel for="slug" value="Slug" />
                            <TextInput id="slug" v-model="form.slug" class="mt-1 w-full" required />
                            <InputError :message="form.errors.slug" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="excerpt" value="Excerpt / summary" />
                        <textarea id="excerpt" v-model="form.excerpt" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"></textarea>
                    </div>

                    <div>
                        <InputLabel value="Body" />
                        <RichTextEditor v-model="form.body" class="mt-1" />
                        <InputError :message="form.errors.body" />
                    </div>

                    <div>
                        <InputLabel value="Featured image" />
                        <div class="mt-1 flex items-center gap-3">
                            <img v-if="form.featured_image" :src="form.featured_image" class="h-16 w-24 rounded object-cover" />
                            <input type="file" accept="image/*" :disabled="uploading" @change="uploadFeaturedImage" />
                            <span v-if="uploading" class="text-sm text-gray-500">Uploading…</span>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="tags" value="Tags (comma separated)" />
                        <TextInput id="tags" v-model="tagsText" class="mt-1 w-full" />
                        <p class="mt-1 text-xs text-gray-500">Existing: {{ tags.map((t) => t.name).join(', ') || '—' }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Status" />
                            <select v-model="form.status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <option value="draft">Draft</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="published_at" value="Publish date/time" />
                            <input id="published_at" v-model="form.published_at" type="datetime-local" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                        </div>
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

                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Save changes' : 'Create post' }}</PrimaryButton>
                        <Link :href="route('admin.posts.index')" class="text-sm text-gray-600 hover:underline dark:text-gray-400">Cancel</Link>
                    </div>
                </form>

                <div class="lg:sticky lg:top-6 lg:self-start">
                    <PolicyCheckPanel
                        :type="form.type"
                        :title="form.title"
                        :body="form.body"
                        :excerpt="form.excerpt"
                        :meta-description="form.meta_description"
                        :featured-image="form.featured_image"
                        :category-id="form.category_id"
                        :tags="tagsArray"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
