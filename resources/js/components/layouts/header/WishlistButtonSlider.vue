<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useStoreData } from '@/composables/useStoreData'
import { useHeaderSlideover } from '@/composables/useHeaderSlideover'
import type { WishlistItemData } from '@/composables/useStoreData'

const { wishlistCount, wishlistItems } = useStoreData()
const { wishlistSlideoverOpen, openWishlistSlideover } = useHeaderSlideover()

const isEmpty = computed(() => wishlistItems.value.length === 0)

// — Client-only checked state untuk operasi bulk —
const checkedIds = ref<(number | string)[]>([])

const checkedCount = computed(() => checkedIds.value.length)
const allChecked = computed(
    () => wishlistItems.value.length > 0 && checkedIds.value.length === wishlistItems.value.length,
)

function isItemChecked(id: number | string): boolean {
    return checkedIds.value.includes(id)
}

function toggleItem(id: number | string, val: boolean): void {
    if (val) {
        if (!isItemChecked(id)) checkedIds.value = [...checkedIds.value, id]
    } else {
        checkedIds.value = checkedIds.value.filter((i) => i !== id)
    }
}

function toggleAll(val: boolean): void {
    checkedIds.value = val ? wishlistItems.value.map((i) => i.id) : []
}

function formatIDR(n: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(n)
}

// — Loading state per item —
const pendingIds = ref<(number | string)[]>([])

function isPending(id: number | string): boolean {
    return pendingIds.value.includes(id)
}

function markPending(id: number | string): void {
    if (!isPending(id)) pendingIds.value = [...pendingIds.value, id]
}

function clearPending(id: number | string): void {
    pendingIds.value = pendingIds.value.filter((i) => i !== id)
}

function removeItem(item: WishlistItemData): void {
    if (isPending(item.id)) return
    markPending(item.id)
    checkedIds.value = checkedIds.value.filter((i) => i !== item.id)
    router.delete(`/wishlist/items/${item.id}`, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => clearPending(item.id),
    })
}

function removeSelected(): void {
    if (checkedIds.value.length === 0) return
    const ids = [...checkedIds.value]
    router.post('/wishlist/remove-selected', { ids }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { checkedIds.value = [] },
    })
}

function clearWishlist(): void {
    checkedIds.value = []
    router.delete('/wishlist', { preserveState: true, preserveScroll: true })
}

function addToCart(item: WishlistItemData): void {
    if (isPending(item.id) || !item.inStock) return
    markPending(item.id)
    router.post(`/wishlist/items/${item.id}/move-to-cart`, {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => clearPending(item.id),
    })
}

function addSelectedToCart(): void {
    const ids = checkedIds.value.filter((id) => {
        const item = wishlistItems.value.find((i) => i.id === id)
        return item?.inStock
    })
    if (ids.length === 0) return
    router.post('/wishlist/move-to-cart', { ids }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { checkedIds.value = [] },
    })
}
</script>

<template>
    <USlideover v-model:open="wishlistSlideoverOpen" :portal="true" :ui="{ overlay: 'z-[90]', content: 'z-[100] w-full sm:max-w-md' }" title="Wishlist"
        description="Simpan favoritmu, lalu pindahkan ke keranjang saat siap checkout.">
        <!-- Trigger -->
        <template #default>
            <UTooltip text="Wishlist">
                <UButton icon="i-lucide-heart" color="neutral" variant="ghost"
                    class="relative hidden rounded-xl sm:inline-flex" aria-label="Wishlist" @click="openWishlistSlideover">
                    <UBadge v-if="wishlistCount > 0" :label="String(wishlistCount)" color="neutral" variant="solid"
                        size="xs" class="absolute -right-0.5 -top-0.5 min-w-4.5 rounded-full px-1.5 text-[10px]" />
                </UButton>
            </UTooltip>
        </template>

        <!-- BODY -->
        <template #body>
            <div class="flex h-full flex-col">
                <!-- Header bar -->
                <div
                    class="mb-4 rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-2">
                            <div
                                class="grid size-9 shrink-0 place-items-center rounded-xl bg-gray-100 dark:bg-gray-900">
                                <UIcon name="i-lucide-heart" class="size-5 text-gray-700 dark:text-gray-200" />
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ isEmpty ? 'Wishlist kosong' : `${wishlistItems.length} item di wishlist` }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span v-if="isEmpty">Simpan produk favoritmu di sini.</span>
                                    <span v-else>{{ checkedCount > 0 ? `${checkedCount} item dipilih` : 'Pilih item untuk aksi cepat' }}</span>
                                </p>
                            </div>
                        </div>

                        <div v-if="!isEmpty" class="flex flex-wrap items-center gap-2">
                            <div
                                class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white/70 px-3 py-2 dark:border-gray-800 dark:bg-gray-950/30">
                                <UCheckbox :model-value="allChecked" @update:model-value="toggleAll" />
                                <span class="text-xs text-gray-600 dark:text-gray-300">Pilih semua</span>
                            </div>

                            <UButton color="neutral" variant="ghost" size="sm" icon="i-lucide-trash-2"
                                class="rounded-xl" @click="clearWishlist">
                                Kosongkan
                            </UButton>
                        </div>
                    </div>

                    <!-- Bulk actions -->
                    <div v-if="checkedCount > 0" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <UButton color="primary" variant="solid" class="rounded-xl" icon="i-lucide-shopping-cart"
                            block @click="addSelectedToCart">
                            Tambah yang dipilih
                        </UButton>
                        <UButton color="neutral" variant="outline" class="rounded-xl" icon="i-lucide-trash-2"
                            block @click="removeSelected">
                            Hapus yang dipilih
                        </UButton>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="isEmpty" class="flex flex-1 flex-col items-center justify-center gap-3 py-10 text-center">
                    <div
                        class="grid size-14 place-items-center rounded-2xl border border-dashed border-gray-300 bg-white/60 dark:border-gray-700 dark:bg-gray-950/40">
                        <UIcon name="i-lucide-heart" class="size-7 text-gray-500 dark:text-gray-400" />
                    </div>

                    <div>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">Wishlist kamu masih kosong</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Klik ikon hati pada produk untuk
                            menyimpannya.</p>
                    </div>

                    <div class="mt-2 flex flex-wrap justify-center gap-2">
                        <UButton to="/products" color="primary" variant="solid" class="rounded-xl" block>Cari Produk</UButton>
                        <UButton to="/wishlist" color="neutral" variant="outline" class="rounded-xl" block>Buka Wishlist
                        </UButton>
                    </div>
                </div>

                <!-- Items list -->
                <div v-else class="flex-1 overflow-auto pr-1">
                    <div class="space-y-3">
                        <div v-for="item in wishlistItems" :key="item.id"
                            class="group rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur transition hover:bg-white dark:border-gray-800 dark:bg-gray-950/40 dark:hover:bg-gray-950/55"
                            :class="{ 'pointer-events-none opacity-60': isPending(item.id) }">
                            <div class="flex items-start gap-3">
                                <!-- checkbox -->
                                <div class="pt-1">
                                    <UCheckbox :model-value="isItemChecked(item.id)"
                                        @update:model-value="(val: boolean) => toggleItem(item.id, val)" />
                                </div>

                                <!-- image -->
                                <div
                                    class="size-14 shrink-0 overflow-hidden rounded-2xl border border-gray-200 bg-white sm:size-16 dark:border-gray-800 dark:bg-gray-900">
                                    <img v-if="item.image" :src="item.image" :alt="item.name"
                                        class="h-full w-full object-cover" loading="lazy" />
                                    <div v-else class="grid h-full w-full place-items-center">
                                        <UIcon name="i-lucide-image" class="size-5 text-gray-400" />
                                    </div>
                                </div>

                                <!-- meta -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <a v-if="item.slug" :href="`/shop/${item.slug}`"
                                                class="truncate text-sm font-semibold text-gray-900 hover:underline dark:text-white">
                                                {{ item.name }}
                                            </a>
                                            <p v-else class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ item.name }}
                                            </p>
                                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                                {{ item.sku }}
                                            </p>
                                            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ formatIDR(item.price) }}
                                            </p>
                                        </div>

                                        <UBadge v-if="!item.inStock" label="Habis" color="warning"
                                            variant="soft" size="xs" class="shrink-0 rounded-full" />
                                    </div>

                                    <!-- actions -->
                                    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <UButton color="primary" variant="solid" size="sm" class="rounded-xl"
                                            icon="i-lucide-shopping-cart" :disabled="!item.inStock || isPending(item.id)"
                                            block @click="addToCart(item)">
                                            + ke keranjang
                                        </UButton>

                                        <UButton color="error" variant="outline" size="sm" class="rounded-xl"
                                            icon="i-lucide-trash-2" aria-label="Hapus dari wishlist"
                                            :disabled="isPending(item.id)" block @click="removeItem(item)">
                                            Hapus
                                        </UButton>
                                    </div>

                                    <p v-if="!item.inStock"
                                        class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Produk sedang habis. Kamu tetap bisa menyimpannya untuk cek lagi nanti.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer CTA -->
                <div v-if="!isEmpty" class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <UButton to="/products" color="primary" variant="solid" class="rounded-xl" block>Lanjut Belanja
                        </UButton>
                    </div>

                    <div
                        class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <div class="inline-flex items-center gap-1">
                            <UIcon name="i-lucide-heart-handshake" class="size-4" />
                            Simpan favoritmu
                        </div>
                        <div class="inline-flex items-center gap-1">
                            <UIcon name="i-lucide-shopping-cart" class="size-4" />
                            Pindahkan ke keranjang kapan saja
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </USlideover>
</template>
