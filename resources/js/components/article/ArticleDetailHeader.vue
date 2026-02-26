<script setup lang="ts">
import type { ArticleDetail } from '@/types/article'

defineProps<{
    article: ArticleDetail
    breadcrumbItems: Array<{ label: string; to?: string; icon?: string }>
    publishedDate: string | null
    updatedDate: string | null
    readTimeLabel: string
}>()
</script>

<template>
    <div class="space-y-4">
        <UBreadcrumb :items="breadcrumbItems" />

        <UCard class="overflow-hidden rounded-2xl bg-white/90 dark:bg-gray-950/70">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center gap-2">
                    <UBadge color="primary" variant="soft" size="sm" class="rounded-full">
                        Artikel
                    </UBadge>
                    <UBadge color="neutral" variant="subtle" size="sm" class="rounded-full">
                        {{ readTimeLabel }}
                    </UBadge>
                    <UBadge v-if="publishedDate" color="neutral" variant="subtle" size="sm" class="rounded-full">
                        {{ publishedDate }}
                    </UBadge>
                </div>

                <div class="space-y-2">
                    <h1 class="text-2xl font-bold leading-tight text-highlighted sm:text-4xl">
                        {{ article.title }}
                    </h1>
                    <p class="max-w-3xl text-sm leading-relaxed text-muted sm:text-base">
                        {{ article.excerpt }}
                    </p>
                </div>

                <div v-if="article.tags.length > 0" class="flex flex-wrap gap-2">
                    <UBadge
                        v-for="tag in article.tags"
                        :key="`${article.id}-${tag}`"
                        color="primary"
                        variant="subtle"
                        class="rounded-full"
                    >
                        {{ tag }}
                    </UBadge>
                </div>

                <p v-if="updatedDate" class="text-xs text-muted">
                    Terakhir diperbarui {{ updatedDate }}
                </p>
            </div>
        </UCard>
    </div>
</template>
