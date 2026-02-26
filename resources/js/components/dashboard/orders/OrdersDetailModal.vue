<script setup lang="ts">
import type { DashboardOrder, DashboardOrderItemPreview } from '@/types/dashboard'

const props = withDefaults(
    defineProps<{
        open: boolean
        selectedOrder: DashboardOrder | null
        detailItems: DashboardOrderItemPreview[]
        statusMeta: Record<DashboardOrder['status'], { label: string; color: any; icon: string }>
        paymentMeta: Record<string, { label: string; color: any; icon: string }>
        checkingPaymentOrderId: string | null
        payingOrderId: string | null
        formatDateTime: (value: string | null | undefined) => string
        formatCurrency: (value: number) => string
        shippingAddressLine: (order: DashboardOrder) => string
        normalizeImageUrl: (url?: string | null) => string | null
        isOrderUnpaid: (order: DashboardOrder) => boolean
        canPayNow: (order: DashboardOrder) => boolean
    }>(),
    {
        selectedOrder: null,
    }
)

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'close'): void
    (e: 'check-payment-status', order: DashboardOrder): void
    (e: 'pay-now', order: DashboardOrder): void
}>()

function closeModal(): void {
    emit('close')
    emit('update:open', false)
}
</script>

<template>
    <UModal
        :open="open"
        :title="selectedOrder ? `Detail Order #${selectedOrder.code}` : 'Detail Order'"
        description="Ringkasan pembayaran, pengiriman, dan item order."
        scrollable
        :content="{ class: 'w-[calc(100vw-1rem)] sm:max-w-4xl lg:max-w-5xl' }"
        :ui="{ body: 'max-h-[72vh] overflow-y-auto px-4 sm:px-6', footer: 'px-4 sm:px-6 pb-4' }"
        @update:open="(value) => emit('update:open', value)"
    >
        <template #body>
            <div v-if="selectedOrder" class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <UBadge :color="statusMeta[selectedOrder.status]?.color" variant="soft" class="rounded-2xl">
                        <UIcon :name="statusMeta[selectedOrder.status]?.icon" class="mr-1 size-3.5" />
                        {{ statusMeta[selectedOrder.status]?.label }}
                    </UBadge>

                    <UBadge v-if="selectedOrder.payment_status" :color="paymentMeta[selectedOrder.payment_status]?.color" variant="subtle" class="rounded-2xl">
                        <UIcon :name="paymentMeta[selectedOrder.payment_status]?.icon" class="mr-1 size-3.5" />
                        {{ paymentMeta[selectedOrder.payment_status]?.label }}
                    </UBadge>

                    <UBadge v-if="selectedOrder.tracking_number" color="neutral" variant="subtle" class="rounded-2xl">
                        <UIcon name="i-lucide-scan-line" class="mr-1 size-3.5" />
                        {{ selectedOrder.tracking_number }}
                    </UBadge>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <UCard class="rounded-2xl" :ui="{ root: 'border border-default bg-elevated/20', body: 'p-3' }">
                        <p class="text-xs uppercase tracking-wider text-muted">Informasi Order</p>
                        <div class="mt-2 space-y-1.5 text-sm">
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Tanggal Order</span>
                                <span class="font-medium text-highlighted">{{ formatDateTime(selectedOrder.created_at) }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Dibayar Pada</span>
                                <span class="font-medium text-highlighted">{{ formatDateTime(selectedOrder.paid_at) }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Metode Bayar</span>
                                <span class="font-medium text-highlighted">{{ selectedOrder.payment_method ?? '-' }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Kurir</span>
                                <span class="font-medium text-highlighted">{{ selectedOrder.shipping_method ?? '-' }}</span>
                            </p>
                        </div>
                    </UCard>

                    <UCard class="rounded-2xl" :ui="{ root: 'border border-default bg-elevated/20', body: 'p-3' }">
                        <p class="text-xs uppercase tracking-wider text-muted">Ringkasan Biaya</p>
                        <div class="mt-2 space-y-1.5 text-sm">
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Subtotal</span>
                                <span class="font-medium text-highlighted">{{ formatCurrency(selectedOrder.subtotal ?? selectedOrder.total) }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Ongkir</span>
                                <span class="font-medium text-highlighted">{{ formatCurrency(selectedOrder.shipping_cost ?? 0) }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Pajak</span>
                                <span class="font-medium text-highlighted">{{ formatCurrency(selectedOrder.tax_amount ?? 0) }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Diskon</span>
                                <span class="font-medium text-highlighted">-{{ formatCurrency(selectedOrder.discount_amount ?? 0) }}</span>
                            </p>
                            <div class="border-t border-default pt-2">
                                <p class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-highlighted">Total</span>
                                    <span class="font-black text-primary tabular-nums">{{ formatCurrency(selectedOrder.total) }}</span>
                                </p>
                            </div>
                        </div>
                    </UCard>
                </div>

                <UCard v-if="selectedOrder.shipping_address" class="rounded-2xl" :ui="{ root: 'border border-default bg-elevated/20', body: 'p-3' }">
                    <p class="text-xs uppercase tracking-wider text-muted">Alamat Pengiriman</p>
                    <p class="mt-2 text-sm font-semibold text-highlighted">
                        {{ selectedOrder.shipping_address.recipient_name ?? '-' }}
                        <span class="font-normal text-muted">· {{ selectedOrder.shipping_address.recipient_phone ?? '-' }}</span>
                    </p>
                    <p class="mt-1 text-sm text-muted">
                        {{ shippingAddressLine(selectedOrder) }}
                    </p>
                </UCard>

                <UCard class="rounded-2xl" :ui="{ root: 'border border-default bg-elevated/20', body: 'p-3' }">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs uppercase tracking-wider text-muted">Item Order</p>
                        <UBadge color="neutral" variant="subtle" class="rounded-xl">
                            {{ detailItems.length }} item
                        </UBadge>
                    </div>

                    <div v-if="detailItems.length" class="mt-3 space-y-2">
                        <div
                            v-for="item in detailItems"
                            :key="item.id"
                            class="flex items-center gap-3 rounded-xl border border-default bg-default/10 p-2.5"
                        >
                            <div class="size-14 shrink-0 overflow-hidden rounded-lg bg-elevated/60">
                                <img
                                    v-if="normalizeImageUrl(item.image)"
                                    :src="normalizeImageUrl(item.image) ?? undefined"
                                    :alt="item.name"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="flex h-full w-full items-center justify-center">
                                    <UIcon name="i-lucide-image" class="size-5 text-muted" />
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-highlighted">{{ item.name }}</p>
                                <p v-if="item.sku" class="truncate text-xs text-muted">SKU: {{ item.sku }}</p>
                                <p class="truncate text-xs text-muted">
                                    <span v-if="item.variant">{{ item.variant }} · </span>
                                    {{ item.qty }} x {{ formatCurrency(item.price) }}
                                </p>
                            </div>

                            <p class="shrink-0 text-sm font-bold tabular-nums text-highlighted">
                                {{ formatCurrency(item.row_total ?? item.price * item.qty) }}
                            </p>
                        </div>
                    </div>

                    <UEmpty
                        v-else
                        icon="i-lucide-shopping-bag"
                        title="Item order belum tersedia"
                        description="Data item tidak ditemukan pada order ini."
                        size="sm"
                        variant="outline"
                        :ui="{ root: 'mt-3 rounded-xl py-8' }"
                    />
                </UCard>

                <UCard v-if="selectedOrder.notes" class="rounded-2xl" :ui="{ root: 'border border-default bg-elevated/20', body: 'p-3' }">
                    <p class="text-xs uppercase tracking-wider text-muted">Catatan Order</p>
                    <p class="mt-2 text-sm text-highlighted">{{ selectedOrder.notes }}</p>
                </UCard>
            </div>
        </template>

        <template #footer>
            <div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <UButton color="neutral" variant="outline" class="rounded-xl" @click="closeModal">
                        Tutup
                    </UButton>

                    <UButton
                        v-if="selectedOrder && isOrderUnpaid(selectedOrder)"
                        color="warning"
                        variant="solid"
                        class="rounded-xl"
                        icon="i-lucide-refresh-cw"
                        :loading="checkingPaymentOrderId === String(selectedOrder.id)"
                        @click="emit('check-payment-status', selectedOrder)"
                    >
                        Cek status pembayaran
                    </UButton>
                </div>

                <UButton
                    v-if="selectedOrder && canPayNow(selectedOrder)"
                    color="success"
                    variant="solid"
                    class="rounded-xl"
                    icon="i-lucide-wallet"
                    :loading="payingOrderId === String(selectedOrder.id)"
                    @click="emit('pay-now', selectedOrder)"
                >
                    Bayar sekarang
                </UButton>
            </div>
        </template>
    </UModal>
</template>
