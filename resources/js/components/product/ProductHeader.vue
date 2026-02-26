<script setup lang="ts">
import { starsArray, type ProductData } from '@/composables/useProductDetail'

defineProps<{
    product: ProductData
    avgRating: number
    reviewCount: number
    inStock: boolean
}>()
</script>

<template>
    <div class="mb-6 py-8 lg:py-10">
        <UBreadcrumb
            :items="[
                { label: 'Home', icon: 'i-lucide-home', to: '/' },
                { label: 'Katalog', to: '/shop' },
                { label: product.name },
            ]"
        />

        <div class="mt-4 min-w-0">
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">
                {{ product.name }}
            </h1>

            <p v-if="product.shortDescription" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ product.shortDescription }}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <UBadge v-if="product.brand" color="neutral" variant="subtle">
                    <UIcon name="i-lucide-tag" class="mr-1 size-4" />
                    {{ product.brand }}
                </UBadge>

                <div class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300">
                    <UIcon
                        v-for="(filled, i) in starsArray(avgRating)"
                        :key="i"
                        name="i-lucide-star"
                        class="size-4"
                        :class="filled ? 'text-amber-400' : 'text-gray-300 dark:text-gray-700'"
                    />
                    <span class="font-semibold">{{ avgRating.toFixed(1) }}</span>
                    <span class="text-gray-500 dark:text-gray-400">({{ reviewCount }} ulasan)</span>
                </div>

                <UBadge v-if="!inStock" color="error" variant="soft">Stok habis</UBadge>
                <UBadge v-else color="success" variant="soft">Stok tersedia</UBadge>
            </div>
        </div>
    </div>
</template>
