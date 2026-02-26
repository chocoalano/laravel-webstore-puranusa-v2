<script setup lang="ts">
import { computed } from 'vue'
import type { DashboardPromo } from '@/types/dashboard'
import { useDashboardPromo, type DashboardPromoFilterType } from '@/composables/useDashboardPromo'
import PromoHeaderFilters from '@/components/dashboard/promo/PromoHeaderFilters.vue'
import PromoCardsGrid from '@/components/dashboard/promo/PromoCardsGrid.vue'

const props = withDefaults(
    defineProps<{
        promos?: DashboardPromo[]
        loading?: boolean
    }>(),
    {
        promos: () => [],
        loading: false,
    }
)

const {
    searchQuery,
    selectedType,
    onlyAvailable,
    copiedCode,
    typeMeta,
    typeItems,
    selectedTypeIcon,
    filteredPromos,
    formatExpiry,
    copyCode,
    resetFilters,
} = useDashboardPromo({
    promos: computed(() => props.promos),
})

function onSearchQueryChange(value: string): void {
    searchQuery.value = value
}

function onSelectedTypeChange(value: DashboardPromoFilterType): void {
    selectedType.value = value
}

function onOnlyAvailableChange(value: boolean): void {
    onlyAvailable.value = value
}
</script>

<template>
    <UPageCard class="space-y-8 p-1">
        <PromoHeaderFilters
            :filtered-count="filteredPromos.length"
            :search-query="searchQuery"
            :selected-type="selectedType"
            :only-available="onlyAvailable"
            :selected-type-icon="selectedTypeIcon"
            :type-items="typeItems"
            @update:search-query="onSearchQueryChange"
            @update:selected-type="onSelectedTypeChange"
            @update:only-available="onOnlyAvailableChange"
        />

        <PromoCardsGrid
            :loading="loading"
            :promos="filteredPromos"
            :copied-code="copiedCode"
            :type-meta="typeMeta"
            :format-expiry="formatExpiry"
            @copy-code="copyCode"
            @reset-filters="resetFilters"
        />
    </UPageCard>
</template>
