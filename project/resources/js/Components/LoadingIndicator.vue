<template>
    <div v-if="isLoading" class="absolute inset-0 z-40 flex items-center justify-center bg-background/80 backdrop-blur-sm">
        <div class="flex flex-col items-center space-y-4">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
            <p class="text-sm text-muted-foreground">Loading...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoading = ref(false);

const handleStart = () => {
    isLoading.value = true;
};

const handleFinish = () => {
    isLoading.value = false;
};

onMounted(() => {
    router.on('start', handleStart);
    router.on('finish', handleFinish);
});

onUnmounted(() => {
    router.off('start', handleStart);
    router.off('finish', handleFinish);
});
</script> 