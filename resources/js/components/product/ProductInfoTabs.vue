<script setup lang="ts">
import type { ProductSpec, Review } from '@/composables/useProductDetail'
import { starsArray } from '@/composables/useProductDetail'

defineProps<{
    description?: string
    highlights?: string[]
    specs?: ProductSpec[]
    reviews: Review[]
    avgRating: number
    reviewCount: number
}>()
</script>

<template>
    <UTabs :items="[
        { label: 'Deskripsi', slot: 'desc' },
        { label: 'Spesifikasi', slot: 'spec' },
        { label: 'Ulasan', slot: 'review' }
    ]">
        <template #desc>
            <UCard class="mt-4 bg-primary-50 dark:bg-primary-950/40" :ui="{ body: 'p-5' }">
                <div class="prose prose-sm max-w-none dark:prose-invert">
                    <p v-if="description" v-html="description" />
                    <p v-else class="text-gray-600 dark:text-gray-400">
                        Belum ada deskripsi untuk produk ini.
                    </p>
                </div>

                <div v-if="highlights?.length" class="mt-5">
                    <div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Highlight
                    </div>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                        <li
                            v-for="(h, i) in highlights"
                            :key="i"
                            class="flex items-start gap-2 rounded-xl bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-900/40 dark:text-gray-300"
                        >
                            <UIcon name="i-lucide-check-circle-2" class="mt-0.5 size-4 text-primary-500" />
                            <span class="min-w-0">{{ h }}</span>
                        </li>
                    </ul>
                </div>
            </UCard>
        </template>

        <template #spec>
            <UCard class="mt-4 bg-primary-50 dark:bg-primary-950/40" :ui="{ body: 'p-5' }">
                <div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Spesifikasi
                </div>

                <div class="mt-4 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-950/40">
                    <div
                        v-for="(s, i) in (specs ?? [])"
                        :key="i"
                        class="flex items-center justify-between gap-4 p-4"
                    >
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ s.label }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ s.value }}</div>
                    </div>

                    <div v-if="!(specs?.length)" class="p-4 text-sm text-gray-600 dark:text-gray-400">
                        Belum ada spesifikasi untuk produk ini.
                    </div>
                </div>
            </UCard>
        </template>

        <template #review>
            <UCard class="mt-4 bg-primary-50 dark:bg-primary-950/40" :ui="{ body: 'p-5' }">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Ulasan pelanggan
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <div class="flex items-center gap-1">
                                <UIcon
                                    v-for="(filled, i) in starsArray(avgRating)"
                                    :key="i"
                                    name="i-lucide-star"
                                    class="size-4"
                                    :class="filled ? 'text-amber-400' : 'text-gray-300 dark:text-gray-700'"
                                />
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ avgRating.toFixed(1) }} / 5
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                ({{ reviewCount }} ulasan)
                            </div>
                        </div>
                    </div>
                </div>

                <USeparator class="my-5" />

                <div class="space-y-4">
                    <UCard v-for="r in reviews" :key="r.id" class="rounded-2xl" :ui="{ body: 'p-4' }">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ r.name }}</div>
                                    <UBadge v-if="r.verified" color="success" variant="soft" size="xs">
                                        Terverifikasi
                                    </UBadge>
                                </div>

                                <div class="mt-1 flex items-center gap-1">
                                    <UIcon
                                        v-for="i in 5"
                                        :key="i"
                                        name="i-lucide-star"
                                        class="size-4"
                                        :class="i <= r.rating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-700'"
                                    />
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ r.date }}</span>
                                </div>

                                <div v-if="r.title" class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ r.title }}
                                </div>

                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ r.body }}
                                </p>
                            </div>

                            <UButton icon="i-lucide-more-vertical" color="neutral" variant="ghost" size="xs" />
                        </div>
                    </UCard>
                </div>
            </UCard>
        </template>
    </UTabs>
</template>
