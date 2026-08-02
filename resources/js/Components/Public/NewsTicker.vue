<script setup>
import { computed } from 'vue';
import { departmentIcon } from '@/Support/newsDepartments';

const props = defineProps({
    items: { type: Array, required: true },
});

// Duplicated so the CSS animation can loop seamlessly from -50% back to 0%.
const loopItems = computed(() => [...props.items, ...props.items]);
</script>

<template>
    <div v-if="items.length" class="news-ticker" role="marquee" aria-label="Most-read headlines">
        <span class="news-ticker-label">Trending</span>
        <div class="news-ticker-track">
            <a
                v-for="(item, i) in loopItems"
                :key="i"
                :href="`/news/${item.slug}/`"
                class="news-ticker-item"
            >
                <span aria-hidden="true">{{ departmentIcon(item.category?.slug) }}</span>
                {{ item.title }}
            </a>
        </div>
    </div>
</template>

<style scoped>
.news-ticker {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: var(--accent-deep, #5b21b6);
    color: #fff;
    border-radius: var(--radius, 12px);
    padding: 0.55rem 0;
    margin-bottom: var(--s-5, 1.5rem);
    overflow: hidden;
}

.news-ticker-label {
    flex: none;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0 1rem;
    border-right: 1px solid rgba(255, 255, 255, 0.25);
}

.news-ticker-track {
    display: flex;
    align-items: center;
    gap: 2.5rem;
    white-space: nowrap;
    animation: news-ticker-scroll 60s linear infinite;
}

.news-ticker:hover .news-ticker-track {
    animation-play-state: paused;
}

.news-ticker-item {
    color: #fff;
    font-size: 0.9rem;
    text-decoration: none;
    opacity: 0.95;
}

.news-ticker-item:hover {
    text-decoration: underline;
}

@keyframes news-ticker-scroll {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .news-ticker-track {
        animation: none;
    }
}
</style>
