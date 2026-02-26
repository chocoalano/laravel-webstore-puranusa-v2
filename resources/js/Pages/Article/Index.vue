<script setup lang="ts">
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SeoHead from '@/components/SeoHead.vue'
import type { ArticleIndexPageProps, ArticleSortValue } from '@/types/article'
import { useArticleCatalog } from '@/composables/useArticleCatalog'
import ArticlePageHeader from '@/components/article/ArticlePageHeader.vue'
import ArticleFilterBar from '@/components/article/ArticleFilterBar.vue'
import ArticleFilterDrawer from '@/components/article/ArticleFilterDrawer.vue'
import ArticleCardGrid from '@/components/article/ArticleCardGrid.vue'
import ArticlePagination from '@/components/article/ArticlePagination.vue'

defineOptions({ layout: AppLayout })

const props = defineProps<ArticleIndexPageProps>()

const {
    searchQuery,
    selectedTag,
    sortValue,
    isFilterDrawerOpen,
    isApplying,
    articles,
    pagination,
    stats,
    sortItems,
    tagItems,
    hasActiveFilters,
    selectedSortLabel,
    selectedTagLabel,
    applyCurrentFilters,
    onSearchQueryChange,
    onTagChange,
    onSortChange,
    onPageChange,
    resetFilters,
    openFilterDrawer,
    setFilterDrawerOpen,
} = useArticleCatalog(props)

const structuredDataScripts = computed(() => {
    const payload = props.seo.structured_data ?? []

    return payload.map((item) => JSON.stringify(item))
})

function handleSortChange(value: string): void {
    onSortChange(value as ArticleSortValue)
}
</script>

<template>
    <SeoHead
        :title="props.seo.title"
        :description="props.seo.description"
        :canonical="props.seo.canonical"
        :robots="props.seo.robots"
        :image="props.seo.image ?? undefined"
    />

    <Head>
        <component
            v-for="(script, index) in structuredDataScripts"
            :key="`article-index-ld-${index}`"
            :is="'script'"
            type="application/ld+json"
            v-html="script"
        />
    </Head>

    <div class="min-h-screen bg-gray-50/60 py-8 transition-colors duration-300 dark:bg-gray-950">
        <div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            <ArticlePageHeader :stats="stats" :has-active-filters="hasActiveFilters" />

            <ArticleFilterBar
                :search-query="searchQuery"
                :selected-tag="selectedTag"
                :sort-value="sortValue"
                :tag-items="tagItems"
                :sort-items="sortItems"
                :selected-sort-label="selectedSortLabel"
                :is-applying="isApplying"
                :has-active-filters="hasActiveFilters"
                @update:search-query="onSearchQueryChange"
                @update:selected-tag="onTagChange"
                @update:sort-value="handleSortChange"
                @apply="applyCurrentFilters"
                @reset="resetFilters"
                @open-mobile="openFilterDrawer"
            />

            <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted">
                <p>
                    Menampilkan
                    <span class="font-semibold text-highlighted">{{ pagination.from ?? 0 }} - {{ pagination.to ?? 0 }}</span>
                    dari
                    <span class="font-semibold text-highlighted">{{ pagination.total }}</span>
                    artikel.
                </p>
                <p>
                    Tag: <span class="font-medium text-highlighted">{{ selectedTagLabel }}</span>
                    · Sort: <span class="font-medium text-highlighted">{{ selectedSortLabel }}</span>
                </p>
            </div>

            <ArticleCardGrid :articles="articles" :is-applying="isApplying" />

            <ArticlePagination
                v-if="pagination.last_page > 1"
                :page="pagination.current_page"
                :total="pagination.total"
                :per-page="pagination.per_page"
                :from="pagination.from"
                :to="pagination.to"
                @page-change="onPageChange"
            />
        </div>

        <ArticleFilterDrawer
            :open="isFilterDrawerOpen"
            :search-query="searchQuery"
            :selected-tag="selectedTag"
            :sort-value="sortValue"
            :tag-items="tagItems"
            :sort-items="sortItems"
            :is-applying="isApplying"
            :has-active-filters="hasActiveFilters"
            @update:open="setFilterDrawerOpen"
            @update:search-query="onSearchQueryChange"
            @update:selected-tag="onTagChange"
            @update:sort-value="handleSortChange"
            @apply="applyCurrentFilters"
            @reset="resetFilters"
        />
    </div>
</template>
