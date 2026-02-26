<script setup lang="ts">
import type { DashboardZennerContent } from '@/types/dashboard'

defineProps<{
    contents: DashboardZennerContent[]
    formatDate: (value?: string | null) => string
    normalizeFileUrl: (file?: string | null) => string | null
}>()
</script>

<template>
    <UCard
        v-if="contents.length === 0"
        class="rounded-2xl"
    >
        <div class="py-10 text-center text-gray-500 dark:text-gray-400">
            <UIcon name="i-lucide-file-search" class="mx-auto size-10 opacity-40" />
            <p class="mt-3 text-sm">Konten belum tersedia untuk filter ini.</p>
        </div>
    </UCard>

    <div v-else class="grid gap-3">
        <UCard
            v-for="content in contents"
            :key="content.id"
            class="rounded-xl"
        >
            <div class="space-y-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ content.title }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ content.excerpt }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <UBadge v-if="content.category_name" color="neutral" variant="soft" class="rounded-full">
                            {{ content.category_name }}
                        </UBadge>
                        <UBadge color="neutral" variant="soft" class="rounded-full">
                            {{ content.status_label ?? 'Unknown' }}
                        </UBadge>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <p>Diperbarui: {{ formatDate(content.updated_at ?? content.created_at) }}</p>

                    <div class="flex gap-2">
                        <UButton
                            v-if="content.vlink"
                            :to="content.vlink"
                            target="_blank"
                            size="xs"
                            color="primary"
                            variant="outline"
                            icon="i-lucide-play-circle"
                            class="rounded-lg"
                        >
                            Video
                        </UButton>

                        <UButton
                            v-if="normalizeFileUrl(content.file)"
                            :to="normalizeFileUrl(content.file) ?? undefined"
                            target="_blank"
                            size="xs"
                            color="neutral"
                            variant="outline"
                            icon="i-lucide-paperclip"
                            class="rounded-lg"
                        >
                            File
                        </UButton>
                    </div>
                </div>
            </div>
        </UCard>
    </div>
</template>
