<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

export interface Product {
  id: number
  slug: string
  name: string
  price: number
  image: string | null
  rating: number
  reviewCount: number
  salesCount: number
  badge?: string | null
}

const props = defineProps<{ product: Product }>()

const formatPrice = (n: number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(n)

const formattedSales = computed(() => {
  const s = props.product.salesCount
  if (s >= 1000) return `${(s / 1000).toFixed(s >= 10000 ? 0 : 1)}rb`
  return String(s)
})

const badgeColor = computed(() => {
  switch (props.product.badge) {
    case 'Terlaris': return 'warning'
    case 'Baru': return 'info'
    default: return 'primary'
  }
})
</script>

<template>
  <!--
    @container wrapper — the card responds to its OWN width, not the viewport.
    Parent grid controls width → card adapts internally.
  -->
  <div class="product-card @container">
    <div
      class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200/80 bg-white transition-all duration-300 hover:shadow-lg hover:border-gray-300/80 dark:border-gray-800/80 dark:bg-gray-900 dark:hover:border-gray-700/80 dark:hover:shadow-gray-950/50"
    >
      <!-- ── Image ─────────────────────────────────────── -->
      <Link
        :href="`/shop/${product.slug}`"
        class="relative block aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800/50"
      >
        <!-- Product image -->
        <img
          v-if="product.image"
          :src="product.image"
          :alt="product.name"
          class="size-full object-cover transition-transform duration-700 ease-out will-change-transform group-hover:scale-[1.06]"
          loading="lazy"
        />

        <!-- Placeholder -->
        <div v-else class="flex size-full items-center justify-center bg-gray-50 dark:bg-gray-800/80">
          <UIcon name="i-lucide-image" class="size-8 text-gray-300 @xs:size-10 dark:text-gray-600" />
        </div>

        <!-- Hover overlay — hidden on touch / small cards -->
        <div
          class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/20 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 @xs:pointer-events-auto"
        >
          <div class="absolute bottom-3 left-1/2 hidden -translate-x-1/2 gap-2 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 @xs:flex">
            <UTooltip text="Lihat detail">
              <UButton
                icon="i-lucide-eye"
                color="neutral"
                variant="solid"
                size="sm"
                class="rounded-full shadow-lg backdrop-blur-sm"
              />
            </UTooltip>
            <UTooltip text="Wishlist">
              <UButton
                icon="i-lucide-heart"
                color="neutral"
                variant="solid"
                size="sm"
                class="rounded-full shadow-lg backdrop-blur-sm"
              />
            </UTooltip>
          </div>
        </div>

        <!-- Badge -->
        <div v-if="product.badge" class="absolute top-2 left-2 @xs:top-3 @xs:left-3">
          <UBadge
            :color="badgeColor"
            variant="solid"
            size="sm"
            :ui="{ root: 'shadow-md font-extrabold uppercase tracking-wider text-[9px] @xs:text-[10px]' }"
          >
            {{ product.badge }}
          </UBadge>
        </div>

        <!-- Wishlist mobile (visible on small cards where overlay is hidden) -->
        <button
          class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-full bg-white/80 text-gray-500 shadow-sm backdrop-blur-sm transition-colors hover:bg-white hover:text-red-500 @xs:hidden dark:bg-gray-900/80 dark:text-gray-400"
          aria-label="Wishlist"
        >
          <UIcon name="i-lucide-heart" class="size-3.5" />
        </button>
      </Link>

      <!-- ── Content ───────────────────────────────────── -->
      <div class="flex flex-1 flex-col p-2.5 @xs:p-3.5 @sm:p-4">

        <!-- Rating row -->
        <div class="mb-1.5 flex items-center justify-between gap-1">
          <div class="flex items-center gap-1">
            <!-- Star icon -->
            <UIcon name="i-lucide-star" class="size-3 @xs:size-3.5 text-amber-400" />
            <span class="text-[10px] font-bold text-gray-700 @xs:text-[11px] dark:text-gray-300">
              {{ product.rating.toFixed(1) }}
            </span>
            <span class="hidden text-[10px] text-gray-400 @xs:inline dark:text-gray-500">
              ({{ product.reviewCount }})
            </span>
          </div>

          <span
            v-if="product.salesCount > 0"
            class="truncate text-[9px] font-medium text-gray-400 @xs:text-[10px] dark:text-gray-500"
          >
            {{ formattedSales }} terjual
          </span>
        </div>

        <!-- Product name -->
        <Link :href="`/shop/${product.slug}`" class="flex-1">
          <h3
            class="line-clamp-2 text-[11px] font-bold leading-snug text-gray-900 transition-colors group-hover:text-primary @xs:text-xs @sm:text-sm dark:text-white dark:group-hover:text-primary"
          >
            {{ product.name }}
          </h3>
        </Link>

        <!-- Price + Cart row -->
        <div class="mt-auto flex items-end justify-between gap-1.5 pt-2 @xs:pt-2.5">
          <div class="min-w-0">
            <p
              class="truncate text-xs font-extrabold tracking-tight text-gray-900 @xs:text-sm @sm:text-base dark:text-white"
            >
              {{ formatPrice(product.price) }}
            </p>
          </div>

          <UTooltip text="Tambah ke keranjang">
            <UButton
              icon="i-lucide-shopping-cart"
              color="primary"
              variant="soft"
              size="sm"
              class="shrink-0 rounded-full transition-transform active:scale-90 @xs:rounded-xl p-2"
            />
          </UTooltip>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/*
  Container query breakpoints — the card adapts to its OWN width.
  Tailwind v4 supports @container natively with the @container class.

  @xs  → ≥ 180px  (small card: 2-col mobile)
  @sm  → ≥ 260px  (medium card: tablet / 3-col)
  @md  → ≥ 360px  (large card: list view or wide grid)

  Usage in template: @xs:text-sm, @sm:p-4, etc.
*/
</style>
