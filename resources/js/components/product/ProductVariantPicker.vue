<script setup lang="ts">
import { inject } from 'vue'
import type { Variant } from '@/composables/useProductDetail'
import { PRODUCT_PAGE_KEY } from '@/composables/useProductPage'
import ProductPurchasePanel from '@/components/product/ProductPurchasePanel.vue'

defineModel<Variant['id'] | null>('selectedVariantId')

const ctx = inject(PRODUCT_PAGE_KEY)

if (!ctx) {
    throw new Error('ProductVariantPicker must be used inside a component that provides PRODUCT_PAGE_KEY')
}

const { variants, selectedVariantId, selectedVariant, hasRealVariants } = ctx
</script>

<template>
    <UCard :ui="{ body: 'p-5' }" class="bg-primary-50 dark:bg-primary-950/40">
        <!-- Header: label + SKU -->
        <div class="flex items-start justify-between gap-3">
            <div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {{ hasRealVariants ? 'Pilih Varian' : 'Detail Produk' }}
            </div>
            <UBadge v-if="selectedVariant?.sku" color="neutral" variant="subtle" class="font-mono text-[11px]">
                SKU: {{ selectedVariant.sku }}
            </UBadge>
        </div>

        <div class="mt-4 space-y-4">
            <!-- Variant selector — hanya tampil jika ada opsi nyata -->
            <template v-if="hasRealVariants">
                <USelectMenu
                    v-model="selectedVariantId"
                    :items="variants.map((v) => ({ label: v.name, value: v.id }))"
                    value-key="value"
                    label-key="label"
                    placeholder="Pilih varian"
                />

                <div v-if="selectedVariant?.options?.length" class="grid gap-2 sm:grid-cols-2">
                    <div
                        v-for="(opt, i) in selectedVariant.options"
                        :key="i"
                        class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-950/40"
                    >
                        <div class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ opt.name }}
                        </div>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ opt.value }}
                            </div>
                            <UBadge v-if="opt.badge" color="warning" variant="soft" size="xs">
                                {{ opt.badge }}
                            </UBadge>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Panel harga, qty, dan tombol aksi -->
            <ProductPurchasePanel />
        </div>
    </UCard>
</template>
