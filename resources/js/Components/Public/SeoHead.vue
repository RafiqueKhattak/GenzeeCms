<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: null },
    canonical: { type: String, required: true },
    ogImage: { type: String, default: null },
    jsonLd: { type: Array, default: () => [] },
});

const site = computed(() => usePage().props.site);
const description = computed(() => props.description ?? site.value.metaDescription);
const image = computed(() => props.ogImage ?? site.value.ogImage);
</script>

<template>
    <Head>
        <title>{{ title }}</title>
        <meta name="description" :content="description" />
        <link rel="canonical" :href="canonical" />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" :content="site.name" />
        <meta property="og:title" :content="title" />
        <meta property="og:description" :content="description" />
        <meta property="og:url" :content="canonical" />
        <meta property="og:image" :content="image" />
        <meta name="twitter:card" content="summary_large_image" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
        <link rel="manifest" href="/site.webmanifest" />
        <link rel="preload" href="/assets/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin />
        <link rel="preload" href="/assets/fonts/sora-latin.woff2" as="font" type="font/woff2" crossorigin />
        <link rel="stylesheet" href="/assets/css/style.css" />
        <meta v-if="site.googleSiteVerification" name="google-site-verification" :content="site.googleSiteVerification" />
        <component :is="'script'" v-if="site.googleAnalyticsId" async :src="`https://www.googletagmanager.com/gtag/js?id=${site.googleAnalyticsId}`" />
        <component :is="'script'" v-if="site.googleAnalyticsId">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ site.googleAnalyticsId }}');
        </component>
        <component :is="'script'" v-for="(block, i) in jsonLd" :key="i" type="application/ld+json">{{ JSON.stringify(block) }}</component>
    </Head>
</template>
