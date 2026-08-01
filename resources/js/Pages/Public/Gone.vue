<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SeoHead from '@/Components/Public/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    path: { type: String, required: true },
});

const site = computed(() => usePage().props.site);
const canonical = computed(() => site.value.url.replace(/\/$/, '') + props.path);
</script>

<template>
    <SeoHead title="Page removed — 410 Gone" :canonical="canonical" description="This page has been permanently removed." />
    <Head>
        <meta name="robots" content="noindex, follow" />
    </Head>

    <PublicLayout>
        <div class="gone-page">
            <h1>This page has been permanently removed</h1>
            <p><code>{{ path }}</code> no longer exists and won't come back — you don't need to check again.</p>
            <p>
                Try <Link href="/tools/">browsing all tools</Link>, or head back to the
                <Link href="/">homepage</Link>.
            </p>
        </div>
    </PublicLayout>
</template>

<style scoped>
.gone-page {
    max-width: 40rem;
    margin: 4rem auto;
    text-align: center;
}
.gone-page h1 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
}
.gone-page code {
    background: rgba(0, 0, 0, 0.06);
    padding: 0.1rem 0.4rem;
    border-radius: 0.25rem;
}
</style>
