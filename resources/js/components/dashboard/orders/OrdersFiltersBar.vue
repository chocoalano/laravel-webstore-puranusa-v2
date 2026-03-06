<script setup lang="ts">
import type { DashboardOrder } from '@/types/dashboard'

type SortValue = 'newest' | 'oldest' | 'highest' | 'lowest'

const props = defineProps<{
    shownCount: number
    totalCount: number
    q: string
    status: DashboardOrder['status'] | 'unpaid' | 'all'
    sort: SortValue
    statusItems: Array<{ label: string; value: string }>
    sortItems: Array<{ label: string; value: SortValue }>
}>()

const emit = defineEmits<{
    (e: 'update:q', value: string): void
    (e: 'update:status', value: DashboardOrder['status'] | 'unpaid' | 'all'): void
    (e: 'update:sort', value: SortValue): void
    (e: 'reset'): void
}>()

function onSearchUpdate(value: string | number): void {
    emit('update:q', String(value ?? ''))
}

function normalizeSelectValue(value: unknown): string {
    if (Array.isArray(value)) {
        return normalizeSelectValue(value[0] ?? '')
    }

    if (typeof value === 'string' || typeof value === 'number') {
        return String(value)
    }

    if (value && typeof value === 'object') {
        const payload = value as Record<string, unknown>

        if (payload.value !== undefined) {
            return String(payload.value ?? '')
        }

        if (payload.id !== undefined) {
            return String(payload.id ?? '')
        }

        if (payload.code !== undefined) {
            return String(payload.code ?? '')
        }

        if (payload.key !== undefined) {
            return String(payload.key ?? '')
        }

        if (typeof payload.label === 'string') {
            return payload.label
        }
    }

    return ''
}

function onStatusUpdate(value: unknown): void {
    const normalized = normalizeSelectValue(value).trim().toLowerCase()
    const allowed: Array<DashboardOrder['status'] | 'unpaid' | 'all'> = ['all', 'unpaid', 'pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']
    const labels: Record<string, DashboardOrder['status'] | 'unpaid' | 'all'> = {
        'semua status': 'all',
        'belum bayar': 'unpaid',
        'menunggu pembayaran': 'unpaid',
        menunggu: 'pending',
        dibayar: 'paid',
        diproses: 'processing',
        dikirim: 'shipped',
        selesai: 'delivered',
        dibatalkan: 'cancelled',
        refund: 'refunded',
    }
    const normalizedStatus = allowed.includes(normalized as DashboardOrder['status'] | 'unpaid' | 'all')
        ? (normalized as DashboardOrder['status'] | 'unpaid' | 'all')
        : labels[normalized] ?? props.status

    emit('update:status', normalizedStatus)
}

function onSortUpdate(value: unknown): void {
    const normalized = normalizeSelectValue(value).trim().toLowerCase()
    const allowed: SortValue[] = ['newest', 'oldest', 'highest', 'lowest']
    const labels: Record<string, SortValue> = {
        terbaru: 'newest',
        terlama: 'oldest',
        'total tertinggi': 'highest',
        'total terendah': 'lowest',
    }
    const normalizedSort = allowed.includes(normalized as SortValue)
        ? (normalized as SortValue)
        : labels[normalized] ?? props.sort

    emit('update:sort', normalizedSort)
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
