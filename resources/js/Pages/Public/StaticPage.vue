<script setup>
import SeoHead from '@/Components/Public/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    page: { type: Object, required: true },
    canonical: { type: String, required: true },
});
</script>

<template>
    <SeoHead :title="page.meta_title ?? page.title" :description="page.meta_description" :canonical="canonical" />
    <PublicLayout :breadcrumbs="[{ label: 'Home', href: '/' }, { label: page.title }]">
        <article class="prose" v-html="page.body" />
    </PublicLayout>
</template>
