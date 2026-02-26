<script setup lang="ts">
import type { ShopFilters } from '@/composables/useShopCatalog'
import { formatCurrency } from '@/composables/useProductDetail'

const props = defineProps<{
    currentFilters: ShopFilters
    hasActiveFilters: boolean
    activeCategoryLabel: string
    activeBrandLabel: string | null
    currentSortLabel: string
    priceRange: [number, number]
    filterStats: { min_price: number; max_price: number }
    sortOptions: { label: string; onSelect: () => void }[][]
}>()

const search = defineModel<string>('search', { required: true })
const viewMode = defineModel<'grid' | 'list'>('viewMode', { required: true })

const emit = defineEmits<{
    filter: [filters: Partial<ShopFilters>]
    reset: []
    openMobileFilters: []
}>()
</script>

<template>
    <div class="toolbar sticky top-25 z-1 mb-6 rounded-2xl border border-default bg-white/80 p-3 backdrop-blur-xl dark:bg-gray-900/80">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <UInput v-model="search" icon="i-lucide-search" placeholder="Cari produk..." size="md" class="w-full sm:w-72 lg:w-80" />

                <UChip :show="hasActiveFilters" color="primary" size="sm">
                    <UButton
                        icon="i-lucide-sliders-horizontal"
                        color="primary"
                        variant="soft"
                        size="md"
                        class="shrink-0 lg:hidden"
                        @click="emit('openMobileFilters')"
                    />
                </UChip>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center rounded-lg border border-default bg-elevated/50 p-0.5">
                    <UButton
                        icon="i-lucide-grid-3x3"
                        size="xs"
                        :color="viewMode === 'grid' ? 'primary' : 'neutral'"
                        :variant="viewMode === 'grid' ? 'soft' : 'ghost'"
                        @click="viewMode = 'grid'"
                    />
                    <UButton
                        icon="i-lucide-list"
                        size="xs"
                        :color="viewMode === 'list' ? 'primary' : 'neutral'"
                        :variant="viewMode === 'list' ? 'soft' : 'ghost'"
                        @click="viewMode = 'list'"
                    />
                </div>

                <UDropdownMenu :items="sortOptions">
                    <UButton color="neutral" variant="outline" size="sm" trailing-icon="i-lucide-chevron-down">
                        {{ currentSortLabel }}
                    </UButton>
                </UDropdownMenu>

                <UButton
                    v-if="hasActiveFilters"
                    color="neutral"
                    variant="ghost"
                    size="sm"
                    icon="i-lucide-rotate-ccw"
                    @click="emit('reset')"
                >
                    Reset
                </UButton>
            </div>
        </div>

        <div v-if="hasActiveFilters" class="mt-2.5">
            <USeparator class="mb-2.5" />
            <div class="flex flex-wrap gap-1.5">
                <UBadge v-if="currentFilters.search" color="primary" variant="soft" class="gap-1">
                    <UIcon name="i-lucide-search" class="size-3" />
                    "{{ currentFilters.search }}"
                    <UButton icon="i-lucide-x" size="xs" color="neutral" variant="ghost" class="-mr-1! p-0.5!" @click="emit('filter', { search: '' })" />
                </UBadge>

                <UBadge v-if="currentFilters.category" color="primary" variant="soft" class="gap-1">
                    <UIcon name="i-lucide-tag" class="size-3" />
                    {{ activeCategoryLabel }}
                    <UButton icon="i-lucide-x" size="xs" color="neutral" variant="ghost" class="-mr-1! p-0.5!" @click="emit('filter', { category: undefined })" />
                </UBadge>

                <UBadge v-if="currentFilters.brand && activeBrandLabel" color="primary" variant="soft" class="gap-1">
                    <UIcon name="i-lucide-tags" class="size-3" />
                    {{ activeBrandLabel }}
                    <UButton icon="i-lucide-x" size="xs" color="neutral" variant="ghost" class="-mr-1! p-0.5!" @click="emit('filter', { brand: undefined })" />
                </UBadge>

                <UBadge v-if="currentFilters.in_stock" color="success" variant="soft" class="gap-1">
                    <UIcon name="i-lucide-package-check" class="size-3" />
                    Stok tersedia
                    <UButton icon="i-lucide-x" size="xs" color="neutral" variant="ghost" class="-mr-1! p-0.5!" @click="emit('filter', { in_stock: false })" />
                </UBadge>

                <UBadge
                    v-if="(currentFilters.min_price && Number(currentFilters.min_price) > Number(filterStats.min_price)) || (currentFilters.max_price && Number(currentFilters.max_price) < Number(filterStats.max_price))"
                    color="primary"
                    variant="soft"
                    class="gap-1"
                >
                    <UIcon name="i-lucide-banknote" class="size-3" />
                    {{ formatCurrency(priceRange[0]) }} – {{ formatCurrency(priceRange[1]) }}
                    <UButton icon="i-lucide-x" size="xs" color="neutral" variant="ghost" class="-mr-1! p-0.5!" @click="emit('filter', { min_price: filterStats.min_price, max_price: filterStats.max_price })" />
                </UBadge>
            </div>
        </div>
    </div>
</template>

<style scoped>
.toolbar {
    -webkit-backdrop-filter: blur(20px);
}
</style>
