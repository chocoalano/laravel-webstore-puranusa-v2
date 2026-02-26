<script setup lang="ts">
import type { DashboardOrder } from '@/types/dashboard'

type SortValue = 'newest' | 'oldest' | 'highest' | 'lowest'

const props = defineProps<{
    shownCount: number
    totalCount: number
    q: string
    status: DashboardOrder['status'] | 'all'
    sort: SortValue
    statusItems: Array<{ label: string; value: string }>
    sortItems: Array<{ label: string; value: SortValue }>
}>()

const emit = defineEmits<{
    (e: 'update:q', value: string): void
    (e: 'update:status', value: DashboardOrder['status'] | 'all'): void
    (e: 'update:sort', value: SortValue): void
    (e: 'reset'): void
}>()

function onSearchUpdate(value: string | number): void {
    emit('update:q', String(value ?? ''))
}

function onStatusUpdate(value: DashboardOrder['status'] | 'all'): void {
    emit('update:status', value)
}

function onSortUpdate(value: SortValue): void {
    emit('update:sort', value)
}
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex size-10 items-center justify-center rounded-2xl border border-default bg-elevated/60">
                <UIcon name="i-lucide-package-search" class="size-5 text-primary" />
            </div>

            <div class="min-w-0">
                <p class="text-base font-semibold text-highlighted">Order</p>
                <p class="mt-0.5 text-xs text-muted">
                    {{ shownCount }} dari {{ totalCount }} pesanan
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
            <UInput
                :model-value="props.q"
                icon="i-lucide-search"
                placeholder="Cari kode, nama, resi..."
                size="sm"
                class="w-full sm:w-64"
                @update:model-value="onSearchUpdate"
            />

            <USelectMenu
                :model-value="props.status"
                :items="props.statusItems"
                value-key="value"
                label-key="label"
                size="sm"
                class="w-full sm:w-44"
                @update:model-value="onStatusUpdate"
            />

            <USelectMenu
                :model-value="props.sort"
                :items="props.sortItems"
                value-key="value"
                label-key="label"
                size="sm"
                class="w-full sm:w-44"
                @update:model-value="onSortUpdate"
            />

            <UButton
                v-if="props.q || props.status !== 'all' || props.sort !== 'newest'"
                size="sm"
                color="neutral"
                variant="ghost"
                icon="i-lucide-rotate-ccw"
                @click="emit('reset')"
            >
                Reset
            </UButton>
        </div>
    </div>
</template>
