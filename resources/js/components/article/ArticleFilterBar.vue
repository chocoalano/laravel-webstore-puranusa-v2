<script setup lang="ts">
import type { ArticleFilterOption, ArticleSortValue } from '@/types/article'

const props = defineProps<{
    searchQuery: string
    selectedTag: string
    sortValue: ArticleSortValue
    tagItems: ArticleFilterOption[]
    sortItems: ArticleFilterOption<ArticleSortValue>[]
    selectedSortLabel: string
    isApplying: boolean
    hasActiveFilters: boolean
}>()

const emit = defineEmits<{
    'update:searchQuery': [value: string]
    'update:selectedTag': [value: string]
    'update:sortValue': [value: ArticleSortValue]
    apply: []
    reset: []
    openMobile: []
}>()

function onTagChange(value: string): void {
    emit('update:selectedTag', value)
}

function onSortChange(value: ArticleSortValue): void {
    emit('update:sortValue', value)
}
</script>

<template>
    <UCard class="rounded-2xl">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="w-full lg:max-w-sm">
                <UFormField label="Cari Artikel">
                    <UInput
                        :model-value="props.searchQuery"
                        icon="i-lucide-search"
                        placeholder="Contoh: strategi closing"
                        size="lg"
                        class="w-full"
                        @update:model-value="(value: string) => emit('update:searchQuery', value)"
                    />
                </UFormField>
            </div>

            <div class="w-full lg:max-w-xs">
                <UFormField label="Tag">
                    <USelectMenu
                        :model-value="props.selectedTag"
                        :items="props.tagItems"
                        value-key="value"
                        class="w-full"
                        @update:model-value="onTagChange"
                    />
                </UFormField>
            </div>

            <div class="w-full lg:max-w-xs">
                <UFormField label="Urutkan">
                    <USelectMenu
                        :model-value="props.sortValue"
                        :items="props.sortItems"
                        value-key="value"
                        class="w-full"
                        @update:model-value="onSortChange"
                    />
                </UFormField>
            </div>

            <div class="flex gap-2 lg:ml-auto">
                <UButton
                    color="neutral"
                    variant="outline"
                    icon="i-lucide-sliders-horizontal"
                    class="lg:hidden"
                    @click="emit('openMobile')"
                >
                    Filter
                </UButton>

                <UButton
                    color="neutral"
                    variant="outline"
                    icon="i-lucide-rotate-ccw"
                    :disabled="!props.hasActiveFilters || props.isApplying"
                    @click="emit('reset')"
                >
                    Reset
                </UButton>

                <UButton
                    color="primary"
                    icon="i-lucide-check"
                    :loading="props.isApplying"
                    @click="emit('apply')"
                >
                    Terapkan
                </UButton>
            </div>
        </div>
    </UCard>
</template>
