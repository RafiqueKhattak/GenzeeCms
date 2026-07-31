<script setup>
import SeoHead from '@/Components/Public/SeoHead.vue';
import ToolTile from '@/Components/Public/ToolTile.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    categories: { type: Array, required: true },
    canonical: { type: String, required: true },
});

const toolCount = props.categories.reduce((sum, c) => sum + c.tools.length, 0);
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
        </section>

        <template v-for="category in categories" :key="category.id">
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
