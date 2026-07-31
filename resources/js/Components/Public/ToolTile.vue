<script setup>
defineProps({
    href: { type: String, required: true },
    icon: { type: String, default: '•' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <li>
        <a class="tool-tile" :href="href">
            <span class="tool-glyph" aria-hidden="true">{{ icon }}</span>
            <span>
                <span class="tile-title">{{ title }}</span>
                <p>{{ description }}</p>
            </span>
        </a>
    </li>
</template>
