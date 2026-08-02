<script setup>
import { computed, ref } from 'vue';
import SeoHead from '@/Components/Public/SeoHead.vue';
import ToolTile from '@/Components/Public/ToolTile.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    categories: { type: Array, required: true },
    canonical: { type: String, required: true },
});

const toolCount = props.categories.reduce((sum, c) => sum + c.tools.length, 0);

const query = ref('');

const filteredCategories = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return props.categories;

    return props.categories
        .map((category) => ({
            ...category,
            tools: category.tools.filter(
                (tool) =>
                    tool.title.toLowerCase().includes(q) || (tool.short_description ?? '').toLowerCase().includes(q)
            ),
        }))
        .filter((category) => category.tools.length > 0);
});

const hasResults = computed(() => filteredCategories.value.some((c) => c.tools.length > 0));
</script>

<template>
    <SeoHead
        :title="`All Tools: ${toolCount} Free Calculators & Converters`"
        description="Free calculators and converters, organised by category. Results appear instantly — nothing you enter ever leaves your browser."
        :canonical="canonical"
    />
    <PublicLayout :breadcrumbs="[{ label: 'Home', href: '/' }, { label: 'Tools' }]">
        <section class="hero">
            <h1>All tools</h1>
            <p class="lead">
                Free calculators and converters, organised by what you're trying to do. Results appear instantly as
                you type, and nothing you enter ever leaves your browser.
            </p>
            <input
                v-model="query"
                type="search"
                class="tool-search"
                :placeholder="`Search ${toolCount} tools…`"
                aria-label="Search tools"
            />
        </section>

        <p v-if="!hasResults" style="color: var(--ink-muted)">No tools match "{{ query }}".</p>

        <template v-for="category in filteredCategories" :key="category.id">
            <div class="section-head"><h2 :id="category.slug">{{ category.name }}</h2></div>
            <p style="color: var(--ink-muted); margin-top: 0.5rem">{{ category.tagline }}</p>
            <ul class="tool-grid">
                <ToolTile
                    v-for="tool in category.tools"
                    :key="tool.id"
                    :href="`/tools/${tool.slug}/`"
                    :icon="tool.icon"
                    :title="tool.title"
                    :description="tool.short_description"
                />
            </ul>
        </template>
    </PublicLayout>
</template>

<style scoped>
.tool-search {
    width: 100%;
    max-width: 28rem;
    margin-top: 1rem;
    padding: 0.65rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid var(--border, #ddd);
    font-size: 1rem;
}
</style>
