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
    isDetailOpen,
    selectedOrder,
    q,
    status,
    sort,
    statusMeta,
    paymentMeta,
    statusItems,
    sortItems,
    filtered,
    totalCount,
    shownCount,
    detailItems,
    formatCurrency,
    formatDateTime,
    reset,
    loadMore,
    openDetail,
    closeDetail,
    isOrderUnpaid,
    canPayNow,
    canDownloadInvoice,
    downloadInvoice,
    checkPaymentStatus,
    payNow,
    normalizeImageUrl,
    shippingAddressLine,
} = useDashboardOrders({
    orders: computed(() => props.orders),
    midtrans: computed(() => props.midtrans),
})

function onSearchChange(value: string): void {
    q.value = value
}

function onStatusChange(value: 'all' | 'pending' | 'paid' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded'): void {
    status.value = value
}

function onSortChange(value: 'newest' | 'oldest' | 'highest' | 'lowest'): void {
    sort.value = value
}
</script>

<template>
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
            @open-detail="openDetail"
            @pay-now="payNow"
            @check-payment-status="checkPaymentStatus"
            @load-more="loadMore"
            @reset="reset"
        />
    </UCard>

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
        @close="closeDetail"
        @check-payment-status="checkPaymentStatus"
        @pay-now="payNow"
    />
</template>
