<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useCategories } from '@/composables/useCategories'

interface Category {
    id: number
    slug: string
    name: string
    description: string | null
    image: string | null
    productCount: number
}

const props = defineProps<{
    categories?: Category[]
    /** optional: batas maksimal item yang ditampilkan (default: semua) */
    maxItems?: number
    /** sembunyikan background internal (gunakan saat di-wrap dengan shared background) */
    hideBackground?: boolean
}>()

const { getCategoryIcon, getCategoryGradient } = useCategories()

/** data final yang ditampilkan */
const shown = computed(() => {
    const arr = props.categories ?? []
    const max = typeof props.maxItems === 'number' ? props.maxItems : arr.length
    return arr.slice(0, Math.max(0, max))
})

/**
 * Grid adaptif:
 * - kecil (<=2 item): tampil 2 kolom di mobile
 * - 3-4 item: 2 kolom mobile, 3-4 kolom di sm/md
 * - >=5 item: 2 kolom mobile, 3 sm, 4 md, 6 xl
 */
const gridClass = computed(() => {
    const n = shown.value.length

    if (n <= 1) return 'grid grid-cols-1 gap-4'
    if (n === 2) return 'grid grid-cols-2 gap-4'
    if (n === 3) return 'grid grid-cols-2 sm:grid-cols-3 gap-4'
    if (n === 4) return 'grid grid-cols-2 sm:grid-cols-4 gap-4'

    return 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4 lg:gap-5'
})

/**
 * Padding konten kartu adaptif berdasarkan jumlah item.
 */
const cardPadding = computed(() => {
    const n = shown.value.length
    if (n <= 3) return 'px-7 pt-8 pb-10 sm:px-8 sm:pt-9 sm:pb-11'
    if (n <= 6) return 'px-6 pt-7 pb-9 sm:px-7 sm:pt-8 sm:pb-10'
    return 'px-5 pt-6 pb-8 sm:px-6 sm:pt-7 sm:pb-9'
})

const iconWrapClass = computed(() => {
    const n = shown.value.length
    if (n <= 3) return 'size-16 sm:size-18'
    if (n <= 6) return 'size-14 sm:size-16'
    return 'size-12 sm:size-14'
})

const iconClass = computed(() => {
    const n = shown.value.length
    if (n <= 3) return 'size-7'
    if (n <= 6) return 'size-6'
    return 'size-5'
})
</script>

<template>
    <section v-if="shown.length" class="relative py-16 sm:py-20" :class="{ 'overflow-hidden': !hideBackground }">
        <!-- Background decorations (dirender hanya jika standalone) -->
        <div v-if="!hideBackground" class="pointer-events-none absolute inset-0 -z-10">
            <div
                class="absolute inset-0 bg-linear-to-br from-indigo-50/70 via-white to-violet-50/50 dark:from-indigo-950/40 dark:via-gray-950 dark:to-purple-950/30">
            </div>
            <div
                class="absolute -top-32 -right-16 h-96 w-96 rounded-full bg-violet-300/30 blur-3xl dark:bg-violet-700/15">
            </div>
            <div
                class="absolute -bottom-24 -left-16 h-80 w-80 rounded-full bg-blue-300/30 blur-3xl dark:bg-blue-700/15">
            </div>
            <div
                class="absolute top-1/2 left-1/2 h-125 w-125 -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-100/20 blur-3xl dark:bg-indigo-900/10">
            </div>
            <div
                class="absolute inset-0 bg-[radial-gradient(circle,#6366f125_1px,transparent_1px)] bg-size-[28px_28px] dark:bg-[radial-gradient(circle,#6366f115_1px,transparent_1px)]">
            </div>
        </div>

        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-10 flex items-end justify-between gap-4">
                <div class="min-w-0">
                    <!-- Eyebrow -->
                    <div
                        class="mb-3 inline-flex items-center gap-1.5 rounded-full border border-indigo-200/70 bg-indigo-50/90 px-3 py-1 text-xs font-medium text-indigo-600 backdrop-blur-sm dark:border-indigo-700/40 dark:bg-indigo-950/60 dark:text-indigo-400">
                        <UIcon name="i-lucide-layout-grid" class="size-3.5" />
                        Semua Kategori
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                        Belanja Berdasarkan Kategori
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Temukan produk favorit dari berbagai kategori pilihan
                    </p>
                </div>

                <UButton to="/shop" variant="ghost" color="neutral" trailing-icon="i-lucide-arrow-right"
                    class="hidden sm:flex">
                    Lihat Semua
                </UButton>
            </div>

            <!-- Grid -->
            <div :class="gridClass">
                <Link v-for="(cat, idx) in shown" :key="cat.id ?? cat.slug" :href="`/shop/${cat.slug}`"
                    class="group relative flex flex-col overflow-hidden rounded-3xl border border-gray-200/50 bg-white/75 backdrop-blur-sm shadow-sm shadow-gray-100 transition-all duration-300 hover:-translate-y-1.5 hover:border-gray-200 hover:bg-white hover:shadow-2xl hover:shadow-gray-200/70 dark:border-white/8 dark:bg-white/5 dark:shadow-none dark:hover:border-white/12 dark:hover:bg-white/8 dark:hover:shadow-2xl dark:hover:shadow-black/50">

                    <!-- Top gradient accent strip -->
                    <div :class="['absolute top-0 inset-x-0 h-1 bg-linear-to-r', getCategoryGradient(cat.slug, idx)]">
                    </div>

                    <!-- Hover background tint -->
                    <div :class="[
                        'absolute inset-0 bg-linear-to-br opacity-0 transition-opacity duration-500 group-hover:opacity-[0.06]',
                        getCategoryGradient(cat.slug, idx)
                    ]"></div>

                    <!-- Content -->
                    <div class="relative flex flex-1 flex-col items-center gap-4 text-center" :class="cardPadding">
                        <!-- Icon with glow -->
                        <div class="relative">
                            <!-- Glow halo -->
                            <div :class="[
                                'absolute -inset-3 rounded-3xl bg-linear-to-br opacity-0 blur-xl transition-opacity duration-300 group-hover:opacity-50',
                                getCategoryGradient(cat.slug, idx)
                            ]"></div>
                            <!-- Icon circle -->
                            <div :class="[
                                'relative grid place-items-center overflow-hidden rounded-2xl bg-linear-to-br text-white shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-2xl',
                                iconWrapClass,
                                getCategoryGradient(cat.slug, idx)
                            ]">
                                <!-- Inner top shine -->
                                <div class="absolute inset-0 bg-linear-to-b from-white/25 to-transparent"></div>
                                <UIcon :name="getCategoryIcon(cat.slug)" :class="['relative', iconClass]" />
                            </div>
                        </div>

                        <!-- Text -->
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                {{ cat.name }}
                            </p>
                            <div
                                class="mt-2 inline-flex items-center gap-1 rounded-full bg-gray-100/90 px-2.5 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-white/10 dark:text-gray-400">
                                <UIcon name="i-lucide-package-2" class="size-3 shrink-0" />
                                {{ (cat.productCount ?? 0).toLocaleString('id-ID') }} produk
                            </div>
                        </div>
                    </div>

                    <!-- Hover arrow button -->
                    <div
                        class="absolute right-3 bottom-3 flex size-7 items-center justify-center rounded-full bg-gray-100 opacity-0 transition-all duration-300 group-hover:opacity-100 dark:bg-white/10">
                        <UIcon name="i-lucide-arrow-right"
                            class="size-3.5 -translate-x-0.5 text-gray-500 transition-transform duration-300 group-hover:translate-x-0 dark:text-gray-300" />
                    </div>
                </Link>
            </div>

            <!-- Mobile CTA -->
            <div class="mt-8 sm:hidden">
                <UButton to="/shop" color="neutral" variant="outline" trailing-icon="i-lucide-arrow-right" block
                    class="rounded-2xl p-3">
                    Lihat Semua
                </UButton>
            </div>
        </div>
    </section>
</template>
