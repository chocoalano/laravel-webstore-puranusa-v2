<script setup lang="ts">
import type { ShopCategory, ShopBrand, ShopFilters } from '@/composables/useShopCatalog'
import { formatCurrency } from '@/composables/useProductDetail'

const props = defineProps<{
    categories: ShopCategory[]
    brands?: ShopBrand[]
    currentFilters: ShopFilters
    totalProducts: number
    minPrice: number
    maxPrice: number
    hasActiveFilters: boolean
    activeFilterCount: number
    ratingItems: { label: string; value: number | undefined }[]
    isActiveCat: (slug: string | undefined) => boolean
    isActiveBrand: (slug: string) => boolean
}>()

const priceRange = defineModel<[number, number]>('priceRange', { required: true })
const inStockOnly = defineModel<boolean>('inStockOnly', { required: true })

const emit = defineEmits<{
    filter: [filters: Partial<ShopFilters>]
    reset: []
}>()
</script>

<template>
    <aside class="hidden w-72 shrink-0 lg:block mb-10">
        <div class="sticky top-36 space-y-4">
            <!-- Categories -->
            <UCard :ui="{ root: 'shadow-sm overflow-hidden', header: 'px-4 pt-4 pb-3', body: 'p-0' }">
                <template #header>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex size-7 items-center justify-center rounded-md bg-primary-50 dark:bg-primary-950/40">
                                <UIcon name="i-lucide-layout-grid" class="size-3.5 text-primary" />
                            </div>
                            <span class="text-sm font-semibold text-highlighted">Kategori</span>
                        </div>
                        <UBadge color="neutral" variant="subtle" size="xs">{{ totalProducts }}</UBadge>
                    </div>
                </template>

                <UScrollArea class="max-h-72">
                    <div class="flex flex-col gap-px p-2">
                        <button
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors"
                            :class="isActiveCat(undefined)
                                ? 'bg-primary-50 font-semibold text-primary dark:bg-primary-950/40'
                                : 'text-default hover:bg-elevated/60'"
                            @click="emit('filter', { category: undefined })">
                            <span>Semua Kategori</span>
                            <UBadge :color="isActiveCat(undefined) ? 'primary' : 'neutral'" variant="subtle" size="xs">
                                {{ totalProducts
                                }}</UBadge>
                        </button>

                        <button v-for="cat in categories" :key="`cat-${cat.slug}`"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors"
                            :class="isActiveCat(cat.slug)
                                ? 'bg-primary-50 font-semibold text-primary dark:bg-primary-950/40'
                                : 'text-default hover:bg-elevated/60'" @click="emit('filter', { category: cat.slug })">
                            <span class="truncate">{{ cat.name }}</span>
                            <UBadge :color="isActiveCat(cat.slug) ? 'primary' : 'neutral'" variant="subtle" size="xs">{{
                                cat.products_count || 0 }}</UBadge>
                        </button>
                    </div>
                </UScrollArea>
            </UCard>

            <!-- Price Range -->
            <UCard :ui="{ root: 'shadow-sm', header: 'px-4 pt-4 pb-3', body: 'px-4 pb-4 pt-2' }">
                <template #header>
                    <div class="flex items-center gap-2">
                        <div
                            class="flex size-7 items-center justify-center rounded-md bg-success-50 dark:bg-success-950/40">
                            <UIcon name="i-lucide-banknote" class="size-3.5 text-success" />
                        </div>
                        <span class="text-sm font-semibold text-highlighted">Rentang Harga</span>
                    </div>
                </template>

                <USlider v-model="priceRange" range :min="minPrice" :max="maxPrice" :step="10000" color="primary"
                    class="mb-4" />

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-elevated/50 p-3 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Min</p>
                        <p class="mt-0.5 text-sm font-bold tabular-nums text-highlighted">{{
                            formatCurrency(priceRange[0]) }}</p>
                    </div>
                    <div class="rounded-xl bg-elevated/50 p-3 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Maks</p>
                        <p class="mt-0.5 text-sm font-bold tabular-nums text-highlighted">{{
                            formatCurrency(priceRange[1]) }}</p>
                    </div>
                </div>
            </UCard>

            <!-- Brand -->
            <UCard v-if="brands?.length"
                :ui="{ root: 'shadow-sm overflow-hidden', header: 'px-4 pt-4 pb-3', body: 'p-0' }">
                <template #header>
                    <div class="flex items-center gap-2">
                        <div class="flex size-7 items-center justify-center rounded-md bg-info-50 dark:bg-info-950/40">
                            <UIcon name="i-lucide-tags" class="size-3.5 text-info" />
                        </div>
                        <span class="text-sm font-semibold text-highlighted">Brand</span>
                    </div>
                </template>

                <UScrollArea class="max-h-60">
                    <div class="flex flex-col gap-px p-2">
                        <button
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors"
                            :class="!currentFilters.brand
                                ? 'bg-primary-50 font-semibold text-primary dark:bg-primary-950/40'
                                : 'text-default hover:bg-elevated/60'" @click="emit('filter', { brand: undefined })">
                            <span>Semua Brand</span>
                        </button>

                        <button v-for="brand in brands" :key="`brand-${brand.slug}`"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors"
                            :class="isActiveBrand(brand.slug)
                                ? 'bg-primary-50 font-semibold text-primary dark:bg-primary-950/40'
                                : 'text-default hover:bg-elevated/60'" @click="emit('filter', { brand: brand.slug })">
                            <span class="truncate">{{ brand.name }}</span>
                            <UBadge :color="isActiveBrand(brand.slug) ? 'primary' : 'neutral'" variant="subtle"
                                size="xs">{{
                                brand.products_count || 0 }}</UBadge>
                        </button>
                    </div>
                </UScrollArea>
            </UCard>

            <!-- Preferences -->
            <UCard :ui="{ root: 'shadow-sm', header: 'px-4 pt-4 pb-3', body: 'px-4 pb-4 pt-2' }">
                <template #header>
                    <div class="flex items-center gap-2">
                        <div
                            class="flex size-7 items-center justify-center rounded-md bg-warning-50 dark:bg-warning-950/40">
                            <UIcon name="i-lucide-settings-2" class="size-3.5 text-warning" />
                        </div>
                        <span class="text-sm font-semibold text-highlighted">Preferensi</span>
                    </div>
                </template>

                <div class="space-y-3">
                    <label
                        class="flex cursor-pointer items-center justify-between rounded-xl bg-elevated/40 px-3 py-2.5 transition-colors hover:bg-elevated/70">
                        <div class="flex items-center gap-2.5">
                            <UIcon name="i-lucide-package-check" class="size-4 text-success" />
                            <span class="text-sm font-medium text-highlighted">Stok tersedia</span>
                        </div>
                        <USwitch v-model="inStockOnly" color="primary" size="sm" />
                    </label>

                    <div class="rounded-xl bg-elevated/40 px-3 py-2.5">
                        <div class="mb-2 flex items-center gap-2">
                            <UIcon name="i-lucide-star" class="size-4 text-amber-400" />
                            <span class="text-sm font-medium text-highlighted">Rating minimum</span>
                        </div>
                        <USelectMenu :model-value="currentFilters.rating" :items="ratingItems" value-key="value"
                            label-key="label" placeholder="Semua rating" size="sm" class="w-full"
                            @update:model-value="(v: any) => emit('filter', { rating: v })" />
                    </div>
                </div>
            </UCard>

            <!-- Reset -->
            <UButton v-if="hasActiveFilters" block color="neutral" variant="outline" icon="i-lucide-rotate-ccw"
                size="sm" @click="emit('reset')">
                Reset Semua Filter
                <template #trailing>
                    <UBadge color="primary" variant="soft" size="xs">{{ activeFilterCount }}</UBadge>
                </template>
            </UButton>
        </div>
    </aside>
</template>
