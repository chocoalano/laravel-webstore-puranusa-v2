<script setup lang="ts">
import type { ArticleCard } from '@/types/article'

const props = defineProps<{
    articles: ArticleCard[]
    isApplying: boolean
}>()

function formatDate(date: string | null): string {
    if (!date) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="props.articles.length === 0" class="rounded-3xl border border-dashed border-default p-10 text-center">
            <div class="mx-auto mb-4 grid size-14 place-items-center rounded-2xl bg-elevated">
                <UIcon name="i-lucide-newspaper" class="size-7 text-muted" />
            </div>
            <p class="text-lg font-semibold text-highlighted">Belum ada artikel ditemukan</p>
            <p class="mt-1 text-sm text-muted">Ubah filter atau kata kunci untuk melihat hasil lain.</p>
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <UCard
                v-for="article in props.articles"
                :key="article.id"
                class="group overflow-hidden rounded-2xl transition-all hover:-translate-y-0.5 hover:shadow-lg"
                :ui="{ body: 'p-0' }"
            >
                <div class="relative h-48 overflow-hidden bg-elevated rounded-lg">
                    <img
                        v-if="article.cover_image"
                        :src="article.cover_image"
                        :alt="article.title"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy"
                    >
                    <div v-else class="grid h-full w-full place-items-center">
                        <UIcon name="i-lucide-image-off" class="size-8 text-muted" />
                    </div>
                    <div class="absolute left-3 top-3 flex items-center gap-2">
                        <UBadge color="neutral" variant="solid" size="xs" class="rounded-full">
                            {{ formatDate(article.published_at) }}
                        </UBadge>
                        <UBadge color="primary" variant="soft" size="xs" class="rounded-full">
                            {{ article.read_time_minutes }} menit
                        </UBadge>
                    </div>
                </div>

                <div class="space-y-4 p-4">
                    <div>
                        <h2 class="line-clamp-2 text-lg font-semibold leading-snug text-highlighted">
                            {{ article.title }}
                        </h2>
                        <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-muted">
                            {{ article.excerpt }}
                        </p>
                    </div>

                    <div v-if="article.tags.length > 0" class="flex flex-wrap gap-1.5">
                        <UBadge
                            v-for="tag in article.tags.slice(0, 3)"
                            :key="`${article.id}-${tag}`"
                            color="primary"
                            variant="subtle"
                            size="xs"
                            class="rounded-full"
                        >
                            {{ tag }}
                        </UBadge>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-muted">
                            Diperbarui {{ article.published_label ?? formatDate(article.published_at) }}
                        </span>
                        <UButton
                            :to="article.url"
                            size="sm"
                            color="primary"
                            variant="outline"
                            trailing-icon="i-lucide-arrow-right"
                            :loading="props.isApplying"
                        >
                            Baca
                        </UButton>
                    </div>
                </div>
            </UCard>
        </div>
    </div>
</template>
