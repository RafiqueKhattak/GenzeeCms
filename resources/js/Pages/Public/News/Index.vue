<script setup>
import SeoHead from '@/Components/Public/SeoHead.vue';
import ToolTile from '@/Components/Public/ToolTile.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    posts: { type: Array, required: true },
    canonical: { type: String, required: true },
});

function formatDate(value) {
    return value?.slice(0, 10);
}
</script>

<template>
    <SeoHead
        title="News: Money, Tax & Tech Updates | GenzeeLogics"
        description="Short, useful updates — money, tax and tech news that actually affects Gen Z, explained in plain language."
        :canonical="canonical"
    />
    <PublicLayout :breadcrumbs="[{ label: 'Home', href: '/' }, { label: 'News' }]">
        <section class="hero">
            <h1>GenzeeLogics News</h1>
            <p class="lead">
                Short, useful updates — money, tax and tech news that actually affects Gen Z, explained in plain
                language.
            </p>
        </section>
        <ul class="post-list">
            <ToolTile
                v-for="post in posts"
                :key="post.slug"
                :href="`/news/${post.slug}/`"
                icon="📰"
                :title="post.title"
                :description="`${formatDate(post.published_at)} — ${post.excerpt ?? ''}`"
            />
        </ul>
    </PublicLayout>
</template>
