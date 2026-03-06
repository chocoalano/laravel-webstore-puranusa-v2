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

const getCategoryLink = (slug: string): string => `/shop?category=${encodeURIComponent(slug)}`
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
                <Link v-for="(cat, idx) in shown" :key="cat.id ?? cat.slug"
                    class="group relative block aspect-[4/4.8] overflow-hidden rounded-3xl border border-gray-200/60 bg-white/80 shadow-sm shadow-gray-100 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-gray-200/70 dark:border-white/10 dark:bg-white/5 dark:shadow-none dark:hover:border-white/15 dark:hover:shadow-black/40"
                    :href="getCategoryLink(cat.slug)">

                    <img v-if="cat.image" :src="cat.image" :alt="cat.name"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />

                    <div v-else :class="[
                        'absolute inset-0 grid place-items-center bg-linear-to-br text-white',
                        getCategoryGradient(cat.slug, idx)
                    ]">
                        <UIcon :name="getCategoryIcon(cat.slug)" class="size-10 drop-shadow-lg" />
                    </div>

                    <div
                        class="absolute inset-0 bg-linear-to-t from-black/85 via-black/35 to-black/10 transition-opacity duration-300 group-hover:from-black/75 group-hover:via-black/30 group-hover:to-transparent">
                    </div>

                    <div class="absolute inset-x-0 bottom-0 p-4">
                        <p class="line-clamp-2 text-sm font-semibold text-white drop-shadow-md">
                            {{ cat.name }}
                        </p>
                        <div
                            class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-0.5 text-[11px] font-medium text-gray-700 backdrop-blur-sm">
                            <UIcon name="i-lucide-package-2" class="size-3 shrink-0" />
                            {{ (cat.productCount ?? 0).toLocaleString('id-ID') }} produk
                        </div>
                    </div>

                    <div
                        class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-black/35 px-2 py-1 text-[11px] font-medium text-white opacity-0 backdrop-blur-sm transition-opacity duration-300 group-hover:opacity-100">
                        Lihat
                        <UIcon name="i-lucide-arrow-right" class="size-3.5" />
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
