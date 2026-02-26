<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { Recommendation } from '@/composables/useProductDetail'
import ProductCard from '@/components/product/ProductCard.vue'

defineProps<{
    recommendations: Recommendation[]
}>()
</script>

<template>
    <div class="mt-10">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white">Rekomendasi untuk Anda</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Produk yang sering dibeli bersamaan atau mirip dengan pilihan Anda.
                </p>
            </div>

            <Link href="/shop" class="text-sm font-semibold text-primary-600 hover:underline dark:text-primary-400">
                Lihat semua
            </Link>
        </div>

        <UCard class="overflow-hidden bg-primary-50 dark:bg-primary-950/40" :ui="{ body: 'p-4' }">
            <UCarousel :items="recommendations" class="w-full" :ui="{ container: 'gap-4' }">
                <template #default="{ item }">
                    <ProductCard :product="item" class="block w-72" />
                </template>
            </UCarousel>
        </UCard>
    </div>
</template>
