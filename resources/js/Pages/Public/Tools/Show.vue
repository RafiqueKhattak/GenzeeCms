<script setup>
import CalculatorMount from '@/Components/Public/CalculatorMount.vue';
import SeoHead from '@/Components/Public/SeoHead.vue';
import ToolTile from '@/Components/Public/ToolTile.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tool: { type: Object, required: true },
    canonical: { type: String, required: true },
});

const site = computed(() => usePage().props.site);

const jsonLd = computed(() => {
    const blocks = [
        {
            '@context': 'https://schema.org',
            '@type': 'WebApplication',
            name: props.tool.title,
            url: props.canonical,
            description: props.tool.meta_description,
            applicationCategory: 'UtilityApplication',
            operatingSystem: 'Any',
            browserRequirements: 'Requires JavaScript',
            offers: { '@type': 'Offer', price: '0', priceCurrency: 'USD' },
            publisher: { '@type': 'Organization', name: site.value.name, url: site.value.url },
        },
        {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: [
                { '@type': 'ListItem', position: 1, name: 'Home', item: site.value.url + '/' },
                { '@type': 'ListItem', position: 2, name: 'Tools', item: site.value.url + '/tools/' },
                { '@type': 'ListItem', position: 3, name: props.tool.title, item: props.canonical },
            ],
        },
    ];

    if (props.tool.faqs?.length) {
        blocks.push({
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            mainEntity: props.tool.faqs.map((f) => ({
                '@type': 'Question',
                name: f.question,
                acceptedAnswer: { '@type': 'Answer', text: f.answer },
            })),
        });
    }

    return blocks;
});
</script>

<template>
    <SeoHead
        :title="tool.meta_title ?? tool.title"
        :description="tool.meta_description"
        :canonical="canonical"
        :og-image="tool.og_image"
        :json-ld="jsonLd"
    />
    <PublicLayout :breadcrumbs="[{ label: 'Home', href: '/' }, { label: 'Tools', href: '/tools/' }, { label: tool.title }]">
        <article>
            <h1>{{ tool.title }}</h1>
            <p class="lead">{{ tool.short_description }}</p>

            <CalculatorMount :component="tool.component" :title="tool.title" />

            <div v-if="tool.guide_content" class="prose" v-html="tool.guide_content" />

            <section v-if="tool.faqs?.length" class="faq-section">
                <h2>Frequently asked questions</h2>
                <details v-for="faq in tool.faqs" :key="faq.id" class="fold">
                    <summary>{{ faq.question }}</summary>
                    <div class="fold-body"><p>{{ faq.answer }}</p></div>
                </details>
            </section>

            <section v-if="tool.related?.length" class="related-tools">
                <h2>Related tools</h2>
                <ul class="tool-grid">
                    <ToolTile
                        v-for="related in tool.related"
                        :key="related.id"
                        :href="`/tools/${related.slug}/`"
                        :icon="related.icon"
                        :title="related.title"
                        :description="related.short_description"
                    />
                </ul>
            </section>
        </article>
    </PublicLayout>
</template>
