<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Brand {
    name: string
    slug: string
    productCount: number
}

const props = defineProps<{
    brands?: Brand[]
}>()

/** Fallback ketika database belum terisi brand */
const fallbackBrands: Brand[] = [
    { name: 'Nike', slug: 'nike', productCount: 0 },
    { name: 'Adidas', slug: 'adidas', productCount: 0 },
    { name: 'Samsung', slug: 'samsung', productCount: 0 },
    { name: 'Apple', slug: 'apple', productCount: 0 },
    { name: 'Sony', slug: 'sony', productCount: 0 },
    { name: 'Uniqlo', slug: 'uniqlo', productCount: 0 },
    { name: 'H&M', slug: 'hm', productCount: 0 },
    { name: 'Zara', slug: 'zara', productCount: 0 },
    { name: 'Levi\'s', slug: 'levis', productCount: 0 },
    { name: 'Puma', slug: 'puma', productCount: 0 },
]

/** Gradients untuk inisial brand — diassign via hash nama */
const gradients = [
    'from-blue-500 to-cyan-400',
    'from-violet-500 to-purple-400',
    'from-rose-500 to-pink-400',
    'from-emerald-500 to-teal-400',
    'from-amber-500 to-orange-400',
    'from-indigo-500 to-blue-400',
    'from-fuchsia-500 to-rose-400',
    'from-lime-500 to-green-400',
]

function brandGradient(name: string): string {
    let hash = 0
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash)
    }
    return `bg-linear-to-br ${gradients[Math.abs(hash) % gradients.length]}`
}

/**
 * Pastikan ada cukup item untuk mengisi marquee (min 8 per baris).
 * Jika DB punya sedikit brand, duplikasi hingga cukup.
 */
const source = computed((): Brand[] => {
    const raw = props.brands?.length ? props.brands : fallbackBrands
    if (raw.length >= 8) return raw
    const result: Brand[] = []
    while (result.length < 8) result.push(...raw)
    return result
})

/** Baris 1: urutan normal */
const row1 = computed(() => source.value)
/** Baris 2: dibalik untuk variasi */
const row2 = computed(() => [...source.value].reverse())

const totalBrands = computed(() => props.brands?.length ?? 0)
const totalProducts = computed(() =>
    props.brands?.reduce((sum, b) => sum + b.productCount, 0) ?? 0,
)
</script>

<template>
    <section class="relative overflow-hidden bg-slate-50 py-20 transition-colors duration-500 dark:bg-slate-950 sm:py-24">

        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-40 left-1/4 h-96 w-96 rounded-full bg-indigo-500/10 blur-3xl dark:bg-indigo-600/15"></div>
            <div class="absolute top-1/2 -right-20 h-80 w-80 rounded-full bg-violet-500/10 blur-3xl dark:bg-violet-600/15"></div>
        </div>

        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(99,102,241,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(99,102,241,0.05)_1px,transparent_1px)] bg-size-[48px_48px] dark:bg-[linear-gradient(rgba(99,102,241,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(99,102,241,0.04)_1px,transparent_1px)]">
        </div>

        <div class="pointer-events-none absolute inset-x-0 top-0 h-16 bg-linear-to-b from-slate-50 to-transparent dark:from-slate-950"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-linear-to-t from-slate-50 to-transparent dark:from-slate-950"></div>

        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="relative z-20 mb-12 text-center">
                <div
                    class="mb-4 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <span class="size-1.5 rounded-full bg-indigo-500 shadow-[0_0_6px_1px_rgba(99,102,241,0.5)] dark:bg-indigo-400"></span>
                    Brand Partner
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                    Brand Terpercaya
                </h2>
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                    Produk original dari brand-brand pilihan kami
                </p>

                <div v-if="totalBrands > 0"
                    class="mt-8 inline-flex items-center divide-x divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white/80 backdrop-blur-md dark:divide-white/10 dark:border-white/10 dark:bg-white/5">
                    <div class="px-6 py-3 text-center">
                        <p class="text-xl font-bold text-slate-900 dark:text-white">{{ totalBrands }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Brand</p>
                    </div>
                    <div class="px-6 py-3 text-center">
                        <p class="text-xl font-bold text-slate-900 dark:text-white">{{ totalProducts.toLocaleString('id-ID') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Produk</p>
                    </div>
                    <div class="px-6 py-3 text-center">
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">100%</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Original</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <template v-for="(row, idx) in [row1, row2]" :key="idx">
                <div class="group/row relative flex overflow-hidden">
                    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-32 bg-linear-to-r from-slate-50 to-transparent dark:from-slate-950"></div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-32 bg-linear-to-l from-slate-50 to-transparent dark:from-slate-950"></div>

                    <div :class="[
                        'flex shrink-0 gap-4 group-hover/row:[animation-play-state:paused]',
                        idx === 0 ? 'animate-[marquee_35s_linear_infinite]' : 'animate-[marquee-reverse_42s_linear_infinite]'
                    ]">
                        <Link v-for="(brand, i) in row" :key="`r${idx}-${i}`" :href="`/shop?brand=${brand.slug}`"
                            class="group flex shrink-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 shadow-sm transition-all duration-300 hover:border-indigo-300 hover:bg-indigo-50 dark:border-white/10 dark:bg-white/5 dark:backdrop-blur-sm dark:hover:border-indigo-500/40 dark:hover:bg-indigo-950/40">

                            <div :class="['flex size-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-md', brandGradient(brand.name)]">
                                {{ brand.name.charAt(0).toUpperCase() }}
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold whitespace-nowrap text-slate-800 group-hover:text-indigo-600 dark:text-white/90 dark:group-hover:text-white">
                                    {{ brand.name }}
                                </p>
                                <p v-if="brand.productCount > 0" class="text-[11px] text-slate-500 dark:text-slate-500 dark:group-hover:text-indigo-400">
                                    {{ brand.productCount.toLocaleString('id-ID') }} produk
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div aria-hidden :class="[
                        'flex shrink-0 gap-4 group-hover/row:[animation-play-state:paused]',
                        idx === 0 ? 'animate-[marquee_35s_linear_infinite]' : 'animate-[marquee-reverse_42s_linear_infinite]'
                    ]">
                        <Link v-for="(brand, i) in row" :key="`r${idx}b-${i}`" :href="`/shop?brand=${brand.slug}`"
                            class="group flex shrink-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 shadow-sm transition-all duration-300 hover:border-indigo-300 hover:bg-indigo-50 dark:border-white/10 dark:bg-white/5 dark:backdrop-blur-sm dark:hover:border-indigo-500/40 dark:hover:bg-indigo-950/40">
                            <div :class="['flex size-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-md', brandGradient(brand.name)]">
                                {{ brand.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold whitespace-nowrap text-slate-800 group-hover:text-indigo-600 dark:text-white/90 dark:group-hover:text-white">
                                    {{ brand.name }}
                                </p>
                                <p v-if="brand.productCount > 0" class="text-[11px] text-slate-500 dark:text-slate-500 dark:group-hover:text-indigo-400">
                                    {{ brand.productCount.toLocaleString('id-ID') }} produk
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </template>
        </div>

        <div class="relative z-20 mx-auto mt-12 max-w-screen-2xl px-4 text-center sm:px-6 lg:px-8">
            <UButton to="/shop" color="primary" variant="outline" trailing-icon="i-lucide-arrow-right"
                class="rounded-2xl p-5 border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-500/40 dark:text-zinc-400 dark:hover:border-zinc-400 dark:hover:text-zinc-300">
                Jelajahi Semua Produk
            </UButton>
        </div>
    </section>
</template>

<style>
@keyframes marquee {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

@keyframes marquee-reverse {
    from { transform: translateX(-50%); }
    to { transform: translateX(0); }
}
</style>
