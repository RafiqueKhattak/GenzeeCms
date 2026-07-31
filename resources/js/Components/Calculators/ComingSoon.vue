<script setup>
defineProps({
    title: { type: String, required: true },
});
</script>

<template>
    <div class="tool-card">
        <p class="lead">The interactive {{ title }} is being rebuilt as a native component and will be back shortly. The guide below still explains the full method and formula.</p>
    </div>
</template>
