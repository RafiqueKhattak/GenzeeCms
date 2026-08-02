<script setup>
import { computed } from 'vue';

const props = defineProps({
    author: { type: Object, default: null },
    size: { type: Number, default: 36 },
});

// Deterministic colour per author so the same writer always gets the same
// initials-avatar colour across the site, without storing anything extra.
const PALETTE = ['#7c3aed', '#0891b2', '#c026d3', '#059669', '#d97706', '#dc2626', '#4f46e5', '#0d9488'];

const initials = computed(() => {
    const name = props.author?.name ?? '';
    const words = name.trim().split(/\s+/).filter(Boolean);
    return words.map((w) => w[0]?.toUpperCase() ?? '').slice(0, 2).join('') || '?';
});

const bgColor = computed(() => {
    const name = props.author?.name ?? '';
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return PALETTE[Math.abs(hash) % PALETTE.length];
});
</script>

<template>
    <img
        v-if="author?.avatar_path"
        :src="author.avatar_path"
        :alt="author.name"
        class="author-avatar-img"
        :style="{ width: size + 'px', height: size + 'px' }"
    />
    <span
        v-else
        class="author-avatar-initials"
        :style="{ width: size + 'px', height: size + 'px', background: bgColor, fontSize: Math.round(size * 0.4) + 'px' }"
        aria-hidden="true"
    >
        {{ initials }}
    </span>
</template>

<style scoped>
.author-avatar-img {
    border-radius: 50%;
    object-fit: cover;
    flex: none;
}

.author-avatar-initials {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    font-weight: 600;
    flex: none;
}
</style>
