<script setup lang="ts">
import type { DashboardLifetimeSummary } from '@/types/dashboard'

defineProps<{
    summary: DashboardLifetimeSummary
    formatCurrency: (value: number) => string
}>()
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <UCard class="rounded-2xl">
            <p class="text-[11px] uppercase tracking-wider text-muted">Omzet Kiri Plan B</p>
            <p class="mt-1 text-lg font-bold text-highlighted">{{ formatCurrency(summary.accumulated_left) }}</p>
        </UCard>
        <UCard class="rounded-2xl">
            <p class="text-[11px] uppercase tracking-wider text-muted">Omzet Kanan Plan B</p>
            <p class="mt-1 text-lg font-bold text-highlighted">{{ formatCurrency(summary.accumulated_right) }}</p>
        </UCard>
        <UCard class="rounded-2xl">
            <p class="text-[11px] uppercase tracking-wider text-muted">Siap Klaim</p>
            <p class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ summary.eligible_count }}</p>
        </UCard>
        <UCard class="rounded-2xl">
            <p class="text-[11px] uppercase tracking-wider text-muted">Sudah Diklaim</p>
            <p class="mt-1 text-lg font-bold text-primary">{{ summary.claimed_count }}</p>
        </UCard>
        <UCard class="rounded-2xl">
            <p class="text-[11px] uppercase tracking-wider text-muted">Sisa Target</p>
            <p class="mt-1 text-lg font-bold text-amber-600 dark:text-amber-400">{{ summary.remaining_count }}</p>
        </UCard>
    </div>
</template>
