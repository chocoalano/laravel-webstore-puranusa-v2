<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import type {
    DashboardWalletTransaction,
    DashboardWalletTransactionStatus,
    DashboardWalletTransactionType,
} from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const props = defineProps<{
    transactions: DashboardWalletTransaction[]
    shownCount: number
    totalCount: number
    isLoadingMore: boolean
    canLoadMore: boolean
}>()

const emit = defineEmits<{
    loadMore: []
}>()

const { formatIDR } = useDashboard()

const typeMeta: Record<DashboardWalletTransactionType, { icon: string; label: string }> = {
    topup: { icon: 'i-lucide-circle-plus', label: 'Top Up Saldo' },
    withdrawal: { icon: 'i-lucide-circle-minus', label: 'Penarikan Saldo' },
    bonus: { icon: 'i-lucide-hand-coins', label: 'Bonus Member' },
    purchase: { icon: 'i-lucide-shopping-bag', label: 'Pembayaran Belanja' },
    refund: { icon: 'i-lucide-undo-2', label: 'Refund' },
    tax: { icon: 'i-lucide-receipt-text', label: 'Potongan Pajak' },
    other: { icon: 'i-lucide-wallet', label: 'Transaksi Wallet' },
}

const statusMeta: Record<DashboardWalletTransactionStatus, { color: 'warning' | 'success' | 'error' | 'neutral' }> = {
    pending: { color: 'warning' },
    completed: { color: 'success' },
    failed: { color: 'error' },
    cancelled: { color: 'neutral' },
}

const sentinel = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

const hasTransactions = computed(() => props.transactions.length > 0)

function amountClass(transaction: DashboardWalletTransaction): string {
    return transaction.direction === 'credit'
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-rose-600 dark:text-rose-400'
}

function signedAmount(transaction: DashboardWalletTransaction): string {
    const sign = transaction.direction === 'credit' ? '+' : '-'

    return `${sign} ${formatIDR(Math.abs(Number(transaction.amount ?? 0)))}`
}

function formatDateTime(value?: string | null): string {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

function observeSentinel(): void {
    if (!observer) {
        return
    }

    observer.disconnect()

    if (sentinel.value) {
        observer.observe(sentinel.value)
    }
}

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0]?.isIntersecting && props.canLoadMore && !props.isLoadingMore) {
                emit('loadMore')
            }
        },
        { rootMargin: '420px' }
    )

    observeSentinel()
})

watch(sentinel, () => observeSentinel())

onBeforeUnmount(() => {
    observer?.disconnect()
    observer = null
})
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h4 class="font-semibold text-gray-900 dark:text-white">Riwayat Transaksi Wallet</h4>
            <UBadge color="neutral" variant="soft" class="rounded-full">
                {{ shownCount }} / {{ totalCount }} transaksi
            </UBadge>
        </div>

        <UCard
            v-if="!hasTransactions"
            class="rounded-2xl border-dashed border-2 border-gray-200 dark:border-gray-800 shadow-none"
        >
            <div class="py-10 text-center text-gray-500">
                <UIcon name="i-lucide-history" class="mx-auto mb-3 size-12 opacity-20" />
                <p>Belum ada mutasi wallet untuk filter yang dipilih.</p>
            </div>
        </UCard>

        <div v-else class="space-y-3">
            <UCard
                v-for="transaction in transactions"
                :key="transaction.id"
                class="rounded-xl transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="rounded-xl bg-gray-100 p-2 text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <UIcon :name="typeMeta[transaction.type]?.icon ?? typeMeta.other.icon" class="size-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ transaction.type_label || typeMeta[transaction.type]?.label || typeMeta.other.label }}
                                </p>
                                <UBadge
                                    size="xs"
                                    variant="soft"
                                    :color="statusMeta[transaction.status]?.color ?? statusMeta.pending.color"
                                >
                                    {{ transaction.status_label }}
                                </UBadge>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ transaction.description }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ formatDateTime(transaction.completed_at ?? transaction.created_at) }}
                            </p>
                        </div>
                    </div>

                    <div class="text-left sm:text-right">
                        <p class="text-sm font-bold" :class="amountClass(transaction)">
                            {{ signedAmount(transaction) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Saldo akhir: {{ formatIDR(transaction.balance_after) }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <UBadge
                        v-if="transaction.transaction_ref"
                        color="neutral"
                        variant="outline"
                        size="xs"
                        class="rounded-full"
                    >
                        Ref: {{ transaction.transaction_ref }}
                    </UBadge>
                    <UBadge
                        v-if="transaction.payment_method"
                        color="neutral"
                        variant="outline"
                        size="xs"
                        class="rounded-full"
                    >
                        {{ transaction.payment_method }}
                    </UBadge>
                </div>
            </UCard>
        </div>

        <div ref="sentinel" class="h-1" />

        <div v-if="isLoadingMore" class="space-y-2">
            <USkeleton class="h-16 rounded-xl" />
            <USkeleton class="h-16 rounded-xl" />
        </div>
    </div>
</template>
