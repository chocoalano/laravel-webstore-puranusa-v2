<script setup lang="ts">
import { ref, computed } from 'vue'

interface Banner {
    id: number
    name: string
    description: string | null
    image: string | null
    slug: string | null
    type: string
    code: string
    discount?: number
}

const props = withDefaults(defineProps<{ banners: Banner[] }>(), {
    banners: () => []
})

/** Demo images biar tetap jelas terlihat */
const placeholders: Banner[] = [
    {
        id: 1,
        name: 'Organic & Natural Wellness',
        description: 'Pilihan produk herba dan suplemen alami terbaik untuk menjaga kesehatan tubuh dan pikiran Anda setiap hari.',
        image: 'https://images.unsplash.com/photo-1523473827533-2a64d0f291f0?auto=format&fit=crop&w=2400&q=80',
        slug: '/shop/herba-care',
        type: 'bundle',
        code: 'NATURAL25',
        discount: 25
    },
    {
        id: 2,
        name: 'Radiant Beauty Selection',
        description: 'Rawat diri Anda dengan koleksi skincare dan kosmetik premium. Pancarkan kecantikan alami Anda bersama kami.',
        image: 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=2400&q=80',
        slug: '/shop/beauty-care',
        type: 'new',
        code: 'BEAUTY10'
    },
    {
        id: 3,
        name: 'Therapeutic Health Gear',
        description: 'Dukung proses pemulihan dan kenyamanan Anda dengan alat terapi berkualitas tinggi dan teknologi terkini.',
        image: 'https://images.unsplash.com/photo-1580281658628-48f1cf6b62f0?auto=format&fit=crop&w=2400&q=80',
        slug: '/shop/health-therapy',
        type: 'flash_sale',
        code: 'HEALTH70',
        discount: 70
    }
]

const items = computed(() => (props.banners?.length ? props.banners : placeholders))

const activeIndex = ref(0)
const copiedCode = ref<string | null>(null)

const typeBadge: Record<string, { label: string; icon: string; gradient: string }> = {
    bundle: { label: 'Bundle Hemat', icon: 'i-lucide-package', gradient: 'from-indigo-500 to-violet-600' },
    flash_sale: { label: 'Flash Sale', icon: 'i-lucide-zap', gradient: 'from-orange-500 to-red-600' },
    discount: { label: 'Diskon Spesial', icon: 'i-lucide-percent', gradient: 'from-emerald-500 to-teal-600' },
    new: { label: 'Produk Baru', icon: 'i-lucide-sparkles', gradient: 'from-sky-500 to-blue-600' }
}

const getBadge = (type: string) =>
    typeBadge[type] ?? { label: 'Promo', icon: 'i-lucide-tag', gradient: 'from-pink-500 to-rose-600' }

const bgGradients = [
    'from-indigo-50/60 via-white to-sky-50/60 dark:from-indigo-950/25 dark:via-primary-950 dark:to-sky-950/25',
    'from-rose-50/60 via-white to-amber-50/60 dark:from-rose-950/25 dark:via-primary-950 dark:to-amber-950/25',
    'from-emerald-50/60 via-white to-teal-50/60 dark:from-emerald-950/25 dark:via-primary-950 dark:to-teal-950/25'
]

const splitTitle = (title: string) => {
    const words = (title || '').trim().split(/\s+/).filter(Boolean)
    if (words.length <= 1) return { first: title, last: '' }
    return { first: words.slice(0, -1).join(' '), last: words.at(-1) as string }
}

const hrefOf = (slug?: string | null) => (slug?.startsWith('/') ? slug : slug ? `/${slug}` : '/shop')

const copyPromo = async (code?: string | null) => {
    if (!code) return
    try {
        await navigator.clipboard.writeText(code)
        copiedCode.value = code
        window.setTimeout(() => {
            if (copiedCode.value === code) copiedCode.value = null
        }, 1600)
    } catch {
        const el = document.createElement('textarea')
        el.value = code
        el.style.position = 'fixed'
        el.style.left = '-9999px'
        document.body.appendChild(el)
        el.select()
        document.execCommand('copy')
        document.body.removeChild(el)

        copiedCode.value = code
        window.setTimeout(() => {
            if (copiedCode.value === code) copiedCode.value = null
        }, 1600)
    }
}
</script>

<template>
    <!-- ✅ Tinggi mengikuti layar: pakai SVH + fallback DVH bila support -->
    <section class="relative w-full overflow-hidden h-[100svh] supports-[height:100dvh]:h-[100dvh]" role="region"
        aria-label="Promotional banners">
        <UCarousel v-slot="{ item, index }" :items="items" loop arrows dots fade :autoplay="{ delay: 8000 }"
            @select="(i: number) => (activeIndex = i)" :ui="{
                root: 'relative w-full h-full group',
                viewport: 'overflow-hidden h-full',
                container: 'ms-0 h-full',
                item: 'basis-full ps-0 h-full',
                arrows: 'absolute inset-0 z-30 pointer-events-none',
                prev: 'pointer-events-auto absolute left-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity',
                next: 'pointer-events-auto absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity',
                dots: 'absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white/45 dark:bg-black/20 backdrop-blur-md border border-primary-200/60 dark:border-white/10 ring-1 ring-black/5 dark:ring-white/5',
                dot: 'cursor-pointer size-1.5 rounded-full bg-primary-400/80 dark:bg-primary-600/80 transition-all duration-300 data-[state=active]:bg-primary-600 dark:data-[state=active]:bg-primary-500 data-[state=active]:w-7'
            }">
            <!-- ✅ 1 slide = full tinggi layar -->
            <div class="relative w-full h-full">
                <!-- Background full (tidak pakai aspect ratio lagi) -->
                <div class="absolute inset-0">
                    <img v-if="item.image" :src="item.image" :alt="item.name"
                        class="size-full object-cover scale-[1.01] transition-transform duration-[12s] ease-out motion-reduce:transition-none group-hover:scale-[1.06]"
                        loading="lazy" decoding="async" />

                    <div v-else :class="`size-full bg-gradient-to-br ${bgGradients[index % bgGradients.length]}`">
                        <div class="absolute inset-0 overflow-hidden opacity-35 dark:opacity-20 pointer-events-none">
                            <div
                                class="absolute -left-[10%] -top-[10%] size-[52%] rounded-full bg-primary-400/25 blur-3xl animate-pulse motion-reduce:animate-none" />
                            <div class="absolute -right-[10%] -bottom-[10%] size-[52%] rounded-full bg-indigo-400/25 blur-3xl animate-pulse motion-reduce:animate-none"
                                style="animation-delay: 1.8s" />
                            <div
                                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-[62%] rounded-full border border-primary-500/10 animate-[spin_22s_linear_infinite] motion-reduce:animate-none" />
                        </div>
                    </div>

                    <!-- ✅ Overlay minimal agar image tetap jelas -->
                    <div class="pointer-events-none absolute inset-0
            bg-[radial-gradient(circle_at_22%_18%,rgba(99,102,241,0.10),transparent_20%)]
            dark:bg-[radial-gradient(circle_at_22%_18%,rgba(99,102,241,0.08),transparent_25%)]" />
                    <div
                        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/0 via-black/0 to-black/0 dark:from-black/25 dark:via-black/10 dark:to-black/0" />
                    <div
                        class="pointer-events-none absolute inset-0 shadow-[inset_0_0_0_9999px_rgba(0,0,0,0.03)] dark:shadow-[inset_0_0_0_9999px_rgba(0,0,0,0.10)]" />

                    <!-- bottom fade kecil -->
                    <div
                        class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-white/22 to-transparent dark:from-primary-950/18" />
                </div>

                <!-- Content full height -->
                <div class="relative z-10 h-full">
                    <div class="mx-auto h-full w-full max-w-screen-2xl px-4 sm:px-10 lg:px-20">
                        <div class="grid h-full items-end lg:items-center lg:grid-cols-12 pb-8 sm:pb-10">
                            <!-- Glass panel -->
                            <div class="lg:col-span-7">
                                <div class="
                    relative
                    w-full max-w-[92vw] sm:max-w-2xl lg:max-w-3xl
                    mx-auto lg:mx-0
                    rounded-2xl sm:rounded-3xl
                    border border-white/35 dark:border-white/10
                    bg-white/20 dark:bg-white/4
                    backdrop-blur-md sm:backdrop-blur-sm
                    shadow-[0_18px_70px_-35px_rgba(0,0,0,0.40)]
                    p-4 sm:p-6 lg:p-9
                    overflow-hidden
                  ">
                                    <div class="
                      pointer-events-none absolute inset-0
                      bg-gradient-to-br
                      from-white/14 via-white/6 to-transparent
                      dark:from-white/8 dark:via-transparent dark:to-transparent
                    " />
                                    <div
                                        class="pointer-events-none absolute -top-20 -right-20 size-64 rounded-full bg-white/14 blur-3xl dark:bg-white/5" />
                                    <div
                                        class="pointer-events-none absolute -bottom-24 -left-24 size-72 rounded-full bg-primary-500/10 blur-3xl" />

                                    <div
                                        class="relative flex flex-col gap-4 sm:gap-6 animate-in slide-in-from-bottom-6 duration-700 ease-out">
                                        <!-- badges -->
                                        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                                            <div
                                                :class="`inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r ${getBadge(item.type).gradient} px-3 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-widest text-white shadow-lg`">
                                                <UIcon :name="getBadge(item.type).icon" class="size-4" />
                                                {{ getBadge(item.type).label }}
                                            </div>

                                            <div v-if="item.discount"
                                                class="inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-3 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-widest text-white shadow-sm ring-1 ring-amber-600/25">
                                                <UIcon name="i-lucide-tags" class="size-4" />
                                                Diskon {{ item.discount }}%
                                            </div>

                                            <div
                                                class="hidden sm:flex items-center gap-2 text-[12px] font-semibold text-primary-700/80 dark:text-primary-200/80">
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-xl bg-white/28 dark:bg-white/4 px-3 py-1 border border-white/30 dark:border-white/10">
                                                    <UIcon name="i-lucide-shield-check" class="size-4" />
                                                    Original
                                                </span>
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-xl bg-white/28 dark:bg-white/4 px-3 py-1 border border-white/30 dark:border-white/10">
                                                    <UIcon name="i-lucide-truck" class="size-4" />
                                                    Fast Delivery
                                                </span>
                                            </div>
                                        </div>

                                        <!-- title -->
                                        <div class="space-y-2">
                                            <h2
                                                class="text-2xl sm:text-5xl lg:text-6xl font-black tracking-tight text-primary-950 dark:text-white leading-[1.05]">
                                                {{ splitTitle(item.name).first }}
                                                <span v-if="splitTitle(item.name).last"
                                                    class="block text-primary-600 dark:text-primary-400 italic">
                                                    {{ splitTitle(item.name).last }}
                                                </span>
                                            </h2>

                                            <p
                                                class="max-w-2xl text-sm sm:text-lg font-semibold text-primary-800/90 dark:text-primary-200 leading-relaxed line-clamp-3 sm:line-clamp-none">
                                                {{ item.description }}
                                            </p>
                                        </div>

                                        <!-- actions -->
                                        <div class="grid grid-cols-1 sm:flex sm:flex-row sm:items-center gap-3 pt-1">
                                            <UButton :to="hrefOf(item.slug)" size="xl"
                                                class="rounded-2xl px-8 py-2 font-bold group">
                                                Shop Now
                                                <template #trailing>
                                                    <UIcon name="i-lucide-arrow-right"
                                                        class="size-5 transition-transform duration-200 group-hover:translate-x-1" />
                                                </template>
                                            </UButton>

                                            <UButton v-if="item.code" variant="outline" color="neutral" size="xl"
                                                class="rounded-2xl px-6 py-2 font-bold justify-between"
                                                :aria-label="`Copy promo code ${item.code}`"
                                                @click.prevent="copyPromo(item.code)">
                                                <span class="inline-flex items-center gap-2 min-w-0">
                                                    <UIcon
                                                        :name="copiedCode === item.code ? 'i-lucide-check' : 'i-lucide-copy'"
                                                        class="size-4" />
                                                    <span class="truncate">{{ item.code }}</span>
                                                </span>
                                                <span
                                                    class="text-[11px] font-extrabold uppercase tracking-widest opacity-70"
                                                    aria-live="polite">
                                                    {{ copiedCode === item.code ? 'Copied' : 'Copy' }}
                                                </span>
                                            </UButton>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Counter (desktop) -->
                            <div class="hidden lg:flex lg:col-span-5 justify-end items-center">
                                <div class="flex items-center gap-6">
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="text-[52px] font-black text-primary-900/10 dark:text-white/10 leading-none tabular-nums">
                                            {{ String(activeIndex + 1).padStart(2, '0') }}
                                        </span>
                                        <div
                                            class="h-1 w-16 bg-primary-200/70 dark:bg-white/10 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-600 transition-all duration-300 ease-out"
                                                :style="{ width: `${((activeIndex + 1) / items.length) * 100}%` }" />
                                        </div>
                                    </div>

                                    <div class="h-12 w-px bg-primary-200/80 dark:bg-white/10" />

                                    <div class="text-sm font-extrabold text-primary-500 uppercase tracking-widest">
                                        Explore Items
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </UCarousel>
    </section>
</template>