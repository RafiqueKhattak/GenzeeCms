<script setup>
import AuthorAvatar from '@/Components/Public/AuthorAvatar.vue';
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
        '@type': 'Article',
        headline: props.post.title,
        description: props.post.meta_description,
        url: props.canonical,
        datePublished: props.post.published_at,
        dateModified: props.post.updated_at,
        inLanguage: 'en',
        author: props.post.author
            ? { '@type': 'Person', name: props.post.author.name }
            : { '@type': 'Organization', name: site.value.name, url: site.value.url },
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
            <div class="byline">
                <AuthorAvatar v-if="post.author" :author="post.author" :size="32" />
                <span>
                    <span v-if="post.author" class="byline-name">{{ post.author.name }}</span>
                    <span class="article-meta">Published: {{ post.published_at?.slice(0, 10) }}</span>
                </span>
            </div>
            <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title" class="featured-image" />
            <div v-html="post.body" />

            <section v-if="related?.length" class="related-tools">
                <h2>Related posts</h2>
                <ul class="tool-grid">
                    <ToolTile
                        v-for="item in related"
                        :key="item.slug"
                        :href="`/blog/${item.slug}/`"
                        icon="✎"
                        :title="item.title"
                        :description="item.excerpt"
                    />
                </ul>
            </section>
        </article>
    </PublicLayout>
</template>

<style scoped>
.byline {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: var(--s-4, 1rem);
}

.byline-name {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
}

.byline .article-meta {
    display: block;
    margin-bottom: 0;
}

.featured-image {
    width: 100%;
    height: auto;
    border-radius: var(--radius, 12px);
    margin-bottom: var(--s-5, 1.5rem);
}
</style>
