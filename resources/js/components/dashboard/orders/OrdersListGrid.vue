<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import type { DashboardOrder } from '@/types/dashboard'

const props = withDefaults(
    defineProps<{
        loading?: boolean
        allOrders: DashboardOrder[]
        filtered: DashboardOrder[]
        statusMeta: Record<DashboardOrder['status'], { label: string; color: any; icon: string }>
        paymentMeta: Record<string, { label: string; color: any; icon: string }>
        isLoadingMore: boolean
        hasMore: boolean
        payingOrderId: string | null
        checkingPaymentOrderId: string | null
        formatDateTime: (value: string | null | undefined) => string
        formatCurrency: (value: number) => string
        normalizeImageUrl: (url?: string | null) => string | null
        isOrderUnpaid: (order: DashboardOrder) => boolean
        canPayNow: (order: DashboardOrder) => boolean
        canDownloadInvoice: (order: DashboardOrder) => boolean
        downloadInvoice: (order: DashboardOrder) => void
    }>(),
    {
        loading: false,
    }
)

const emit = defineEmits<{
    (e: 'open-detail', order: DashboardOrder): void
    (e: 'pay-now', order: DashboardOrder): void
    (e: 'check-payment-status', order: DashboardOrder): void
    (e: 'load-more'): void
    (e: 'reset'): void
}>()

const sentinel = ref<HTMLElement | null>(null)
const failedImageKeys = ref<Set<string>>(new Set())
let observer: IntersectionObserver | null = null

function itemImageKey(itemId: string | number, rawUrl?: string | null): string | null {
    const normalizedUrl = props.normalizeImageUrl(rawUrl)

    if (!normalizedUrl) {
        return null
    }

    return `${String(itemId)}::${normalizedUrl}`
}

function itemImageSrc(itemId: string | number, rawUrl?: string | null): string | null {
    const imageKey = itemImageKey(itemId, rawUrl)

    if (!imageKey || failedImageKeys.value.has(imageKey)) {
        return null
    }

    const normalizedUrl = props.normalizeImageUrl(rawUrl)

    return normalizedUrl
}

function markItemImageAsFailed(itemId: string | number, rawUrl?: string | null): void {
    const imageKey = itemImageKey(itemId, rawUrl)

    if (!imageKey || failedImageKeys.value.has(imageKey)) {
        return
    }

    failedImageKeys.value = new Set([...failedImageKeys.value, imageKey])
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
            if (entries[0]?.isIntersecting) {
                emit('load-more')
            }
        },
        { rootMargin: '420px' }
    )

    observeSentinel()
})

watch(sentinel, () => observeSentinel())

watch(
    () => props.filtered.map((order) => `${order.id}:${order.items_preview?.length ?? 0}`).join('|'),
    () => {
        failedImageKeys.value = new Set()
    }
)

onBeforeUnmount(() => {
    observer?.disconnect()
    observer = null
})
</script>

<template>
    <div v-if="props.loading && props.allOrders.length === 0" class="p-4 sm:p-6">
        <div class="grid gap-3">
            <UCard v-for="i in 4" :key="i" class="rounded-2xl" :ui="{ body: 'p-4' }">
                <div class="flex items-start justify-between gap-3">
                    <div class="w-full space-y-2">
                        <USkeleton class="h-4 w-52" />
                        <USkeleton class="h-3 w-72" />
                        <USkeleton class="h-3 w-56" />
                    </div>
                    <USkeleton class="h-7 w-24 rounded-xl" />
                </div>
                <div class="mt-4 grid gap-2 sm:grid-cols-3">
                    <USkeleton class="h-12 w-full rounded-2xl" />
                    <USkeleton class="h-12 w-full rounded-2xl" />
                    <USkeleton class="h-12 w-full rounded-2xl" />
                </div>
            </UCard>
        </div>
    </div>

    <UEmpty
        v-else-if="props.filtered.length === 0"
        icon="i-lucide-package-search"
        title="Tidak ada pesanan ditemukan"
        description="Coba ubah kata kunci atau filter."
        variant="outline"
        size="lg"
        :ui="{ root: 'rounded-2xl py-14' }"
    >
        <template #actions>
            <UButton size="sm" color="neutral" variant="outline" icon="i-lucide-rotate-ccw" @click="emit('reset')">
                Reset filter
            </UButton>
        </template>
    </UEmpty>

    <div v-else>
        <div class="grid gap-3">
            <UCard
                v-for="order in props.filtered"
                :key="order.code"
                class="rounded-xl overflow-hidden"
                :ui="{ root: 'group relative hover:bg-elevated/30 transition-colors', body: 'p-3 sm:p-3' }"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-extrabold text-highlighted">#{{ order.code }}</p>

                            <UBadge :color="props.statusMeta[order.status]?.color" variant="soft" size="sm" class="rounded-2xl">
                                <UIcon :name="props.statusMeta[order.status]?.icon" class="mr-1 size-3.5" />
                                {{ props.statusMeta[order.status]?.label }}
                            </UBadge>

                            <UBadge
                                v-if="order.payment_status"
                                :color="props.paymentMeta[order.payment_status]?.color"
                                variant="subtle"
                                size="sm"
                                class="rounded-2xl"
                            >
                                <UIcon :name="props.paymentMeta[order.payment_status]?.icon" class="mr-1 size-3.5" />
                                {{ props.paymentMeta[order.payment_status]?.label }}
                            </UBadge>

                            <UBadge v-if="order.tracking_number" color="neutral" variant="subtle" size="sm" class="rounded-2xl">
                                <UIcon name="i-lucide-scan-line" class="mr-1 size-3.5" />
                                {{ order.tracking_number }}
                            </UBadge>
                        </div>

                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted">
                            <span class="inline-flex items-center gap-1.5">
                                <UIcon name="i-lucide-calendar" class="size-3.5" />
                                {{ props.formatDateTime(order.created_at) }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <UIcon name="i-lucide-shopping-bag" class="size-3.5" />
                                {{ order.items_count }} item
                            </span>
                            <span v-if="order.payment_method" class="inline-flex items-center gap-1.5">
                                <UIcon name="i-lucide-credit-card" class="size-3.5" />
                                {{ order.payment_method }}
                            </span>
                            <span v-if="order.shipping_method" class="inline-flex items-center gap-1.5">
                                <UIcon name="i-lucide-truck" class="size-3.5" />
                                {{ order.shipping_method }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:flex-col sm:items-end gap-2">
                        <p class="text-base font-black text-primary tabular-nums">
                            {{ props.formatCurrency(order.total) }}
                        </p>

                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <UButton size="xs" color="primary" variant="soft" class="rounded-2xl" icon="i-lucide-eye" @click="emit('open-detail', order)">
                                Detail
                            </UButton>

                            <UButton
                                v-if="props.canDownloadInvoice(order)"
                                size="xs"
                                color="neutral"
                                variant="outline"
                                class="rounded-2xl"
                                icon="i-lucide-file-down"
                                @click="props.downloadInvoice(order)"
                            >
                                Invoice
                            </UButton>

                            <UButton
                                v-if="props.canPayNow(order)"
                                size="xs"
                                color="success"
                                variant="solid"
                                class="rounded-2xl"
                                icon="i-lucide-wallet"
                                :loading="props.payingOrderId === String(order.id)"
                                @click="emit('pay-now', order)"
                            >
                                Bayar Sekarang
                            </UButton>

                            <UButton
                                v-if="props.isOrderUnpaid(order)"
                                size="xs"
                                color="warning"
                                variant="outline"
                                class="rounded-2xl"
                                icon="i-lucide-refresh-cw"
                                :loading="props.checkingPaymentOrderId === String(order.id)"
                                @click="emit('check-payment-status', order)"
                            >
                                Cek Status Bayar
                            </UButton>

                            <UButton
                                v-if="order.tracking_number"
                                size="xs"
                                color="neutral"
                                variant="outline"
                                class="rounded-2xl"
                                icon="i-lucide-truck"
                                @click="emit('open-detail', order)"
                            >
                                Lacak
                            </UButton>
                        </div>
                    </div>
                </div>

                <div v-if="order.items_preview?.length" class="mt-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Ringkasan item</p>
                        <p class="text-xs text-muted">Maks 3 ditampilkan</p>
                    </div>

                    <div class="mt-2 grid gap-2 sm:grid-cols-3">
                        <UCard v-for="item in order.items_preview.slice(0, 3)" :key="item.id" class="rounded-2xl" :ui="{ body: 'p-3' }">
                            <div class="flex items-center gap-3">
                                <div class="size-10 shrink-0 overflow-hidden rounded-xl bg-elevated/40">
                                    <img
                                        v-if="itemImageSrc(item.id, item.image)"
                                        :src="itemImageSrc(item.id, item.image) ?? undefined"
                                        :alt="item.name"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                        @error="markItemImageAsFailed(item.id, item.image)"
                                    />
                                    <div v-else class="flex h-full w-full items-center justify-center">
                                        <UIcon name="i-lucide-image" class="size-5 text-muted" />
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-highlighted">{{ item.name }}</p>
                                    <p class="mt-0.5 truncate text-xs text-muted">
                                        <span v-if="item.variant">{{ item.variant }} · </span>x{{ item.qty }}
                                        <span class="mx-1">·</span>
                                        <span class="font-semibold tabular-nums text-highlighted/80">{{ props.formatCurrency(item.price) }}</span>
                                    </p>
                                </div>
                            </div>
                        </UCard>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between text-xs text-muted">
                    <span class="inline-flex items-center gap-1.5">
                        <UIcon name="i-lucide-user" class="size-3.5" />
                        {{ order.customer?.name ?? 'Guest' }}
                    </span>

                    <span class="hidden sm:inline-flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <UIcon name="i-lucide-mouse-pointer-click" class="size-3.5" />
                        Klik detail untuk info lengkap
                    </span>
                </div>
            </UCard>
        </div>

        <div ref="sentinel" class="pt-8 text-center">
            <div v-if="props.isLoadingMore" class="inline-flex items-center gap-2.5 text-sm text-muted">
                <UIcon name="i-lucide-loader-2" class="size-4 animate-spin text-primary" />
                Memuat pesanan...
            </div>

            <div v-else-if="props.hasMore" class="space-y-2">
                <p class="text-xs text-muted">Scroll untuk memuat lebih banyak pesanan.</p>
                <UButton size="xs" color="neutral" variant="outline" @click="emit('load-more')">
                    Muat lebih banyak
                </UButton>
            </div>

            <UBadge v-else color="neutral" variant="subtle" class="rounded-2xl">
                <UIcon name="i-lucide-check-circle-2" class="mr-1 size-3.5 text-emerald-500" />
                Semua pesanan sudah dimuat
            </UBadge>
        </div>
    </div>
</template>
