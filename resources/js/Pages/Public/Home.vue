<script setup>
import SeoHead from '@/Components/Public/SeoHead.vue';
import ToolTile from '@/Components/Public/ToolTile.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    categories: { type: Array, required: true },
    latestPosts: { type: Array, required: true },
    canonical: { type: String, required: true },
});

const site = computed(() => usePage().props.site);
</script>

<template>
    <SeoHead
        :title="`${site.name}: Free Online Calculators & Everyday Tools`"
        :description="site.metaDescription"
        :canonical="canonical"
    />
    <PublicLayout>
        <section class="hero">
            <span class="eyebrow">Free · Private · No sign-up</span>
            <h1>Every calculator you reach for, in one fast toolkit</h1>
            <p class="lead">
                {{ site.name }} is a collection of free online calculators and converters — loan EMI, Pakistan salary
                tax, zakat, VAT, land and gold units, BMI, invoices, image and file tools. Everything runs in your
                browser, so your numbers never leave your device.
            </p>
            <p>
                <a class="btn" href="/tools/">Browse all tools</a>
                <a class="btn btn-secondary" href="/tools/salary-tax-calculator/">Try the Tax Calculator</a>
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

        <div class="section-head"><h2>Why {{ site.name }}?</h2></div>
        <div class="prose">
            <p>
                Most calculator sites are slow, cluttered and send your data to a server. {{ site.name }} takes the
                opposite approach: every tool is a small, fast page that does its maths entirely on your device.
                There is nothing to install, no account to create, and nothing to upload.
            </p>
        </div>

        <div class="section-head"><h2>Latest from the blog</h2><a href="/blog/">All articles →</a></div>
        <ul class="tool-grid">
            <ToolTile
                v-for="post in latestPosts"
                :key="post.slug"
                :href="`/blog/${post.slug}/`"
                icon="✎"
                :title="post.title"
            />
        </ul>
    </PublicLayout>
</template>
