<script setup lang="ts">
import { computed } from 'vue'
import { useDashboardZenner } from '@/composables/useDashboardZenner'
import type { DashboardZennerCategory, DashboardZennerContent } from '@/types/dashboard'
import ZennerHeaderStats from '@/components/dashboard/zenner/ZennerHeaderStats.vue'
import ZennerFilters from '@/components/dashboard/zenner/ZennerFilters.vue'
import ZennerContentList from '@/components/dashboard/zenner/ZennerContentList.vue'

const props = withDefaults(
    defineProps<{
        categories?: DashboardZennerCategory[]
        contents?: DashboardZennerContent[]
    }>(),
    {
        categories: () => [],
        contents: () => [],
    }
)

const {
    selectedCategory,
    searchQuery,
    categoryItems,
    filteredContents,
    totalContents,
    totalCategories,
    formatDate,
    normalizeFileUrl,
} = useDashboardZenner({
    categories: computed(() => props.categories),
    contents: computed(() => props.contents),
})

function onSearchChange(value: string): void {
    searchQuery.value = value
}

function onCategoryChange(value: string): void {
    selectedCategory.value = value
}
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <ZennerHeaderStats
                :total-contents="totalContents"
                :total-categories="totalCategories"
                :shown-count="filteredContents.length"
            />
        </template>

        <div class="space-y-4">
            <ZennerFilters
                :search="searchQuery"
                :selected-category="selectedCategory"
                :category-items="categoryItems"
                @update:search="onSearchChange"
                @update:selected-category="onCategoryChange"
            />

            <ZennerContentList
                :contents="filteredContents"
                :format-date="formatDate"
                :normalize-file-url="normalizeFileUrl"
            />
        </div>
    </UCard>
</template>
