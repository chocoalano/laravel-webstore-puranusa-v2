<script setup lang="ts">
defineProps<{
    avgRating: number
    reviewCount: number
}>()
</script>

<template>
    <UCard :ui="{ body: 'p-5' }" class="bg-primary-50 dark:bg-primary-950/40">
        <div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
            Ringkasan
        </div>
        <div class="mt-4 grid gap-2">
            <div class="flex items-center justify-between gap-3 text-sm">
                <span class="text-gray-600 dark:text-gray-400">Ulasan</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ reviewCount }}</span>
            </div>
            <div class="flex items-center justify-between gap-3 text-sm">
                <span class="text-gray-600 dark:text-gray-400">Rating</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ avgRating.toFixed(1) }}</span>
            </div>
        </div>
    </UCard>
</template>
