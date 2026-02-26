<script setup lang="ts">
import ProductCard, { type Product } from '@/components/product/ProductCard.vue'

defineProps<{
    products?: Product[]
    /** sembunyikan background internal (gunakan saat di-wrap dengan shared background) */
    hideBackground?: boolean
}>()
</script>

<template>
    <section class="relative pb-16 pt-2 sm:pb-20 sm:pt-4" :class="{ 'overflow-hidden': !hideBackground }">
        <!-- Background decorations (dirender hanya jika standalone) -->
        <div v-if="!hideBackground" class="pointer-events-none absolute inset-0 -z-10">
            <div
                class="absolute inset-0 bg-linear-to-tl from-indigo-50/70 via-white to-violet-50/50 dark:from-indigo-950/40 dark:via-gray-950 dark:to-purple-950/30">
            </div>
            <div
                class="absolute -top-32 -left-16 h-96 w-96 rounded-full bg-indigo-300/25 blur-3xl dark:bg-indigo-700/15">
            </div>
            <div
                class="absolute -bottom-24 -right-16 h-80 w-80 rounded-full bg-violet-300/25 blur-3xl dark:bg-violet-700/15">
            </div>
            <div
                class="absolute inset-0 bg-[radial-gradient(circle,#6366f125_1px,transparent_1px)] bg-size-[28px_28px] dark:bg-[radial-gradient(circle,#6366f115_1px,transparent_1px)]">
            </div>
        </div>

        <!-- Pembatas halus antara dua section -->
        <div class="mx-auto mb-10 max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="border-t border-indigo-100/60 dark:border-white/5"></div>
        </div>

        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex items-end justify-between gap-4 sm:mb-10">
                <div class="min-w-0">
                    <!-- Eyebrow -->
                    <div
                        class="mb-3 inline-flex items-center gap-1.5 rounded-full border border-indigo-200/70 bg-indigo-50/90 px-3 py-1 text-xs font-medium text-indigo-600 backdrop-blur-sm dark:border-indigo-700/40 dark:bg-indigo-950/60 dark:text-indigo-400">
                        <UIcon name="i-lucide-trending-up" class="size-3.5" />
                        Terlaris Bulan Ini
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                        Produk Unggulan
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Produk terlaris pilihan pelanggan kami
                    </p>
                </div>

                <UButton to="/shop" variant="ghost" color="neutral" trailing-icon="i-lucide-arrow-right"
                    class="hidden shrink-0 sm:flex">
                    Lihat Semua
                </UButton>
            </div>

            <!-- Skeleton loading -->
            <div v-if="products === undefined"
                class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                <div v-for="i in 8" :key="i"
                    class="overflow-hidden rounded-2xl border border-gray-200/50 bg-white/60 sm:rounded-3xl dark:border-white/8 dark:bg-white/5">
                    <div class="aspect-square animate-pulse bg-gray-200/80 dark:bg-white/10"></div>
                    <div class="space-y-2 p-2.5 sm:space-y-3 sm:p-4">
                        <div
                            class="h-2.5 w-20 animate-pulse rounded-full bg-gray-200 sm:h-3 sm:w-24 dark:bg-white/10">
                        </div>
                        <div class="h-3 animate-pulse rounded-full bg-gray-200 sm:h-4 dark:bg-white/10"></div>
                        <div class="h-3 w-3/4 animate-pulse rounded-full bg-gray-200 sm:h-4 dark:bg-white/10"></div>
                        <div class="h-4 w-1/2 animate-pulse rounded-full bg-gray-200 sm:h-5 dark:bg-white/10"></div>
                        <div class="mt-1 h-8 animate-pulse rounded-xl bg-gray-200 dark:bg-white/10"></div>
                    </div>
                </div>
            </div>

            <!-- Grid produk -->
            <div v-else-if="products.length"
                class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                <ProductCard v-for="product in products" :key="product.id" :product="product" class="h-full" />
            </div>

            <!-- CTA bawah -->
            <div class="mt-8 sm:hidden">
                <UButton to="/shop" color="neutral" variant="outline" trailing-icon="i-lucide-arrow-right"
                    class="rounded-2xl p-3" block>
                    Lihat Semua Produk
                </UButton>
            </div>
        </div>
    </section>
</template>
