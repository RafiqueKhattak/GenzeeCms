<script setup>
import { computed, ref } from 'vue';
import SeoHead from '@/Components/Public/SeoHead.vue';
import ToolTile from '@/Components/Public/ToolTile.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    posts: { type: Array, required: true },
    canonical: { type: String, required: true },
});

function formatDate(value) {
    return value?.slice(0, 10);
}

const query = ref('');

const filteredPosts = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return props.posts;

    return props.posts.filter(
        (post) => post.title.toLowerCase().includes(q) || (post.excerpt ?? '').toLowerCase().includes(q)
    );
});
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
            <input
                v-model="query"
                type="search"
                class="post-search"
                placeholder="Search blog posts…"
                aria-label="Search blog posts"
            />
        </section>
        <p v-if="filteredPosts.length === 0" style="color: var(--ink-muted)">No posts match "{{ query }}".</p>
        <ul class="post-list">
            <ToolTile
                v-for="post in filteredPosts"
                :key="post.slug"
                :href="`/blog/${post.slug}/`"
                icon="✎"
                :title="post.title"
                :description="`Published ${formatDate(post.published_at)}`"
            />
        </ul>
    </PublicLayout>
</template>

<style scoped>
.post-search {
    width: 100%;
    max-width: 28rem;
    margin-top: 1rem;
    padding: 0.65rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid var(--border, #ddd);
    font-size: 1rem;
}
</style>
