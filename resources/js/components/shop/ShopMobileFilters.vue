<script setup lang="ts">
import type { ShopCategory, ShopBrand, ShopFilters } from '@/composables/useShopCatalog'
import { formatCurrency } from '@/composables/useProductDetail'

defineProps<{
    categories: ShopCategory[]
    brands?: ShopBrand[]
    currentFilters: ShopFilters
    minPrice: number
    maxPrice: number
    ratingItems: { label: string; value: number | undefined }[]
    isActiveCat: (slug: string | undefined) => boolean
    isActiveBrand: (slug: string) => boolean
}>()

const isOpen = defineModel<boolean>('open', { required: true })
const priceRange = defineModel<[number, number]>('priceRange', { required: true })
const inStockOnly = defineModel<boolean>('inStockOnly', { required: true })

const emit = defineEmits<{
    filter: [filters: Partial<ShopFilters>]
    reset: []
}>()
</script>

<template>
    <UDrawer v-model:open="isOpen" direction="right" title="Filter Produk" description="Sesuaikan filter untuk hasil terbaik" class="z-5">
        <template #body>
            <div class="space-y-6">
                <div class="mt-10">
                    <p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted">Kategori</p>
                    <div class="grid grid-cols-2 gap-2">
                        <UButton
                            size="sm"
                            :variant="isActiveCat(undefined) ? 'soft' : 'outline'"
                            :color="isActiveCat(undefined) ? 'primary' : 'neutral'"
                            @click="emit('filter', { category: undefined })"
                        >
                            Semua
                        </UButton>
                        <UButton
                            v-for="cat in categories"
                            :key="`dcat-${cat.slug}`"
                            size="sm"
                            :variant="isActiveCat(cat.slug) ? 'soft' : 'outline'"
                            :color="isActiveCat(cat.slug) ? 'primary' : 'neutral'"
                            @click="emit('filter', { category: cat.slug })"
                        >
                            {{ cat.name }}
                        </UButton>
                    </div>
                </div>

                <USeparator />

                <div>
                    <p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted">Rentang Harga</p>
                    <USlider v-model="priceRange" range :min="minPrice" :max="maxPrice" :step="10000" color="primary" />
                    <div class="mt-3 grid grid-cols-2 gap-2.5 text-center">
                        <div class="rounded-lg bg-elevated/50 p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-muted">Min</div>
                            <div class="mt-1 text-sm font-bold tabular-nums text-highlighted">{{ formatCurrency(priceRange[0]) }}</div>
                        </div>
                        <div class="rounded-lg bg-elevated/50 p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-muted">Maks</div>
                            <div class="mt-1 text-sm font-bold tabular-nums text-highlighted">{{ formatCurrency(priceRange[1]) }}</div>
                        </div>
                    </div>
                </div>

                <USeparator />

                <div v-if="brands?.length">
                    <p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted">Brand</p>
                    <div class="grid grid-cols-2 gap-2">
                        <UButton
                            size="sm"
                            :variant="!currentFilters.brand ? 'soft' : 'outline'"
                            :color="!currentFilters.brand ? 'primary' : 'neutral'"
                            @click="emit('filter', { brand: undefined })"
                        >
                            Semua
                        </UButton>
                        <UButton
                            v-for="brand in brands"
                            :key="`dbrand-${brand.slug}`"
                            size="sm"
                            :variant="isActiveBrand(brand.slug) ? 'soft' : 'outline'"
                            :color="isActiveBrand(brand.slug) ? 'primary' : 'neutral'"
                            @click="emit('filter', { brand: brand.slug })"
                        >
                            {{ brand.name }}
                        </UButton>
                    </div>
                </div>

                <USeparator v-if="brands?.length" />

                <div>
                    <p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted">Ketersediaan</p>
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg p-2 transition-colors hover:bg-elevated/50">
                        <UCheckbox v-model="inStockOnly" />
                        <span class="text-sm font-medium text-highlighted">Hanya stok tersedia</span>
                    </label>
                </div>

                <USeparator />

                <div>
                    <p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted">Rating</p>
                    <USelectMenu
                        :model-value="currentFilters.rating"
                        :items="ratingItems"
                        value-key="value"
                        label-key="label"
                        placeholder="Pilih rating"
                        class="w-full"
                        @update:model-value="(v: any) => emit('filter', { rating: v })"
                    />
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex gap-2">
                <UButton block color="primary" @click="isOpen = false">
                    Terapkan Filter
                </UButton>
                <UButton block color="neutral" variant="outline" @click="emit('reset')">
                    Reset
                </UButton>
            </div>
        </template>
    </UDrawer>
</template>
