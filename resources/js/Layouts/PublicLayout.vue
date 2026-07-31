<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    breadcrumbs: { type: Array, default: () => [] },
});

const site = computed(() => usePage().props.site);
const currentUrl = computed(() => usePage().url);

function isActive(href) {
    if (href === '/') return currentUrl.value === '/';
    return currentUrl.value.startsWith(href);
}
</script>

<template>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="container header-inner">
            <Link class="brand" href="/">
                <img :src="site.logo" width="60" height="48" alt="GenzeeLogics logo" fetchpriority="high" />
                Genzee<span class="brand-accent">Logics</span>
            </Link>
            <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Open menu" />
            <label for="nav-toggle" class="nav-toggle-label" aria-hidden="true"><span></span></label>
            <nav class="site-nav" aria-label="Main">
                <Link href="/" :aria-current="isActive('/') && currentUrl === '/' ? 'page' : null">Home</Link>
                <Link href="/tools/" :aria-current="isActive('/tools/') ? 'page' : null">Tools</Link>
                <Link href="/blog/" :aria-current="isActive('/blog/') ? 'page' : null">Blog</Link>
                <Link href="/news/" :aria-current="isActive('/news/') ? 'page' : null">News</Link>
                <Link href="/about/" :aria-current="isActive('/about/') ? 'page' : null">About</Link>
                <Link href="/contact/" :aria-current="isActive('/contact/') ? 'page' : null">Contact</Link>
            </nav>
        </div>
    </header>
    <!-- AD: header-banner -->
    <main id="main">
        <div class="container">
            <nav v-if="breadcrumbs.length" class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li v-for="(crumb, i) in breadcrumbs" :key="i">
                        <Link v-if="i < breadcrumbs.length - 1" :href="crumb.href">{{ crumb.label }}</Link>
                        <template v-else><span aria-current="page">{{ crumb.label }}</span></template>
                    </li>
                </ol>
            </nav>
            <slot />
        </div>
    </main>
    <!-- AD: footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <img :src="site.logo" class="footer-logo" width="75" height="60" alt="GenzeeLogics logo" loading="lazy" />
                    <p class="footer-brand">{{ site.name }}</p>
                    <p>{{ site.tagline }}</p>
                </div>
                <div>
                    <h2>Popular tools</h2>
                    <ul>
                        <li><Link href="/tools/loan-calculator/">Loan / EMI Calculator</Link></li>
                        <li><Link href="/tools/salary-tax-calculator/">Pakistan Salary Tax Calculator</Link></li>
                        <li><Link href="/tools/zakat-calculator/">Zakat Calculator</Link></li>
                        <li><Link href="/tools/vat-calculator/">VAT Calculator</Link></li>
                        <li><Link href="/tools/bmi-calculator/">BMI Calculator</Link></li>
                        <li><Link href="/tools/invoice-generator/">Invoice Generator</Link></li>
                    </ul>
                </div>
                <div>
                    <h2>Explore</h2>
                    <ul>
                        <li><Link href="/tools/">All tools</Link></li>
                        <li><Link href="/blog/">Blog</Link></li>
                        <li><Link href="/news/">News</Link></li>
                        <li><Link href="/about/">About</Link></li>
                        <li><Link href="/contact/">Contact</Link></li>
                    </ul>
                </div>
                <div>
                    <h2>Legal</h2>
                    <ul>
                        <li><Link href="/privacy-policy/">Privacy Policy</Link></li>
                        <li><Link href="/terms/">Terms of Use</Link></li>
                        <li><Link href="/disclaimer/">Disclaimer</Link></li>
                        <li><Link href="/editorial/">Editorial &amp; Team</Link></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ new Date().getFullYear() }} {{ site.name }} &middot; genzeelogics.com</span>
                <span>Results are estimates, not professional advice.</span>
            </div>
        </div>
    </footer>
</template>
