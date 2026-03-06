<script setup lang="ts">
import { computed } from 'vue'
import type { DashboardMidtransConfig, DashboardOrdersPagination } from '@/types/dashboard'
import { useDashboardOrders } from '@/composables/useDashboardOrders'
import OrdersFiltersBar from '@/components/dashboard/orders/OrdersFiltersBar.vue'
import OrdersListGrid from '@/components/dashboard/orders/OrdersListGrid.vue'
import OrdersDetailModal from '@/components/dashboard/orders/OrdersDetailModal.vue'

const props = withDefaults(
    defineProps<{
        orders?: DashboardOrdersPagination
        midtrans?: DashboardMidtransConfig
        loading?: boolean
    }>(),
    {
        orders: () => ({
            data: [],
            current_page: 1,
            next_page: null,
            has_more: false,
            per_page: 10,
            total: 0,
        }),
        midtrans: () => ({
            env: 'sandbox',
            client_key: '',
        }),
        loading: false,
    }
)

const {
    allOrders,
    hasMore,
    isLoadingMore,
    checkingPaymentOrderId,
    payingOrderId,
    reviewingOrderItemId,
    isDetailOpen,
    selectedOrder,
    isReviewModalOpen,
    reviewTargetOrder,
    reviewTargetItem,
    reviewRating,
    reviewTitle,
    reviewComment,
    q,
    status,
    sort,
    statusMeta,
    paymentMeta,
    statusItems,
    sortItems,
    reviewRatingItems,
    filtered,
    totalCount,
    shownCount,
    pendingReviewCount,
    hasPendingReview,
    detailItems,
    formatCurrency,
    formatDateTime,
    reset,
    loadMore,
    openDetail,
    closeDetail,
    canReviewItem,
    openReviewModal,
    closeReviewModal,
    isOrderUnpaid,
    canPayNow,
    canDownloadInvoice,
    downloadInvoice,
    checkPaymentStatus,
    payNow,
    submitReview,
    normalizeImageUrl,
    shippingAddressLine,
} = useDashboardOrders({
    orders: computed(() => props.orders),
    midtrans: computed(() => props.midtrans),
})

function onSearchChange(value: string): void {
    q.value = value
}

function onStatusChange(value: 'all' | 'unpaid' | 'pending' | 'paid' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded'): void {
    status.value = value
}

function onSortChange(value: 'newest' | 'oldest' | 'highest' | 'lowest'): void {
    sort.value = value
}
</script>

<template>
    <div class="space-y-4">
        <UAlert
            v-if="hasPendingReview"
            color="warning"
            variant="soft"
            icon="i-lucide-star"
            title="Masih ada produk yang belum direview"
            :description="`Kamu masih punya ${pendingReviewCount} produk dari pesanan yang sudah diterima dan belum diulas.`"
        />

        <UCard class="rounded-3xl overflow-hidden">
            <template #header>
                <OrdersFiltersBar
                    :shown-count="shownCount"
                    :total-count="totalCount"
                    :q="q"
                    :status="status"
                    :sort="sort"
                    :status-items="statusItems"
                    :sort-items="sortItems"
                    @update:q="onSearchChange"
                    @update:status="onStatusChange"
                    @update:sort="onSortChange"
                    @reset="reset"
                />
            </template>

            <OrdersListGrid
                :loading="loading"
                :all-orders="allOrders"
                :filtered="filtered"
                :status-meta="statusMeta"
                :payment-meta="paymentMeta"
                :is-loading-more="isLoadingMore"
                :has-more="hasMore"
                :paying-order-id="payingOrderId"
                :checking-payment-order-id="checkingPaymentOrderId"
                :format-date-time="formatDateTime"
                :format-currency="formatCurrency"
                :normalize-image-url="normalizeImageUrl"
                :is-order-unpaid="isOrderUnpaid"
                :can-pay-now="canPayNow"
                :can-download-invoice="canDownloadInvoice"
                :download-invoice="downloadInvoice"
                :can-review-item="canReviewItem"
                :open-review-modal="openReviewModal"
                @open-detail="openDetail"
                @pay-now="payNow"
                @check-payment-status="checkPaymentStatus"
                @load-more="loadMore"
                @reset="reset"
            />
        </UCard>
    </div>

    <OrdersDetailModal
        v-model:open="isDetailOpen"
        :selected-order="selectedOrder"
        :detail-items="detailItems"
        :status-meta="statusMeta"
        :payment-meta="paymentMeta"
        :checking-payment-order-id="checkingPaymentOrderId"
        :paying-order-id="payingOrderId"
        :format-date-time="formatDateTime"
        :format-currency="formatCurrency"
        :shipping-address-line="shippingAddressLine"
        :normalize-image-url="normalizeImageUrl"
        :is-order-unpaid="isOrderUnpaid"
        :can-pay-now="canPayNow"
        :can-download-invoice="canDownloadInvoice"
        :download-invoice="downloadInvoice"
        :can-review-item="canReviewItem"
        :open-review-modal="openReviewModal"
        @close="closeDetail"
        @check-payment-status="checkPaymentStatus"
        @pay-now="payNow"
    />

    <UModal
        :open="isReviewModalOpen"
        :title="reviewTargetItem ? `Ulas Produk: ${reviewTargetItem.name}` : 'Ulas Produk'"
        description="Ulasan akan tampil setelah disetujui admin."
        :ui="{ body: 'space-y-4 p-4 sm:p-5', footer: 'px-4 sm:px-5 pb-4' }"
        @update:open="(value) => { if (!value) closeReviewModal() }"
    >
        <template #body>
            <div v-if="reviewTargetOrder && reviewTargetItem" class="space-y-4">
                <UAlert
                    color="primary"
                    variant="soft"
                    icon="i-lucide-package-check"
                    :title="`Order #${reviewTargetOrder.code}`"
                    :description="`Qty ${reviewTargetItem.qty} • ${formatCurrency(reviewTargetItem.price)}`"
                />

                <UFormField label="Rating">
                    <USelectMenu
                        v-model="reviewRating"
                        :items="reviewRatingItems"
                        value-key="value"
                        label-key="label"
                        class="w-full"
                    />
                </UFormField>

                <UFormField label="Judul review (opsional)">
                    <UInput
                        v-model="reviewTitle"
                        placeholder="Contoh: Produk sesuai deskripsi"
                        class="w-full"
                    />
                </UFormField>

                <UFormField label="Komentar">
                    <UTextarea
                        v-model="reviewComment"
                        :rows="4"
                        placeholder="Tuliskan pengalamanmu menggunakan produk ini..."
                        class="w-full"
                    />
                </UFormField>
            </div>
        </template>

        <template #footer>
            <div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <UButton
                    color="neutral"
                    variant="outline"
                    class="rounded-xl"
                    @click="closeReviewModal"
                >
                    Batal
                </UButton>

                <UButton
                    color="primary"
                    variant="solid"
                    class="rounded-xl"
                    :loading="reviewingOrderItemId === String(reviewTargetItem?.id ?? '')"
                    @click="submitReview"
                >
                    Kirim review
                </UButton>
            </div>
        </template>
    </UModal>
</template>
