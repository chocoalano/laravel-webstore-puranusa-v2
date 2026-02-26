<script setup lang="ts">
import type { ArticleFilterOption, ArticleSortValue } from '@/types/article'

const props = defineProps<{
    open: boolean
    searchQuery: string
    selectedTag: string
    sortValue: ArticleSortValue
    tagItems: ArticleFilterOption[]
    sortItems: ArticleFilterOption<ArticleSortValue>[]
    isApplying: boolean
    hasActiveFilters: boolean
}>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'update:searchQuery': [value: string]
    'update:selectedTag': [value: string]
    'update:sortValue': [value: ArticleSortValue]
    apply: []
    reset: []
}>()

function closeDrawer(): void {
    emit('update:open', false)
}

function applyAndClose(): void {
    emit('apply')
    closeDrawer()
}

function resetAndClose(): void {
    emit('reset')
    closeDrawer()
}
</script>

<template>
    <UDrawer
        :open="props.open"
        title="Filter Artikel"
        description="Sesuaikan hasil artikel berdasarkan kata kunci, tag, dan urutan."
        :ui="{ overlay: 'z-[80]', content: 'z-[81] max-h-[85dvh]' }"
        @update:open="(value: boolean) => emit('update:open', value)"
    >
        <template #body>
            <div class="space-y-4">
                <UFormField label="Cari Artikel" help="Berdasarkan judul dan SEO">
                    <UInput
                        :model-value="props.searchQuery"
                        icon="i-lucide-search"
                        placeholder="Contoh: omzet"
                        @update:model-value="(value: string) => emit('update:searchQuery', value)"
                    />
                </UFormField>

                <UFormField label="Tag">
                    <USelectMenu
                        :model-value="props.selectedTag"
                        :items="props.tagItems"
                        value-key="value"
                        @update:model-value="(value: string) => emit('update:selectedTag', value)"
                    />
                </UFormField>

                <UFormField label="Urutkan">
                    <USelectMenu
                        :model-value="props.sortValue"
                        :items="props.sortItems"
                        value-key="value"
                        @update:model-value="(value: ArticleSortValue) => emit('update:sortValue', value)"
                    />
                </UFormField>

                <div class="grid grid-cols-2 gap-2 pt-2">
                    <UButton
                        color="neutral"
                        variant="outline"
                        icon="i-lucide-rotate-ccw"
                        :disabled="!props.hasActiveFilters || props.isApplying"
                        @click="resetAndClose"
                    >
                        Reset
                    </UButton>
                    <UButton
                        color="primary"
                        icon="i-lucide-check"
                        :loading="props.isApplying"
                        @click="applyAndClose"
                    >
                        Terapkan
                    </UButton>
                </div>
            </div>
        </template>
    </UDrawer>
</template>
