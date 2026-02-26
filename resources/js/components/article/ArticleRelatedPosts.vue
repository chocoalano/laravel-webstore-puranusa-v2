<script setup lang="ts">
import type { ArticleCard } from '@/types/article'

const props = defineProps<{
    relatedArticles: ArticleCard[]
}>()
</script>

<template>
    <UCard class="rounded-2xl dark:bg-gray-950/80">
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-highlighted sm:text-xl">Artikel Terkait</h2>
                <UButton to="/articles" color="neutral" variant="outline" size="sm" trailing-icon="i-lucide-arrow-right">
                    Semua Artikel
                </UButton>
            </div>

            <div v-if="props.relatedArticles.length === 0" class="rounded-2xl border border-dashed border-default p-6 text-sm text-muted">
                Belum ada artikel terkait.
            </div>

            <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <UCard
                    v-for="article in props.relatedArticles"
                    :key="article.id"
                    class="overflow-hidden rounded-2xl"
                    :ui="{ body: 'p-0' }"
                >
                    <div class="h-36 overflow-hidden bg-elevated">
                        <img
                            v-if="article.cover_image"
                            :src="article.cover_image"
                            :alt="article.title"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                        <div v-else class="grid h-full w-full place-items-center text-muted">
                            <UIcon name="i-lucide-image-off" class="size-6" />
                        </div>
                    </div>

                    <div class="space-y-3 p-3">
                        <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-highlighted">
                            {{ article.title }}
                        </h3>
                        <p class="line-clamp-2 text-xs text-muted">{{ article.excerpt }}</p>
                        <UButton :to="article.url" size="xs" color="primary" variant="outline" trailing-icon="i-lucide-arrow-right">
                            Baca
                        </UButton>
                    </div>
                </UCard>
            </div>
        </div>
    </UCard>
</template>
