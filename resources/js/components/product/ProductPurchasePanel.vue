<script setup lang="ts">
import { inject } from 'vue'
import { formatCurrency } from '@/composables/useProductDetail'
import { PRODUCT_PAGE_KEY } from '@/composables/useProductPage'
import ProductQtyStepper from '@/components/product/ProductQtyStepper.vue'
import ProductActionButtons from '@/components/product/ProductActionButtons.vue'
import ProductTrustSignals from '@/components/product/ProductTrustSignals.vue'

const ctx = inject(PRODUCT_PAGE_KEY)

if (!ctx) {
    throw new Error('ProductPurchasePanel must be used inside a component that provides PRODUCT_PAGE_KEY')
}

const {
    selectedVariant,
    price,
    compareAtPrice,
    inStock,
    discountPercent,
    stockMax,
    qty,
    increaseQty,
    decreaseQty,
    onQtyInput,
    isAddingToCart,
    addedToCart,
    handleAddToCart,
    isInWishlist,
    isToggling,
    justWishlisted,
    handleToggleWishlist,
    isSharing,
    handleShare,
} = ctx

function onQtyModelUpdate(value: number): void {
    qty.value = value
}
</script>

<template>
    <div class="rounded-2xl bg-white/70 p-4 ring-1 ring-gray-200/60 dark:bg-gray-900/40 dark:ring-white/5">
        <!-- Harga & Stok -->
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Harga
                </div>
                <div class="mt-1 text-2xl font-black text-gray-900 dark:text-white">
                    {{ formatCurrency(price) }}
                </div>
                <div v-if="compareAtPrice" class="mt-1 flex items-center gap-2">
                    <span class="text-sm text-gray-400 line-through dark:text-gray-500">
                        {{ formatCurrency(compareAtPrice) }}
                    </span>
                    <UBadge v-if="discountPercent" color="error" variant="soft" size="xs">
                        Hemat {{ discountPercent }}%
                    </UBadge>
                </div>
            </div>

            <div class="space-y-1 text-right">
                <UBadge v-if="!inStock" color="error" variant="soft">Stok habis</UBadge>
                <UBadge v-else color="success" variant="soft">Tersedia</UBadge>
                <div
                    v-if="selectedVariant?.stock !== undefined && inStock"
                    class="text-xs text-gray-500 dark:text-gray-400"
                >
                    Sisa
                    <span class="font-semibold text-gray-700 dark:text-gray-300">
                        {{ selectedVariant.stock }}
                    </span>
                    pcs
                </div>
            </div>
        </div>

        <!-- Jumlah -->
        <div v-if="inStock" class="mt-4">
            <div class="mb-1.5 text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Jumlah
            </div>
            <ProductQtyStepper
                :model-value="qty"
                :max="stockMax"
                :disabled="!inStock"
                @update:model-value="onQtyModelUpdate"
            />
        </div>

        <!-- Tombol Aksi -->
        <div class="mt-4">
            <ProductActionButtons
                :disabled="!inStock"
                :is-adding-to-cart="isAddingToCart"
                :added-to-cart="addedToCart"
                :is-in-wishlist="isInWishlist"
                :is-toggling="isToggling"
                :just-wishlisted="justWishlisted"
                :is-sharing="isSharing"
                @add-to-cart="handleAddToCart"
                @toggle-wishlist="handleToggleWishlist"
                @share="handleShare"
            />
        </div>

        <!-- Trust Signals -->
        <div class="mt-4">
            <ProductTrustSignals />
        </div>
    </div>
</template>
