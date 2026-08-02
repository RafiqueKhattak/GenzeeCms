<script setup>
import SeoHead from '@/Components/Public/SeoHead.vue';
import ToolTile from '@/Components/Public/ToolTile.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    post: { type: Object, required: true },
    related: { type: Array, default: () => [] },
    canonical: { type: String, required: true },
});

const site = computed(() => usePage().props.site);

const jsonLd = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'NewsArticle',
        headline: props.post.title,
        description: props.post.meta_description,
        url: props.canonical,
        datePublished: props.post.published_at,
        dateModified: props.post.updated_at,
        inLanguage: 'en',
        author: { '@type': 'Person', name: 'Rafique Khattak' },
        publisher: { '@type': 'Organization', name: site.value.name, url: site.value.url, logo: site.value.ogImage },
        mainEntityOfPage: props.canonical,
    },
    {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home', item: site.value.url + '/' },
            { '@type': 'ListItem', position: 2, name: 'News', item: site.value.url + '/news/' },
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
    <PublicLayout :breadcrumbs="[{ label: 'Home', href: '/' }, { label: 'News', href: '/news/' }, { label: post.title }]">
        <article class="prose">
            <h1>{{ post.title }}</h1>
            <p class="article-meta">Published: {{ post.published_at?.slice(0, 10) }}</p>
            <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title" class="featured-image" />
            <div v-html="post.body" />

            <section v-if="related?.length" class="related-tools">
                <h2>Related news</h2>
                <ul class="tool-grid">
                    <ToolTile
                        v-for="item in related"
                        :key="item.slug"
                        :href="`/news/${item.slug}/`"
                        icon="📰"
                        :title="item.title"
                        :description="item.excerpt"
                    />
                </ul>
            </section>
        </article>
    </PublicLayout>
</template>

<style scoped>
.featured-image {
    width: 100%;
    height: auto;
    border-radius: var(--radius, 12px);
    margin-bottom: var(--s-5, 1.5rem);
}
</style>
