<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type { DashboardLifetimeClaimed } from '@/types/dashboard'

defineProps<{
    claimed: DashboardLifetimeClaimed[]
    columns: TableColumn<DashboardLifetimeClaimed>[]
}>()
</script>

<template>
    <UCard class="overflow-hidden rounded-2xl" :ui="{ body: 'p-0 sm:p-0', header: 'px-4 py-4' }">
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-highlighted">Riwayat Klaim Lifetime</h3>
                    <p class="text-xs text-muted">Data klaim reward lifetime dari transaksi bonus reward.</p>
                </div>
            </div>
        </template>

        <UTable :data="claimed" :columns="columns" class="w-full">
            <template #empty>
                <div class="flex flex-col items-center justify-center py-10">
                    <UIcon name="i-lucide-history" class="mb-2 size-8 text-muted" />
                    <p class="text-sm text-muted">Belum ada riwayat klaim lifetime.</p>
                </div>
            </template>
        </UTable>
    </UCard>
</template>
