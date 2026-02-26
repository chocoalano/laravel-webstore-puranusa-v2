<script setup lang="ts">
import type { Stats } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const { formatIDR } = useDashboard()

defineProps<{
    stats?: Stats
}>()

defineEmits<{
    navigate: [section: string]
}>()
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <UCard class="rounded-2xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Order Total</p>
                    <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">{{ stats?.orders_total ?? 0 }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Pending: <span class="font-semibold">{{ stats?.orders_pending ?? 0 }}</span>
                    </p>
                </div>
                <div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900">
                    <UIcon name="i-lucide-receipt" class="size-5 text-gray-600 dark:text-gray-300" />
                </div>
            </div>
            <div class="mt-4">
                <UButton color="neutral" variant="outline" size="sm" class="rounded-xl" block
                    @click="$emit('navigate', 'orders')">
                    Lihat Pesanan
                </UButton>
            </div>
        </UCard>

        <UCard class="rounded-2xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Wallet</p>
                    <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">
                        {{ formatIDR(stats?.wallet_balance ?? 0) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Siap dipakai checkout</p>
                </div>
                <div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900">
                    <UIcon name="i-lucide-wallet" class="size-5 text-gray-600 dark:text-gray-300" />
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <UButton color="neutral" variant="outline" size="sm" class="rounded-xl" block
                    @click="$emit('navigate', 'wallet')">
                    Detail
                </UButton>
                <UButton color="primary" variant="soft" size="sm" class="rounded-xl" block
                    @click="$emit('navigate', 'wallet')">
                    Top up
                </UButton>
            </div>
        </UCard>

        <UCard class="rounded-2xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Statistik Jaringan</p>
                    <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">{{ stats?.network_total ?? 0 }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Aktif: <span class="font-semibold">{{ stats?.network_active ?? 0 }}</span>
                        <span class="mx-2 text-gray-400">•</span>
                        Level: <span class="font-semibold">{{ stats?.network_level ?? 0 }}</span>
                    </p>
                </div>
                <div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900">
                    <UIcon name="i-lucide-network" class="size-5 text-gray-600 dark:text-gray-300" />
                </div>
            </div>
            <div class="mt-4">
                <UButton color="neutral" variant="outline" size="sm" class="rounded-xl" block
                    @click="$emit('navigate', 'network')">
                    Buka Network
                </UButton>
            </div>
        </UCard>

        <UCard class="rounded-2xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Statistik Bonus</p>
                    <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">
                        {{ formatIDR(stats?.bonus_available ?? 0) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Bulan ini: <span class="font-semibold">{{ formatIDR(stats?.bonus_month ?? 0) }}</span>
                    </p>
                </div>
                <div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900">
                    <UIcon name="i-lucide-coins" class="size-5 text-gray-600 dark:text-gray-300" />
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <UButton color="neutral" variant="outline" size="sm" class="rounded-xl" block
                    @click="$emit('navigate', 'bonus')">
                    Detail
                </UButton>
                <UButton color="primary" variant="soft" size="sm" class="rounded-xl" block
                    @click="$emit('navigate', 'bonus')">
                    Tarik
                </UButton>
            </div>
        </UCard>
    </div>
</template>
