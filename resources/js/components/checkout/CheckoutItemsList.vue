<script setup lang="ts">
import { reactive } from 'vue'
import type { CartTotals, CheckoutItem } from '@/types/checkout'
import { useCheckout } from '@/composables/useCheckout'

const { formatIDR } = useCheckout()

defineProps<{
    items: CheckoutItem[]
    cart?: CartTotals | null
}>()

const imgErrors = reactive<Record<number | string, boolean>>({})
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Produk yang dibeli</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Pastikan varian, jumlah, dan total sudah sesuai sebelum membayar.
                    </p>
                </div>
                <UBadge :label="`${items.length} produk`" color="neutral" variant="soft" class="rounded-full" />
            </div>
        </template>

        <div v-if="items.length === 0"
            class="rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
            Tidak ada item untuk checkout.
        </div>

        <div v-else class="space-y-3">
            <div v-for="it in items" :key="it.id"
                class="flex w-full items-center gap-3 rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                <div
                    class="size-12 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <img v-if="it.image && !imgErrors[it.id]" :src="it.image" :alt="it.name"
                        class="h-full w-full object-cover" @error="imgErrors[it.id] = true" />
                    <div v-else class="grid h-full w-full place-items-center">
                        <UIcon name="i-lucide-image" class="size-5 text-gray-400" />
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ it.name }}</p>
                    <p v-if="it.variant" class="truncate text-xs text-gray-500 dark:text-gray-400">{{ it.variant }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ formatIDR(it.price) }} × {{ it.qty }}
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                    <p class="whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                        {{ formatIDR(it.row_total) }}
                    </p>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">Total qty</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ items.reduce((acc, it) => acc + it.qty, 0) }} item
                </span>
            </div>
        </template>
    </UCard>
</template>
