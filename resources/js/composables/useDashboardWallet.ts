import { computed, ref, watch, type ComputedRef } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import type {
    Customer,
    DashboardMidtransConfig,
    DashboardWalletTransaction,
    DashboardWalletTransactionStatus,
    DashboardWalletTransactionType,
    DashboardWalletTransactionsPagination,
} from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

declare global {
    interface Window {
        snap?: { pay: (token: string, options?: Record<string, unknown>) => void }
    }
}

export type WalletFilterOption = {
    label: string
    value: string
}

type UseDashboardWalletOptions = {
    customer: ComputedRef<Customer | null | undefined>
    transactions: ComputedRef<DashboardWalletTransactionsPagination | undefined>
    walletBalance: ComputedRef<number | null | undefined>
    midtrans: ComputedRef<DashboardMidtransConfig | undefined>
}

type WalletFlashPayload = {
    action?: string | null
    message?: string | null
    payload?: Record<string, unknown> | null
}

type InertiaSharedProps = {
    csrf_token?: string
    flash?: {
        wallet?: WalletFlashPayload | null
    }
}

const defaultTransactionsPayload: DashboardWalletTransactionsPagination = {
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
}

function firstErrorMessage(errors: Record<string, string | string[] | undefined>): string {
    const first = Object.values(errors).find((value) => value !== undefined)

    if (Array.isArray(first)) {
        return first[0] ?? 'Request gagal.'
    }

    return first ?? 'Request gagal.'
}

export function useDashboardWallet(options: UseDashboardWalletOptions) {
    const toast = useToast()
    const page = usePage<InertiaSharedProps>()
    const { formatIDR } = useDashboard()

    const balance = computed(() => Number(options.walletBalance.value ?? options.customer.value?.wallet_balance ?? 0))
    const formattedBalance = computed(() => formatIDR(balance.value))

    const typeItems = computed<WalletFilterOption[]>(() => [
        { label: 'Semua tipe', value: 'all' },
        { label: 'Topup', value: 'topup' },
        { label: 'Withdrawal', value: 'withdrawal' },
        { label: 'Bonus', value: 'bonus' },
        { label: 'Purchase', value: 'purchase' },
        { label: 'Refund', value: 'refund' },
        { label: 'Tax', value: 'tax' },
    ])

    const statusItems = computed<WalletFilterOption[]>(() => [
        { label: 'Semua status', value: 'all' },
        { label: 'Pending', value: 'pending' },
        { label: 'Completed', value: 'completed' },
        { label: 'Failed', value: 'failed' },
        { label: 'Cancelled', value: 'cancelled' },
    ])

    const allTransactions = ref<DashboardWalletTransaction[]>([])
    const currentPage = ref(1)
    const nextPage = ref<number | null>(null)
    const hasMore = ref(false)
    const isLoadingMore = ref(false)
    const isApplyingFilter = ref(false)

    const searchQuery = ref('')
    const typeFilter = ref<DashboardWalletTransactionType | 'all'>('all')
    const statusFilter = ref<DashboardWalletTransactionStatus | 'all'>('all')
    const hasInitializedFilter = ref(false)

    const isTopupModalOpen = ref(false)
    const topupAmount = ref<number | null>(null)
    const topupNotes = ref('')
    const isSubmittingTopup = ref(false)
    const syncingTopupId = ref<number | null>(null)

    const isWithdrawalModalOpen = ref(false)
    const withdrawalAmount = ref<number | null>(null)
    const withdrawalPassword = ref('')
    const withdrawalNotes = ref('')
    const isSubmittingWithdrawal = ref(false)

    const shownCount = computed(() => allTransactions.value.length)
    const totalCount = computed(() => options.transactions.value?.total ?? allTransactions.value.length)

    watch(
        options.transactions,
        (incoming) => {
            const payload = incoming ?? defaultTransactionsPayload
            const incomingPage = payload.current_page ?? 1
            const incomingData = payload.data ?? []

            if (incomingPage <= 1) {
                allTransactions.value = [...incomingData]
            } else if (incomingPage > currentPage.value) {
                const existingKeys = new Set(allTransactions.value.map((transaction) => String(transaction.id)))
                const appended = incomingData.filter((transaction) => !existingKeys.has(String(transaction.id)))
                allTransactions.value = [...allTransactions.value, ...appended]
            }

            currentPage.value = incomingPage
            nextPage.value = payload.next_page ?? null
            hasMore.value = Boolean(payload.has_more)

            if (!hasInitializedFilter.value) {
                const incomingFilters = payload.filters ?? {}
                searchQuery.value = incomingFilters.search ?? ''
                typeFilter.value = (incomingFilters.type as DashboardWalletTransactionType | null) ?? 'all'
                statusFilter.value = (incomingFilters.status as DashboardWalletTransactionStatus | null) ?? 'all'
                hasInitializedFilter.value = true
            }
        },
        { immediate: true }
    )

    function buildWalletQuery(pageNumber: number): Record<string, string | number> {
        const search = searchQuery.value.trim()
        const query: Record<string, string | number> = {
            section: 'wallet',
            wallet_page: pageNumber,
        }

        if (search !== '') {
            query.wallet_search = search
        }

        if (typeFilter.value !== 'all') {
            query.wallet_type = typeFilter.value
        }

        if (statusFilter.value !== 'all') {
            query.wallet_status = statusFilter.value
        }

        return query
    }

    function requestWallet(pageNumber = 1): void {
        if (isApplyingFilter.value) {
            return
        }

        isApplyingFilter.value = true

        if (pageNumber > 1) {
            isLoadingMore.value = true
        }

        router.get('/dashboard', buildWalletQuery(pageNumber), {
            only: ['walletTransactions', 'stats', 'customer', 'hasPendingWithdrawal', 'midtrans'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                isApplyingFilter.value = false
                isLoadingMore.value = false
            },
        })
    }

    function applyFilter(): void {
        requestWallet(1)
    }

    function resetFilter(): void {
        searchQuery.value = ''
        typeFilter.value = 'all'
        statusFilter.value = 'all'
        requestWallet(1)
    }

    function loadMore(): void {
        if (isLoadingMore.value || !hasMore.value || !nextPage.value) {
            return
        }

        requestWallet(nextPage.value)
    }

    function getSnapScriptUrl(): string {
        const env = options.midtrans.value?.env ?? 'sandbox'
        const host = env === 'production' ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com'

        return `${host}/snap/snap.js`
    }

    async function ensureSnapLoaded(): Promise<boolean> {
        if (window.snap?.pay) {
            return true
        }

        const clientKey = options.midtrans.value?.client_key ?? ''

        if (!clientKey) {
            return false
        }

        return new Promise((resolve) => {
            const existingScript = document.querySelector<HTMLScriptElement>('script[data-midtrans-snap="1"]')

            if (existingScript) {
                existingScript.addEventListener('load', () => resolve(!!window.snap?.pay))
                existingScript.addEventListener('error', () => resolve(false))

                return
            }

            const script = document.createElement('script')
            script.src = getSnapScriptUrl()
            script.async = true
            script.setAttribute('data-midtrans-snap', '1')
            script.setAttribute('data-client-key', clientKey)
            script.onload = () => resolve(!!window.snap?.pay)
            script.onerror = () => resolve(false)
            document.head.appendChild(script)
        })
    }

    async function inertiaPost(
        url: string,
        payload: Record<string, unknown> = {},
        only: string[] = ['flash', 'errors']
    ): Promise<InertiaSharedProps> {
        const csrfToken = String(page.props.csrf_token ?? '')

        return new Promise((resolve, reject) => {
            router.post(
                url,
                {
                    _token: csrfToken,
                    ...payload,
                },
                {
                    only,
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    onSuccess: (nextPage) => {
                        const props = (nextPage?.props ?? {}) as InertiaSharedProps
                        resolve(props)
                    },
                    onError: (errors) => {
                        reject(new Error(firstErrorMessage(errors as Record<string, string | string[] | undefined>)))
                    },
                    onCancel: () => {
                        reject(new Error('Request dibatalkan.'))
                    },
                }
            )
        })
    }

    async function syncTopupStatus(walletTransactionId: number): Promise<void> {
        syncingTopupId.value = walletTransactionId

        try {
            const response = await inertiaPost(
                `/dashboard/wallet/topup/${walletTransactionId}/payment-status`,
                {},
                ['flash', 'errors', 'walletTransactions', 'stats', 'customer', 'hasPendingWithdrawal', 'midtrans']
            )

            const message = response.flash?.wallet?.message ?? 'Status pembayaran topup berhasil disinkronkan.'

            toast?.add?.({
                title: 'Status topup diperbarui',
                description: message,
                color: 'success',
                icon: 'i-lucide-badge-check',
            })

            requestWallet(1)
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Gagal sinkronisasi status topup.'

            toast?.add?.({
                title: 'Sinkronisasi topup gagal',
                description: message,
                color: 'error',
                icon: 'i-lucide-x-circle',
            })
        } finally {
            syncingTopupId.value = null
        }
    }

    async function submitTopup(): Promise<void> {
        if (isSubmittingTopup.value) {
            return
        }

        if (!topupAmount.value || topupAmount.value < 10000) {
            toast?.add?.({
                title: 'Nominal topup tidak valid',
                description: 'Nominal topup minimal Rp 10.000.',
                color: 'warning',
                icon: 'i-lucide-alert-circle',
            })

            return
        }

        if (!(options.midtrans.value?.client_key ?? '')) {
            toast?.add?.({
                title: 'Midtrans belum aktif',
                description: 'Client key Midtrans belum tersedia.',
                color: 'error',
                icon: 'i-lucide-x-circle',
            })

            return
        }

        isSubmittingTopup.value = true

        try {
            const response = await inertiaPost('/dashboard/wallet/topup/token', {
                amount: topupAmount.value,
                notes: topupNotes.value.trim() || null,
            })

            const walletFlash = response.flash?.wallet
            const rawPayload = (walletFlash?.payload ?? {}) as {
                snapToken?: string
                walletTransactionId?: number | string
            }
            const snapToken = rawPayload.snapToken
            const walletTransactionId = Number(rawPayload.walletTransactionId ?? 0)

            if (!snapToken || !walletTransactionId) {
                throw new Error(walletFlash?.message ?? 'Token topup Midtrans tidak tersedia.')
            }

            const snapLoaded = await ensureSnapLoaded()

            if (!snapLoaded) {
                throw new Error('Midtrans Snap gagal dimuat. Periksa konfigurasi client key.')
            }

            isSubmittingTopup.value = false
            isTopupModalOpen.value = false

            window.snap?.pay(snapToken, {
                onSuccess: () => {
                    void syncTopupStatus(walletTransactionId)
                },
                onPending: () => {
                    void syncTopupStatus(walletTransactionId)
                },
                onError: () => {
                    toast?.add?.({
                        title: 'Pembayaran topup gagal',
                        description: 'Terjadi kesalahan saat proses Midtrans.',
                        color: 'error',
                        icon: 'i-lucide-x-circle',
                    })
                },
                onClose: () => {
                    toast?.add?.({
                        title: 'Pembayaran ditutup',
                        description: 'Popup pembayaran ditutup sebelum selesai.',
                        color: 'warning',
                        icon: 'i-lucide-alert-circle',
                    })
                },
            })
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Gagal membuat topup Midtrans.'

            toast?.add?.({
                title: 'Topup gagal',
                description: message,
                color: 'error',
                icon: 'i-lucide-x-circle',
            })
        } finally {
            if (isSubmittingTopup.value) {
                isSubmittingTopup.value = false
            }
        }
    }

    async function submitWithdrawal(): Promise<void> {
        if (isSubmittingWithdrawal.value) {
            return
        }

        if (!withdrawalAmount.value || withdrawalAmount.value < 10000) {
            toast?.add?.({
                title: 'Nominal withdrawal tidak valid',
                description: 'Nominal withdrawal minimal Rp 10.000.',
                color: 'warning',
                icon: 'i-lucide-alert-circle',
            })

            return
        }

        if (!withdrawalPassword.value) {
            toast?.add?.({
                title: 'Password wajib diisi',
                description: 'Masukkan password untuk konfirmasi withdrawal.',
                color: 'warning',
                icon: 'i-lucide-alert-circle',
            })

            return
        }

        isSubmittingWithdrawal.value = true

        try {
            const response = await inertiaPost(
                '/dashboard/wallet/withdrawal',
                {
                    amount: withdrawalAmount.value,
                    password: withdrawalPassword.value,
                    notes: withdrawalNotes.value.trim() || null,
                },
                ['flash', 'errors', 'walletTransactions', 'stats', 'customer', 'hasPendingWithdrawal', 'midtrans']
            )

            const message = response.flash?.wallet?.message ?? 'Permintaan withdrawal berhasil dikirim.'

            toast?.add?.({
                title: 'Withdrawal terkirim',
                description: message,
                color: 'success',
                icon: 'i-lucide-badge-check',
            })

            isWithdrawalModalOpen.value = false
            withdrawalPassword.value = ''
            withdrawalNotes.value = ''
            withdrawalAmount.value = null

            requestWallet(1)
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Gagal mengirim permintaan withdrawal.'

            toast?.add?.({
                title: 'Withdrawal gagal',
                description: message,
                color: 'error',
                icon: 'i-lucide-x-circle',
            })
        } finally {
            isSubmittingWithdrawal.value = false
        }
    }

    function resetTopupForm(): void {
        topupAmount.value = null
        topupNotes.value = ''
    }

    function resetWithdrawalForm(): void {
        withdrawalAmount.value = null
        withdrawalPassword.value = ''
        withdrawalNotes.value = ''
    }

    watch(isTopupModalOpen, (open) => {
        if (!open) {
            resetTopupForm()
        }
    })

    watch(isWithdrawalModalOpen, (open) => {
        if (!open) {
            resetWithdrawalForm()
        }
    })

    return {
        balance,
        formattedBalance,
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
    }
}
