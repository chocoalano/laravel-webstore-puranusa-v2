<script setup lang="ts">
import { ref, onMounted } from 'vue'
import ProductCard from '@/components/product/ProductCard.vue'
import { formatCurrency } from '@/composables/useProductDetail'

const props = defineProps<{
    products: ReturnType<typeof import('@/composables/useShopCatalog').transformProduct>[]
    viewMode: 'grid' | 'list'
    isLoading: boolean
    nextPageUrl: string | null
    loadMore: () => void
}>()

const emit = defineEmits<{
    resetFilters: []
}>()

const sentinel = ref<HTMLElement | null>(null)

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting && props.nextPageUrl && !props.isLoading) {
                props.loadMore()
            }
        },
        { rootMargin: '420px' },
    )
    if (sentinel.value) observer.observe(sentinel.value)
})
</script>

<template>
    <main class="min-w-0 flex-1">
        <template v-if="products.length > 0">
            <!-- Grid View -->
            <div v-if="viewMode === 'grid'" class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4 lg:gap-5">
                <ProductCard v-for="product in products" :key="product.id" :product="product" />
            </div>

            <!-- List View -->
            <div v-else class="space-y-3">
                <UCard
                    v-for="product in products"
                    :key="product.id"
                    :ui="{ root: 'group hover:ring-primary/30 transition-all duration-200 shadow-sm', body: 'p-4' }"
                >
                    <div class="flex gap-4">
                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-elevated/50">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center">
                                <UIcon name="i-lucide-image" class="size-6 text-muted" />
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="line-clamp-2 text-sm font-bold text-highlighted transition-colors group-hover:text-primary">
                                        {{ product.name }}
                                    </h3>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <UBadge
                                            v-if="product.badge"
                                            :color="product.badge === 'Baru' ? 'info' : 'warning'"
                                            variant="soft"
                                            size="xs"
                                        >
                                            {{ product.badge }}
                                        </UBadge>
                                        <div v-if="product.rating > 0" class="flex items-center gap-1 text-xs text-muted">
                                            <UIcon name="i-lucide-star" class="size-3.5 text-amber-400" />
                                            <span class="font-semibold text-highlighted">{{ product.rating.toFixed(1) }}</span>
                                            <span>({{ product.reviewCount }})</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-lg font-extrabold text-primary">
                                        {{ formatCurrency(product.price) }}
                                    </p>
                                    <UButton color="primary" size="xs" icon="i-lucide-shopping-cart" class="mt-2">
                                        Beli
                                    </UButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </UCard>
            </div>

            <!-- Sentinel / Load more -->
            <div ref="sentinel" class="py-12 text-center">
                <div v-if="isLoading" class="inline-flex items-center gap-2.5 text-sm text-muted">
                    <UIcon name="i-lucide-loader-2" class="size-4 animate-spin text-primary" />
                    Memuat produk...
                </div>
                <p v-else-if="nextPageUrl === null" class="inline-flex items-center gap-2 text-sm text-muted">
                    <UIcon name="i-lucide-check-circle" class="size-4 text-success" />
                    Anda sudah melihat semua produk
                </p>
            </div>
        </template>

        <!-- Empty State -->
        <UEmpty
            v-else
            icon="i-lucide-search-x"
            title="Tidak Ada Produk"
            description="Coba ubah filter atau kata kunci pencarian untuk menemukan produk yang Anda cari."
            variant="outline"
            size="lg"
            :actions="[
                {
                    label: 'Reset Semua Filter',
                    icon: 'i-lucide-rotate-ccw',
                    color: 'primary' as any,
                    variant: 'soft' as any,
                    onClick: () => emit('resetFilters'),
                }
            ]"
            :ui="{ root: 'py-20 rounded-2xl' }"
        />
    </main>
</template>
