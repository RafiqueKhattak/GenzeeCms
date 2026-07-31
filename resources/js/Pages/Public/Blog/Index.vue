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
        title="Blog: Money, Tax & Everyday Maths Guides | GenzeeLogics"
        description="Plain-English, evergreen guides to the maths behind everyday money, health and tech decisions — each one paired with a free tool that does the arithmetic for you."
        :canonical="canonical"
    />
    <PublicLayout :breadcrumbs="[{ label: 'Home', href: '/' }, { label: 'Blog' }]">
        <section class="hero">
            <h1>The GenzeeLogics blog</h1>
            <p class="lead">
                Plain-English, evergreen guides to the maths behind everyday money, health and tech decisions — each
                one paired with a free tool that does the arithmetic for you.
            </p>
        </section>
        <ul class="post-list">
            <ToolTile
                v-for="post in posts"
                :key="post.slug"
                :href="`/blog/${post.slug}/`"
                icon="✎"
                :title="post.title"
                :description="`Published ${formatDate(post.published_at)}`"
            />
        </ul>
    </PublicLayout>
</template>
