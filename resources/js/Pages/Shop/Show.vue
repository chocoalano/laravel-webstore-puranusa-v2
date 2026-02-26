<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ProductGallery from '@/components/product/ProductGallery.vue'
import ProductHeader from '@/components/product/ProductHeader.vue'
import ProductInfoTabs from '@/components/product/ProductInfoTabs.vue'
import ProductQuickSpecs from '@/components/product/ProductQuickSpecs.vue'
import ProductRecommendations from '@/components/product/ProductRecommendations.vue'
import ProductVariantPicker from '@/components/product/ProductVariantPicker.vue'
import { useProductPage, type ShowPageProps } from '@/composables/useProductPage'

defineOptions({ layout: AppLayout, inheritAttrs: false })

const props = defineProps<ShowPageProps>()

/**
 * Satu baris: semua state halaman tersedia + di-provide ke komponen turunan.
 * Komponen-komponen child (ProductVariantPicker, ProductMobileBar, dll.) menggunakan
 * inject(PRODUCT_PAGE_KEY) untuk mengambil state yang mereka butuhkan.
 */
const {
    product,
    reviews,
    recommendations,
    galleryItems,
    activeImage,
    discountPercent,
    avgRating,
    reviewCount,
} = useProductPage(props)
</script>

<template>
    <Head :title="`${product.name} | Puranusa`" />

    <div class="min-h-screen mx-auto max-w-screen-2xl bg-gray-50/60 px-4 sm:px-6 lg:px-8 dark:bg-gray-950">
        <!-- Breadcrumb + Judul + Rating -->
        <ProductHeader
            :product="product"
            :avg-rating="avgRating"
            :review-count="reviewCount"
            :in-stock="!!(product.variants?.some((v) => v.inStock))"
        />

        <!-- Grid utama -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <!-- Kiri: Galeri + Tab Info -->
            <div class="lg:col-span-7">
                <ProductGallery
                    :items="galleryItems"
                    :active-index="activeImage"
                    :discount-percent="discountPercent"
                    @update:active-index="activeImage = $event"
                />

                <div class="mt-6">
                    <ProductInfoTabs
                        :description="product.description"
                        :highlights="product.highlights"
                        :specs="product.specs"
                        :reviews="reviews"
                        :avg-rating="avgRating"
                        :review-count="reviewCount"
                    />
                </div>
            </div>

            <!-- Kanan: Varian + Pembelian + Ringkasan (sticky) -->
            <div class="lg:col-span-5">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <!-- Variant picker membungkus ProductPurchasePanel via inject -->
                    <ProductVariantPicker />

                    <ProductQuickSpecs
                        :avg-rating="avgRating"
                        :review-count="reviewCount"
                    />
                </div>
            </div>
        </div>

        <!-- Rekomendasi -->
        <ProductRecommendations :recommendations="recommendations" />
    </div>
</template>
