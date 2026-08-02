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
            <input
                v-model="query"
                type="search"
                class="post-search"
                placeholder="Search news…"
                aria-label="Search news"
            />
        </section>
        <p v-if="filteredPosts.length === 0" style="color: var(--ink-muted)">No news items match "{{ query }}".</p>
        <ul class="post-list">
            <ToolTile
                v-for="post in filteredPosts"
                :key="post.slug"
                :href="`/news/${post.slug}/`"
                icon="📰"
                :title="post.title"
                :description="`${formatDate(post.published_at)} — ${post.excerpt ?? ''}`"
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
