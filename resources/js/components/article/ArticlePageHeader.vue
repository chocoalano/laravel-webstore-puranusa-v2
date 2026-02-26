<script setup lang="ts">
import type { ArticleStats } from '@/types/article'

defineProps<{
    stats: ArticleStats
    hasActiveFilters: boolean
}>()
</script>

<template>
    <UCard class="overflow-hidden rounded-3xl border border-primary-100/60 bg-linear-to-br from-primary-50/70 via-white to-cyan-50/40 dark:border-primary-900/40 dark:from-primary-950/50 dark:via-gray-950 dark:to-cyan-950/20">
        <div class="space-y-6">
            <div class="flex flex-wrap items-center gap-2">
                <UBadge color="primary" variant="soft" size="sm" class="rounded-full">Insight</UBadge>
                <UBadge color="neutral" variant="subtle" size="sm" class="rounded-full">Nuxt UI v4</UBadge>
                <UBadge v-if="hasActiveFilters" color="warning" variant="soft" size="sm" class="rounded-full">
                    Filter aktif
                </UBadge>
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                    Artikel & Wawasan Terbaru
                </h1>
                <p class="max-w-3xl text-sm leading-relaxed text-gray-600 sm:text-base dark:text-gray-300">
                    Kumpulan artikel edukasi, strategi bisnis, dan update produk yang dirancang untuk membantu Anda
                    mengambil keputusan lebih cepat.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Artikel</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ stats.total_articles }}</p>
                </div>
                <div class="rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Hasil Saat Ini</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ stats.result_count }}</p>
                </div>
                <div class="rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tag Tersedia</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ stats.tag_count }}</p>
                </div>
            </div>
        </div>
    </UCard>
</template>
