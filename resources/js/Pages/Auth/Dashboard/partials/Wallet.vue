<script setup lang="ts">
import { computed } from 'vue'
import type {
    Customer,
    DashboardMidtransConfig,
    DashboardWalletTransactionsPagination,
} from '@/types/dashboard'
import WalletSummaryCard from '@/components/dashboard/wallet/WalletSummaryCard.vue'
import WalletFilterCard from '@/components/dashboard/wallet/WalletFilterCard.vue'
import WalletTransactionList from '@/components/dashboard/wallet/WalletTransactionList.vue'
import WalletTopupModal from '@/components/dashboard/wallet/WalletTopupModal.vue'
import WalletWithdrawalModal from '@/components/dashboard/wallet/WalletWithdrawalModal.vue'
import { useDashboardWallet } from '@/composables/useDashboardWallet'

const props = withDefaults(
    defineProps<{
        customer?: Customer | null
        transactions?: DashboardWalletTransactionsPagination
        hasPendingWithdrawal?: boolean
        walletBalance?: number
        midtrans?: DashboardMidtransConfig
        waConfirmationUrl?: string | null
        waGatewayHematMode?: boolean
    }>(),
    {
        customer: null,
        transactions: () => ({
            data: [],
            current_page: 1,
            next_page: null,
            has_more: false,
            per_page: 15,
            total: 0,
            filters: {
                search: null,
                type: null,
                status: null,
            },
        }),
        hasPendingWithdrawal: false,
        walletBalance: 0,
        midtrans: () => ({
            env: 'sandbox',
            client_key: '',
        }),
        waConfirmationUrl: null,
        waGatewayHematMode: false,
    }
)

const hasPendingWithdrawal = computed(() => Boolean(props.hasPendingWithdrawal))
const isWaConfirmed = computed(() => props.customer?.is_wa_confirmed === true)

const {
    balance,
    formattedBalance,
    withdrawalAdminFee,
    allTransactions,
    shownCount,
    totalCount,
    hasMore,
    nextPage,
    isLoadingMore,
    isApplyingFilter,
    searchQuery,
    typeFilter,
    statusFilter,
    typeItems,
    statusItems,
    isTopupModalOpen,
    topupAmount,
    topupNotes,
    isSubmittingTopup,
    syncingTopupId,
    isWithdrawalModalOpen,
    withdrawalAmount,
    withdrawalPassword,
    withdrawalNotes,
    isSubmittingWithdrawal,
    applyFilter,
    resetFilter,
    loadMore,
    submitTopup,
    submitWithdrawal,
} = useDashboardWallet({
    customer: computed(() => props.customer),
    transactions: computed(() => props.transactions),
    walletBalance: computed(() => props.walletBalance),
    midtrans: computed(() => props.midtrans),
})
</script>

<template>
    <div class="space-y-6">
        <WalletSummaryCard
            :formatted-balance="formattedBalance"
            :has-pending-withdrawal="hasPendingWithdrawal"
            @topup="isTopupModalOpen = true"
            @withdrawal="isWithdrawalModalOpen = true"
        />

        <WalletFilterCard
            v-model:search="searchQuery"
            v-model:type="typeFilter"
            v-model:status="statusFilter"
            :type-items="typeItems"
            :status-items="statusItems"
            :is-applying="isApplyingFilter"
            @apply="applyFilter"
            @reset="resetFilter"
        />

        <WalletTransactionList
            :transactions="allTransactions"
            :shown-count="shownCount"
            :total-count="totalCount"
            :is-loading-more="isLoadingMore"
            :can-load-more="hasMore && !!nextPage && !isLoadingMore"
            @load-more="loadMore"
        />

        <WalletTopupModal
            v-model:open="isTopupModalOpen"
            v-model:amount="topupAmount"
            v-model:notes="topupNotes"
            :loading="isSubmittingTopup"
            :syncing="syncingTopupId !== null"
            :is-wa-confirmed="isWaConfirmed"
            :wa-confirmation-url="props.waConfirmationUrl"
            :hemat-mode="props.waGatewayHematMode"
            @submit="submitTopup"
        />

        <WalletWithdrawalModal
            v-model:open="isWithdrawalModalOpen"
            v-model:amount="withdrawalAmount"
            v-model:password="withdrawalPassword"
            v-model:notes="withdrawalNotes"
            :max-amount="balance"
            :max-amount-label="formattedBalance"
            :admin-fee="withdrawalAdminFee"
            :loading="isSubmittingWithdrawal"
            :is-wa-confirmed="isWaConfirmed"
            :wa-confirmation-url="props.waConfirmationUrl"
            :hemat-mode="props.waGatewayHematMode"
            @submit="submitWithdrawal"
        />
    </div>
</template>
