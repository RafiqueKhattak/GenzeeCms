<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
    topPages: { type: Array, required: true },
    byType: { type: Array, required: true },
    byCountry: { type: Array, required: true },
    byReferrer: { type: Array, required: true },
    daily: { type: Array, required: true },
    geoAvailable: { type: Boolean, required: true },
});

const days = ref(props.filters.days);
const bots = ref(props.filters.bots);

watch([days, bots], ([d, b]) => {
    router.get(route('admin.analytics.index'), { days: d, bots: b }, { preserveState: true, replace: true });
});

const maxDaily = computed(() => Math.max(1, ...props.daily.map((d) => d.views)));

const typeLabels = {
    home: 'Homepage',
    tool: 'Tool pages',
    'tools-index': 'Tools index',
    blog: 'Blog posts',
    'blog-index': 'Blog index',
    news: 'News articles',
    'news-index': 'News index',
    page: 'Static pages',
};

function pct(value, total) {
    return total > 0 ? Math.round((value / total) * 100) : 0;
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Analytics" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Analytics</h2>
                <div class="flex items-center gap-3">
                    <select v-model.number="days" class="rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option :value="1">Last 24 hours</option>
                        <option :value="7">Last 7 days</option>
                        <option :value="30">Last 30 days</option>
                        <option :value="90">Last 90 days</option>
                    </select>
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <input v-model="bots" type="checkbox" /> Include bots
                    </label>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Page views</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ summary.totalViews.toLocaleString() }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Pages viewed</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ summary.uniquePaths.toLocaleString() }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Bot / crawler hits</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ summary.botViews.toLocaleString() }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Countries</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ summary.countriesSeen || '—' }}</p>
                    </div>
                </div>

                <div v-if="daily.length" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Views per day</h3>
                    <div class="flex h-40 items-end gap-1">
                        <div
                            v-for="d in daily"
                            :key="d.day"
                            class="flex-1 rounded-t bg-indigo-500/80 hover:bg-indigo-500"
                            :style="{ height: Math.max(2, (d.views / maxDaily) * 100) + '%' }"
                            :title="`${d.day}: ${d.views} views`"
                        />
                    </div>
                    <div class="mt-2 flex justify-between text-xs text-gray-400">
                        <span>{{ daily[0]?.day }}</span>
                        <span>{{ daily[daily.length - 1]?.day }}</span>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Most viewed pages</h3>
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 text-left text-xs font-medium uppercase text-gray-500">Page</th>
                                <th class="py-2 text-left text-xs font-medium uppercase text-gray-500">Type</th>
                                <th class="py-2 text-right text-xs font-medium uppercase text-gray-500">Views</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="p in topPages" :key="p.path">
                                <td class="py-2 text-sm">
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ p.title ?? p.path }}</span>
                                    <div v-if="p.title" class="text-xs text-gray-400">{{ p.path }}</div>
                                </td>
                                <td class="py-2 text-sm text-gray-500">{{ typeLabels[p.subject_type] ?? p.subject_type }}</td>
                                <td class="py-2 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">{{ p.views.toLocaleString() }}</td>
                            </tr>
                            <tr v-if="!topPages.length">
                                <td colspan="3" class="py-6 text-center text-sm text-gray-500">
                                    No views recorded yet in this period.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">By section</h3>
                        <ul class="space-y-2 text-sm">
                            <li v-for="t in byType" :key="t.subject_type" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">{{ typeLabels[t.subject_type] ?? t.subject_type }}</span>
                                <b class="text-gray-800 dark:text-gray-200">{{ t.views.toLocaleString() }}</b>
                            </li>
                            <li v-if="!byType.length" class="text-gray-500">No data yet.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-1 font-semibold text-gray-900 dark:text-gray-100">By country</h3>
                        <p v-if="!geoAvailable" class="mb-3 text-xs text-amber-600 dark:text-amber-400">
                            Country data needs a CDN/proxy geo header — see deploy/README.md. Until then everything
                            shows as Unknown.
                        </p>
                        <ul class="space-y-2 text-sm">
                            <li v-for="c in byCountry" :key="c.country_code" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">{{ c.country_code }}</span>
                                <b class="text-gray-800 dark:text-gray-200">{{ c.views.toLocaleString() }} <span class="font-normal text-gray-400">({{ pct(c.views, summary.totalViews) }}%)</span></b>
                            </li>
                            <li v-if="!byCountry.length" class="text-gray-500">No data yet.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Traffic sources</h3>
                        <ul class="space-y-2 text-sm">
                            <li v-for="r in byReferrer" :key="r.referrer_host" class="flex justify-between gap-2">
                                <span class="truncate text-gray-600 dark:text-gray-300">{{ r.referrer_host }}</span>
                                <b class="shrink-0 text-gray-800 dark:text-gray-200">{{ r.views.toLocaleString() }}</b>
                            </li>
                            <li v-if="!byReferrer.length" class="text-gray-500">
                                No external referrers yet — all traffic so far is direct or internal.
                            </li>
                        </ul>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    This log stores no IP addresses and no per-visitor identifiers — only the page path, a coarse
                    country code (when a CDN provides one), the referring domain, and a timestamp.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
