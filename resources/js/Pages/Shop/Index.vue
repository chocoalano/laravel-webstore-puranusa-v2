<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ShopPageHeader from '@/components/shop/ShopPageHeader.vue'
import ShopToolbar from '@/components/shop/ShopToolbar.vue'
import ShopSidebar from '@/components/shop/ShopSidebar.vue'
import ShopProductGrid from '@/components/shop/ShopProductGrid.vue'
import ShopMobileFilters from '@/components/shop/ShopMobileFilters.vue'
import { useShopCatalog, type ShopProps } from '@/composables/useShopCatalog'

defineOptions({ layout: AppLayout, inheritAttrs: false })

const props = defineProps<ShopProps>()

const {
    transformedProducts,
    totalProducts,
    nextPageUrl,
    isLoading,
    loadMore,
    currentFilters,
    search,
    viewMode,
    inStockOnly,
    isFilterDrawerOpen,
    priceRange,
    minPrice,
    maxPrice,
    currentSortLabel,
    activeCategoryLabel,
    activeBrandLabel,
    hasActiveFilters,
    activeFilterCount,
    ratingItems,
    sortOptions,
    handleFilter,
    resetFilters,
    isActiveCat,
    isActiveBrand,
} = useShopCatalog(props)
</script>

<template>

    <Head title="Katalog Produk Premium | Puranusa" />

    <div class="min-h-screen bg-gray-50/60 dark:bg-gray-950 transition-colors duration-300">
        <ShopPageHeader :current-filters="currentFilters" :active-category-label="activeCategoryLabel"
            :total-products="totalProducts" :categories-count="categories.length" :has-active-filters="hasActiveFilters"
            :active-filter-count="activeFilterCount" />

        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 mt-10">
            <ShopToolbar v-model:search="search" v-model:view-mode="viewMode" :current-filters="currentFilters"
                :has-active-filters="hasActiveFilters" :active-category-label="activeCategoryLabel"
                :active-brand-label="activeBrandLabel" :current-sort-label="currentSortLabel" :price-range="priceRange"
                :filter-stats="filterStats" :sort-options="sortOptions" @filter="handleFilter" @reset="resetFilters"
                @open-mobile-filters="isFilterDrawerOpen = true" />

            <div class="flex gap-8 lg:gap-10">
                <ShopSidebar v-model:price-range="priceRange" v-model:in-stock-only="inStockOnly"
                    :categories="categories" :brands="brands" :current-filters="currentFilters"
                    :total-products="totalProducts" :min-price="minPrice" :max-price="maxPrice"
                    :has-active-filters="hasActiveFilters" :active-filter-count="activeFilterCount"
                    :rating-items="ratingItems" :is-active-cat="isActiveCat" :is-active-brand="isActiveBrand"
                    @filter="handleFilter" @reset="resetFilters" />

                <ShopProductGrid :products="transformedProducts" :view-mode="viewMode" :is-loading="isLoading"
                    :next-page-url="nextPageUrl" :load-more="loadMore" @reset-filters="resetFilters" />
            </div>
        </div>

        <ShopMobileFilters v-model:open="isFilterDrawerOpen" v-model:price-range="priceRange"
            v-model:in-stock-only="inStockOnly" :categories="categories" :brands="brands"
            :current-filters="currentFilters" :min-price="minPrice" :max-price="maxPrice" :rating-items="ratingItems"
            :is-active-cat="isActiveCat" :is-active-brand="isActiveBrand" @filter="handleFilter"
            @reset="resetFilters" />
    </div>
</template>
