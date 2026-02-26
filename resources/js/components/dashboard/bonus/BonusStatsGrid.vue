<script setup lang="ts">
import type { DashboardBonusStat } from '@/types/dashboard'

type BonusCard = Pick<DashboardBonusStat, 'key' | 'title' | 'icon' | 'amount' | 'count'>

defineProps<{
    stats: BonusCard[]
    formatCurrency: (value: number) => string
}>()
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <UCard v-for="stat in stats" :key="stat.key" class="rounded-2xl">
            <div class="mb-2 flex items-center gap-2">
                <UIcon :name="stat.icon" class="size-4 text-primary" />
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                    {{ stat.title }}
                </span>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold text-highlighted">{{ formatCurrency(stat.amount) }}</span>
                <span class="text-xs text-muted">{{ stat.count }} transaksi</span>
            </div>
        </UCard>
    </div>
</template>
