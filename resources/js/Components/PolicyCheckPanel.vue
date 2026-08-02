<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    type: { type: String, required: true },
    title: { type: String, default: '' },
    body: { type: String, default: '' },
    excerpt: { type: String, default: '' },
    metaDescription: { type: String, default: '' },
    featuredImage: { type: String, default: '' },
    categoryId: { type: [Number, String], default: null },
    postId: { type: [Number, String], default: null },
    tags: { type: Array, default: () => [] },
});

const result = ref(null);
const checking = ref(false);
let timer = null;

function run() {
    checking.value = true;
    window.axios
        .post(route('admin.policy-check'), {
            type: props.type,
            title: props.title,
            body: props.body,
            excerpt: props.excerpt,
            meta_description: props.metaDescription,
            featured_image: props.featuredImage,
            category_id: props.categoryId || null,
            post_id: props.postId || null,
            tags: props.tags,
        })
        .then(({ data }) => (result.value = data))
        .finally(() => (checking.value = false));
}

if (typeof window !== 'undefined') {
    watch(
        () => [props.title, props.body, props.excerpt, props.metaDescription, props.featuredImage, props.categoryId, props.tags.join(',')],
        () => {
            clearTimeout(timer);
            timer = setTimeout(run, 800);
        },
        { immediate: true },
    );
}

const statusMeta = {
    approvable: { label: 'Looks AdSense-approvable', dot: 'bg-green-500', text: 'text-green-700 dark:text-green-300' },
    needs_work: { label: 'Needs improvement', dot: 'bg-amber-500', text: 'text-amber-700 dark:text-amber-300' },
    not_approvable: { label: 'Not approvable as-is', dot: 'bg-red-500', text: 'text-red-700 dark:text-red-300' },
};

const severityMeta = {
    pass: { dot: 'bg-green-500', label: 'Pass' },
    warn: { dot: 'bg-amber-500', label: 'Suggestion' },
    fail: { dot: 'bg-red-500', label: 'Problem' },
};
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">AdSense policy check</h3>
            <span v-if="checking" class="text-xs text-gray-400">Checking…</span>
        </div>

        <template v-if="result">
            <div class="mt-2 flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full" :class="statusMeta[result.status].dot"></span>
                <span class="text-sm font-medium" :class="statusMeta[result.status].text">{{ statusMeta[result.status].label }}</span>
                <span class="ml-auto text-xs text-gray-400">Score: {{ result.score }}/100</span>
            </div>

            <ul class="mt-3 space-y-2">
                <li v-for="finding in result.findings" :key="finding.key" class="flex items-start gap-2 text-sm">
                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full" :class="severityMeta[finding.severity].dot"></span>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ finding.label }}: <span class="font-normal text-gray-600 dark:text-gray-400">{{ finding.message }}</span></p>
                        <p v-if="finding.suggestion" class="text-xs text-gray-500">→ {{ finding.suggestion }}</p>
                    </div>
                </li>
            </ul>

            <p class="mt-3 text-xs text-gray-400">Automated, rule-based screen — not affiliated with Google. The final AdSense decision is Google's alone.</p>
        </template>
    </div>
</template>
