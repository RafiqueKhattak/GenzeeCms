<script setup>
import { computed, ref } from 'vue';
import AuthorAvatar from '@/Components/Public/AuthorAvatar.vue';
import SeoHead from '@/Components/Public/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    posts: { type: Array, required: true },
    canonical: { type: String, required: true },
});

function formatDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatTime(value) {
    if (!value) return '';
    return new Date(value).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
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

        <ul class="post-grid">
            <li v-for="post in filteredPosts" :key="post.slug" class="post-card">
                <a :href="`/blog/${post.slug}/`" class="post-card-link">
                    <div class="post-card-image">
                        <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title" loading="lazy" />
                        <span v-else class="post-card-placeholder" aria-hidden="true">✎</span>
                    </div>
                    <div class="post-card-body">
                        <h2 class="post-card-title">{{ post.title }}</h2>
                        <p v-if="post.excerpt" class="post-card-excerpt">{{ post.excerpt }}</p>
                        <div class="post-card-meta">
                            <span class="meta-item">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" /></svg>
                                {{ formatDate(post.published_at) }}
                            </span>
                            <span class="meta-item">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" /></svg>
                                {{ formatTime(post.published_at) }}
                            </span>
                        </div>
                        <div v-if="post.author" class="post-card-byline">
                            <AuthorAvatar :author="post.author" :size="24" />
                            <span>{{ post.author.name }}</span>
                        </div>
                    </div>
                </a>
            </li>
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

.post-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--s-6, 2rem);
    list-style: none;
    margin: var(--s-6, 2rem) 0 0;
    padding: 0;
}

.post-card {
    border-radius: var(--radius-lg, 18px);
    overflow: hidden;
    background: var(--surface, #fff);
    border: 1px solid var(--border, #e5e7eb);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.post-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.18);
}

.post-card-link {
    display: block;
    color: inherit;
    text-decoration: none;
    height: 100%;
}

.post-card-image {
    aspect-ratio: 1.91 / 1;
    background: var(--accent-soft, #ede9fe);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.post-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-card-placeholder {
    font-size: 2.5rem;
    color: var(--accent-deep, #5b21b6);
    opacity: 0.5;
}

.post-card-body {
    padding: var(--s-4, 1rem) var(--s-5, 1.5rem) var(--s-5, 1.5rem);
}

.post-card-title {
    font-size: 1.08rem;
    line-height: 1.4;
    margin: 0 0 0.4rem;
}

.post-card-excerpt {
    font-size: 0.88rem;
    color: var(--ink-muted, #6b7280);
    margin: 0 0 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-card-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.78rem;
    color: var(--ink-muted, #6b7280);
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.post-card-byline {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.65rem;
    font-size: 0.82rem;
    font-weight: 500;
}
</style>
