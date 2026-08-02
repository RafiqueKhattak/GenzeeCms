<script setup>
import { computed, ref } from 'vue';
import NewsTicker from '@/Components/Public/NewsTicker.vue';
import SeoHead from '@/Components/Public/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { departmentIcon } from '@/Support/newsDepartments';

const props = defineProps({
    posts: { type: Array, required: true },
    departments: { type: Array, required: true },
    ticker: { type: Array, required: true },
    canonical: { type: String, required: true },
});

const query = ref('');
const activeDept = ref('all');

const filteredPosts = computed(() => {
    const q = query.value.trim().toLowerCase();

    return props.posts.filter((post) => {
        const matchesQuery = !q || post.title.toLowerCase().includes(q);
        const matchesDept = activeDept.value === 'all' || post.category?.slug === activeDept.value;
        return matchesQuery && matchesDept;
    });
});

function dateKey(value) {
    return new Date(value).toDateString();
}

function dateLabel(value) {
    const d = new Date(value);
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    if (d.toDateString() === today.toDateString()) return 'Today';
    if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
    return d.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' });
}

function formatTime(value) {
    return new Date(value).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

// Posts already arrive sorted newest-first, so a single pass groups them
// into consecutive Today / Yesterday / dated sections without re-sorting.
const groupedPosts = computed(() => {
    const groups = [];
    let currentKey = null;

    for (const post of filteredPosts.value) {
        const key = dateKey(post.published_at);
        if (key !== currentKey) {
            groups.push({ label: dateLabel(post.published_at), items: [] });
            currentKey = key;
        }
        groups[groups.length - 1].items.push(post);
    }

    return groups;
});
</script>

<template>
    <SeoHead
        title="News: Money, Tax & Tech Updates | GenzeeLogics"
        description="Short, useful updates — money, tax and tech news that actually affects Gen Z, explained in plain language."
        :canonical="canonical"
    />
    <PublicLayout :breadcrumbs="[{ label: 'Home', href: '/' }, { label: 'News' }]">
        <NewsTicker :items="ticker" />

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

        <div class="dept-tabs" role="tablist">
            <button
                type="button"
                class="dept-tab"
                :class="{ active: activeDept === 'all' }"
                @click="activeDept = 'all'"
            >
                All
            </button>
            <button
                v-for="dept in departments"
                :key="dept.slug"
                type="button"
                class="dept-tab"
                :class="{ active: activeDept === dept.slug }"
                @click="activeDept = dept.slug"
            >
                <span aria-hidden="true">{{ departmentIcon(dept.slug) }}</span> {{ dept.name }}
            </button>
        </div>

        <p v-if="filteredPosts.length === 0" style="color: var(--ink-muted)">No news items match your filters.</p>

        <div v-for="group in groupedPosts" :key="group.label" class="news-group">
            <h2 class="news-group-label">{{ group.label }}</h2>
            <ul class="news-list">
                <li v-for="post in group.items" :key="post.slug" class="news-row">
                    <a :href="`/news/${post.slug}/`" class="news-row-link">
                        <span v-if="post.category" class="news-row-icon" aria-hidden="true">{{ departmentIcon(post.category.slug) }}</span>
                        <span class="news-row-title">{{ post.title }}</span>
                        <span class="news-row-time">{{ formatTime(post.published_at) }}</span>
                    </a>
                </li>
            </ul>
        </div>
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

.dept-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: var(--s-5, 1.5rem) 0;
}

.dept-tab {
    border: 1px solid var(--border, #e5e7eb);
    background: var(--surface, #fff);
    color: var(--ink, #111827);
    border-radius: 999px;
    padding: 0.35rem 0.9rem;
    font-size: 0.83rem;
    cursor: pointer;
}

.dept-tab.active {
    background: var(--accent-deep, #5b21b6);
    border-color: var(--accent-deep, #5b21b6);
    color: #fff;
}

.news-group {
    margin-top: var(--s-6, 2rem);
}

.news-group-label {
    font-size: 0.95rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--ink-muted, #6b7280);
    border-bottom: 1px solid var(--border, #e5e7eb);
    padding-bottom: 0.4rem;
    margin: 0 0 0.25rem;
}

.news-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.news-row {
    border-bottom: 1px solid var(--border, #f0f0f0);
}

.news-row-link {
    display: flex;
    align-items: baseline;
    gap: 0.6rem;
    padding: 0.55rem 0.15rem;
    text-decoration: none;
    color: inherit;
}

.news-row-link:hover .news-row-title {
    color: var(--accent-deep, #5b21b6);
    text-decoration: underline;
}

.news-row-icon {
    flex: none;
    font-size: 0.85rem;
}

.news-row-title {
    flex: 1;
    font-size: 0.94rem;
    line-height: 1.4;
}

.news-row-time {
    flex: none;
    font-size: 0.78rem;
    color: var(--ink-muted, #6b7280);
}
</style>
