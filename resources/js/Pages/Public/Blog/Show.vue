<script setup>
import SeoHead from '@/Components/Public/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    post: { type: Object, required: true },
    canonical: { type: String, required: true },
});

const site = computed(() => usePage().props.site);

const jsonLd = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: props.post.title,
        description: props.post.meta_description,
        url: props.canonical,
        datePublished: props.post.published_at,
        dateModified: props.post.updated_at,
        inLanguage: 'en',
        author: { '@type': 'Organization', name: site.value.name, url: site.value.url },
        publisher: { '@type': 'Organization', name: site.value.name, url: site.value.url },
        mainEntityOfPage: props.canonical,
    },
    {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home', item: site.value.url + '/' },
            { '@type': 'ListItem', position: 2, name: 'Blog', item: site.value.url + '/blog/' },
            { '@type': 'ListItem', position: 3, name: props.post.title, item: props.canonical },
        ],
    },
]);
</script>

<template>
    <SeoHead
        :title="post.meta_title ?? post.title"
        :description="post.meta_description"
        :canonical="canonical"
        :og-image="post.og_image"
        :json-ld="jsonLd"
    />
    <PublicLayout :breadcrumbs="[{ label: 'Home', href: '/' }, { label: 'Blog', href: '/blog/' }, { label: post.title }]">
        <article class="prose">
            <h1>{{ post.title }}</h1>
            <p class="article-meta">Published: {{ post.published_at?.slice(0, 10) }}</p>
            <div v-html="post.body" />
        </article>
    </PublicLayout>
</template>
