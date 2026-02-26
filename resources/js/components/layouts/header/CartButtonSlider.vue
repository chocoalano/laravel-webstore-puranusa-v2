<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useStoreData } from '@/composables/useStoreData'
import { useHeaderSlideover } from '@/composables/useHeaderSlideover'
import type { CartItemData } from '@/composables/useStoreData'

const { cartCount, cartItems } = useStoreData()
const { cartSlideoverOpen, openCartSlideover } = useHeaderSlideover()

const isEmpty = computed(() => cartItems.value.length === 0)

// — Client-only checked state untuk operasi bulk —
const checkedIds = ref<(number | string)[]>([])

const checkedCount = computed(() => checkedIds.value.length)
const allChecked = computed(
    () => cartItems.value.length > 0 && checkedIds.value.length === cartItems.value.length,
)

const effectiveItems = computed(() =>
    checkedCount.value > 0
        ? cartItems.value.filter((i) => checkedIds.value.includes(i.id))
        : cartItems.value,
)

const subtotal = computed(() =>
    effectiveItems.value.reduce((acc, item) => acc + item.price * item.qty, 0),
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
    checkedIds.value = val ? cartItems.value.map((i) => i.id) : []
}

function formatIDR(n: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(n)
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

function incQty(item: CartItemData): void {
    if (isPending(item.id) || !item.inStock) return
    markPending(item.id)
    router.patch(`/cart/items/${item.id}`, { qty: item.qty + 1 }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => clearPending(item.id),
    })
}

function decQty(item: CartItemData): void {
    if (isPending(item.id) || item.qty <= 1) return
    markPending(item.id)
    router.patch(`/cart/items/${item.id}`, { qty: item.qty - 1 }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => clearPending(item.id),
    })
}

function removeItem(item: CartItemData): void {
    if (isPending(item.id)) return
    markPending(item.id)
    checkedIds.value = checkedIds.value.filter((i) => i !== item.id)
    router.delete(`/cart/items/${item.id}`, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => clearPending(item.id),
    })
}

function clearCart(): void {
    checkedIds.value = []
    router.delete('/cart', { preserveState: true, preserveScroll: true })
}

function goToCheckout(): void {
    if (checkedCount.value === 0) return
    router.visit('/checkout', { data: { items: checkedIds.value } })
}
</script>

<template>
    <USlideover v-model:open="cartSlideoverOpen" :portal="true" :ui="{ overlay: 'z-[90]', content: 'z-[100] w-full sm:max-w-md' }"
        title="Keranjang Belanja" description="Cek item, ubah jumlah, lalu checkout.">
        <!-- Trigger -->
        <template #default>
            <UTooltip text="Keranjang">
                <UButton icon="i-lucide-shopping-cart" color="neutral" variant="ghost" class="relative rounded-xl" @click="openCartSlideover">
                    <UBadge v-if="cartCount > 0" :label="String(cartCount)" color="neutral" variant="solid" size="xs"
                        class="absolute -right-0.5 -top-0.5 min-w-4.5 rounded-full px-1.5 text-[10px]" />
                </UButton>
            </UTooltip>
        </template>

        <template #body>
            <div class="flex h-full flex-col">
                <!-- Header ringkas -->
                <div
                    class="mb-3 rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ isEmpty ? 'Keranjang kosong' : `${cartItems.length} item` }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ checkedCount > 0 ? `${checkedCount} dipilih` : 'Subtotal menghitung semua' }}
                            </p>
                        </div>

                        <div v-if="!isEmpty" class="flex items-center gap-2">
                            <div
                                class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white/70 px-2.5 py-1.5 dark:border-gray-800 dark:bg-gray-950/30">
                                <UCheckbox :model-value="allChecked" @update:model-value="toggleAll" />
                                <span class="text-xs text-gray-600 dark:text-gray-300">Semua</span>
                            </div>
                            <UButton color="neutral" variant="ghost" size="sm" icon="i-lucide-trash-2"
                                class="rounded-xl" aria-label="Kosongkan keranjang" @click="clearCart()" />
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="isEmpty" class="flex flex-1 flex-col items-center justify-center gap-3 py-10 text-center">
                    <div
                        class="grid size-14 place-items-center rounded-2xl border border-dashed border-gray-300 bg-white/60 dark:border-gray-700 dark:bg-gray-950/40">
                        <UIcon name="i-lucide-shopping-cart" class="size-7 text-gray-500 dark:text-gray-400" />
                    </div>
                    <div>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">Belum ada item</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan produk untuk checkout.</p>
                    </div>
                    <UButton to="/products" color="primary" variant="solid" class="rounded-xl">Mulai Belanja</UButton>
                </div>

                <!-- Items -->
                <div v-else class="flex-1 overflow-auto pr-1">
                    <div class="space-y-2">
                        <div v-for="item in cartItems" :key="item.id"
                            class="rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"
                            :class="{ 'pointer-events-none opacity-60': isPending(item.id) }">
                            <!-- ROW 1: checkbox + title -->
                            <div class="flex items-center gap-2">
                                <UCheckbox :model-value="isItemChecked(item.id)" class="shrink-0"
                                    @update:model-value="(val: boolean) => toggleItem(item.id, val)" />

                                <p class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ item.name }}
                                </p>

                                <UBadge v-if="!item.inStock" label="Habis" color="warning" variant="soft"
                                    size="xs" class="shrink-0 rounded-full" />
                            </div>

                            <!-- ROW 2: image + qty + harga -->
                            <div class="mt-2 flex items-center gap-3">
                                <!-- image -->
                                <div
                                    class="size-11 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                                    <img v-if="item.image" :src="item.image" :alt="item.name"
                                        class="h-full w-full object-cover" loading="lazy" />
                                    <div v-else class="grid h-full w-full place-items-center">
                                        <UIcon name="i-lucide-image" class="size-4 text-gray-400" />
                                    </div>
                                </div>

                                <!-- qty -->
                                <div
                                    class="inline-flex shrink-0 items-center gap-1 rounded-xl border border-gray-200 bg-white/80 p-1 dark:border-gray-800 dark:bg-gray-900/40">
                                    <UButton icon="i-lucide-minus" color="neutral" variant="ghost" size="xs"
                                        class="rounded-lg" aria-label="Kurangi"
                                        :disabled="isPending(item.id) || item.qty <= 1"
                                        @click="decQty(item)" />
                                    <div
                                        class="min-w-7 text-center text-xs font-semibold text-gray-900 dark:text-white">
                                        {{ item.qty }}
                                    </div>
                                    <UButton icon="i-lucide-plus" color="neutral" variant="ghost" size="xs"
                                        class="rounded-lg" aria-label="Tambah"
                                        :disabled="isPending(item.id) || !item.inStock"
                                        @click="incQty(item)" />
                                </div>

                                <!-- harga (unit) -->
                                <div class="min-w-0 flex-1 text-right">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Harga</p>
                                    <p class="truncate text-xs font-semibold text-gray-900 dark:text-white">
                                        {{ formatIDR(item.price) }}
                                    </p>
                                </div>
                            </div>

                            <!-- ROW 3: variant + total + delete -->
                            <div class="mt-2 flex items-center justify-between gap-2">
                                <p class="min-w-0 truncate text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ item.variant || '—' }}
                                </p>

                                <div class="flex items-center gap-2">
                                    <div class="text-right">
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Total</p>
                                        <p class="whitespace-nowrap text-xs font-bold text-gray-900 dark:text-white">
                                            {{ formatIDR(item.rowTotal) }}
                                        </p>
                                    </div>

                                    <UButton icon="i-lucide-trash-2" color="error" variant="ghost" size="xs"
                                        class="rounded-xl" aria-label="Hapus"
                                        :disabled="isPending(item.id)"
                                        @click="removeItem(item)" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="!isEmpty" class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                    <div
                        class="rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Subtotal
                                <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                    ({{ checkedCount > 0 ? 'dipilih' : 'semua' }})
                                </span>
                            </p>
                            <p class="whitespace-nowrap text-base font-bold text-gray-900 dark:text-white">
                                {{ formatIDR(subtotal) }}
                            </p>
                        </div>

                        <div class="mt-3">
                            <UButton color="primary" variant="solid" class="rounded-xl" block
                                :disabled="checkedCount === 0" @click="goToCheckout()">
                                Checkout
                                <span v-if="checkedCount > 0" class="ml-1 opacity-70">({{ checkedCount }})</span>
                            </UButton>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </USlideover>
</template>
