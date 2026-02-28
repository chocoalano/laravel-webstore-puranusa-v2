import { computed, nextTick, ref, watch, type ComputedRef } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import type {
    DashboardMidtransConfig,
    DashboardOrder,
    DashboardOrderItemPreview,
    DashboardOrdersPagination,
} from '@/types/dashboard'

declare global {
    interface Window {
        snap?: { pay: (token: string, options?: Record<string, unknown>) => void }
    }
}

type OrdersFlashPayload = {
    action?: string | null
    message?: string | null
    payload?: Record<string, unknown> | null
}

type InertiaSharedProps = {
    csrf_token?: string
    flash?: {
        orders?: OrdersFlashPayload | null
    }
}

type UseDashboardOrdersOptions = {
    orders: ComputedRef<DashboardOrdersPagination>
    midtrans: ComputedRef<DashboardMidtransConfig>
}

type SortValue = 'newest' | 'oldest' | 'highest' | 'lowest'

function firstErrorMessage(errors: Record<string, string | string[] | undefined>): string {
    const first = Object.values(errors).find((value) => value !== undefined)

    if (Array.isArray(first)) {
        return first[0] ?? 'Request gagal.'
    }

    return first ?? 'Request gagal.'
}

export function useDashboardOrders(options: UseDashboardOrdersOptions) {
    const toast = useToast()
    const page = usePage<InertiaSharedProps>()

    const allOrders = ref<DashboardOrder[]>([])
    const currentPage = ref(1)
    const nextPage = ref<number | null>(null)
    const hasMore = ref(false)
    const isLoadingMore = ref(false)
    const checkingPaymentOrderId = ref<string | null>(null)
    const payingOrderId = ref<string | null>(null)

    const isDetailOpen = ref(false)
    const selectedOrder = ref<DashboardOrder | null>(null)

    watch(
        options.orders,
        (incoming) => {
            const incomingPage = incoming.current_page ?? 1
            const incomingData = incoming.data ?? []

            if (incomingPage <= 1) {
                allOrders.value = [...incomingData]
            } else if (incomingPage > currentPage.value) {
                const existingKeys = new Set(allOrders.value.map((order) => String(order.code)))
                const appended = incomingData.filter((order) => !existingKeys.has(String(order.code)))
                allOrders.value = [...allOrders.value, ...appended]
            }

            currentPage.value = incomingPage
            nextPage.value = incoming.next_page ?? null
            hasMore.value = Boolean(incoming.has_more)
        },
        { immediate: true }
    )

    const q = ref('')
    const status = ref<DashboardOrder['status'] | 'all'>('all')
    const sort = ref<SortValue>('newest')

    const statusMeta: Record<DashboardOrder['status'], { label: string; color: any; icon: string }> = {
        pending: { label: 'Menunggu', color: 'warning', icon: 'i-lucide-clock' },
        paid: { label: 'Dibayar', color: 'primary', icon: 'i-lucide-badge-check' },
        processing: { label: 'Diproses', color: 'info', icon: 'i-lucide-cog' },
        shipped: { label: 'Dikirim', color: 'info', icon: 'i-lucide-truck' },
        delivered: { label: 'Selesai', color: 'success', icon: 'i-lucide-check-circle-2' },
        cancelled: { label: 'Dibatalkan', color: 'neutral', icon: 'i-lucide-x-circle' },
        refunded: { label: 'Refund', color: 'neutral', icon: 'i-lucide-undo-2' },
    }

    const paymentMeta: Record<string, { label: string; color: any; icon: string }> = {
        unpaid: { label: 'Belum bayar', color: 'warning', icon: 'i-lucide-alert-circle' },
        paid: { label: 'Lunas', color: 'success', icon: 'i-lucide-credit-card' },
        refunded: { label: 'Refund', color: 'neutral', icon: 'i-lucide-undo-2' },
        failed: { label: 'Gagal', color: 'error', icon: 'i-lucide-ban' },
    }

    const statusItems = computed(() => [
        { label: 'Semua status', value: 'all' },
        ...Object.entries(statusMeta).map(([value, meta]) => ({ label: meta.label, value })),
    ])

    const sortItems: Array<{ label: string; value: SortValue }> = [
        { label: 'Terbaru', value: 'newest' },
        { label: 'Terlama', value: 'oldest' },
        { label: 'Total tertinggi', value: 'highest' },
        { label: 'Total terendah', value: 'lowest' },
    ]

    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)

    const formatDateTime = (value: string | null | undefined): string => {
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

    const filtered = computed(() => {
        const keyword = q.value.trim().toLowerCase()
        let items = [...allOrders.value]

        if (status.value !== 'all') {
            items = items.filter((order) => order.status === status.value)
        }

        if (keyword) {
            items = items.filter((order) => {
                const hay = [order.code, String(order.id), order.customer?.name ?? '', order.customer?.email ?? '', order.tracking_number ?? '']
                    .join(' ')
                    .toLowerCase()

                return hay.includes(keyword)
            })
        }

        items.sort((a, b) => {
            const aDate = new Date(a.created_at).getTime()
            const bDate = new Date(b.created_at).getTime()

            if (sort.value === 'newest') {
                return bDate - aDate
            }

            if (sort.value === 'oldest') {
                return aDate - bDate
            }

            if (sort.value === 'highest') {
                return b.total - a.total
            }

            return a.total - b.total
        })

        return items
    })

    const totalCount = computed(() => options.orders.value.total ?? allOrders.value.length)
    const shownCount = computed(() => filtered.value.length)

    const detailItems = computed<DashboardOrderItemPreview[]>(() => {
        if (!selectedOrder.value) {
            return []
        }

        if ((selectedOrder.value.items?.length ?? 0) > 0) {
            return selectedOrder.value.items ?? []
        }

        return selectedOrder.value.items_preview ?? []
    })

    function reset(): void {
        q.value = ''
        status.value = 'all'
        sort.value = 'newest'
    }

    function loadMore(): void {
        if (isLoadingMore.value || !hasMore.value || !nextPage.value) {
            return
        }

        isLoadingMore.value = true

        router.get('/dashboard', { section: 'orders', orders_page: nextPage.value }, {
            only: ['orders'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                isLoadingMore.value = false
            },
        })
    }

    function openDetail(order: DashboardOrder): void {
        selectedOrder.value = { ...order }
        isDetailOpen.value = true
    }

    function closeDetail(): void {
        isDetailOpen.value = false
    }

    watch(isDetailOpen, (open) => {
        if (!open) {
            selectedOrder.value = null
        }
    })

    function isOrderUnpaid(order: DashboardOrder): boolean {
        return ['unpaid', 'failed'].includes(String(order.payment_status ?? ''))
            || (order.payment_status == null && order.status === 'pending')
    }

    function isMidtransOrder(order: DashboardOrder): boolean {
        const code = String(order.payment_method_code ?? '').trim().toLowerCase()
        return ['p-001', 'midtrans'].includes(code)
    }

    function canPayNow(order: DashboardOrder): boolean {
        return isOrderUnpaid(order) && isMidtransOrder(order)
    }

    function canDownloadInvoice(order: DashboardOrder): boolean {
        return Boolean(order.paid_at)
    }

    function invoiceDownloadUrl(order: DashboardOrder): string {
        return `/dashboard/orders/${order.id}/invoice`
    }

    function downloadInvoice(order: DashboardOrder): void {
        if (!canDownloadInvoice(order)) {
            toast?.add?.({
                title: 'Invoice belum tersedia',
                description: 'Invoice hanya dapat diunduh untuk pesanan yang sudah dibayar.',
                color: 'warning',
                icon: 'i-lucide-file-warning',
            })

            return
        }

        const url = invoiceDownloadUrl(order)
        const popup = window.open(url, '_blank', 'noopener,noreferrer')

        if (popup) {
            return
        }

        window.location.assign(url)
    }

    function getSnapScriptUrl(): string {
        const host = options.midtrans.value.env === 'production' ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com'
        return `${host}/snap/snap.js`
    }

    async function ensureSnapLoaded(): Promise<boolean> {
        if (window.snap?.pay) {
            return true
        }

        if (!options.midtrans.value.client_key) {
            return false
        }

        return new Promise((resolve) => {
            const existingScript = document.querySelector<HTMLScriptElement>('script[data-midtrans-snap=\"1\"]')

            if (existingScript) {
                existingScript.addEventListener('load', () => resolve(!!window.snap?.pay))
                existingScript.addEventListener('error', () => resolve(false))
                return
            }

            const script = document.createElement('script')
            script.src = getSnapScriptUrl()
            script.async = true
            script.setAttribute('data-midtrans-snap', '1')
            script.setAttribute('data-client-key', options.midtrans.value.client_key)
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

    function normalizeImageUrl(url?: string | null): string | null {
        const raw = String(url ?? '').trim()

        if (raw === '') {
            return null
        }

        if (raw.startsWith('data:image/')) {
            return raw
        }

        if (raw.startsWith('http://') || raw.startsWith('https://')) {
            try {
                const parsed = new URL(raw)
                const normalizedPath = parsed.pathname.replace(/\/{2,}/g, '/')

                if (normalizedPath.startsWith('/storage/')) {
                    return `${normalizedPath}${parsed.search}`
                }

                return raw
            } catch {
                return raw
            }
        }

        const normalized = raw.replace(/\/{2,}/g, '/')

        if (normalized.startsWith('/storage/')) {
            return normalized
        }

        if (normalized.startsWith('/')) {
            return normalized
        }

        if (normalized.startsWith('public/storage/')) {
            return `/${normalized.slice('public/'.length)}`
        }

        if (normalized.startsWith('storage/')) {
            return `/${normalized}`
        }

        if (normalized.startsWith('public/')) {
            return `/storage/${normalized.slice('public/'.length)}`
        }

        return `/storage/${normalized}`
    }

    function shippingAddressLine(order: DashboardOrder): string {
        const address = order.shipping_address

        if (!address) {
            return '-'
        }

        return [
            address.address_line1,
            address.address_line2,
            address.district,
            address.city,
            address.province,
            address.postal_code,
            address.country,
        ]
            .filter((part) => typeof part === 'string' && part.trim() !== '')
            .join(', ')
    }

    function replaceOrderState(updatedOrder: DashboardOrder): void {
        allOrders.value = allOrders.value.map((order) =>
            String(order.id) === String(updatedOrder.id) ? { ...order, ...updatedOrder } : order
        )

        if (selectedOrder.value && String(selectedOrder.value.id) === String(updatedOrder.id)) {
            selectedOrder.value = { ...selectedOrder.value, ...updatedOrder }
        }
    }

    async function checkPaymentStatus(order: DashboardOrder): Promise<void> {
        if (checkingPaymentOrderId.value !== null) {
            return
        }

        checkingPaymentOrderId.value = String(order.id)

        try {
            const response = await inertiaPost(
                `/dashboard/orders/${order.id}/payment-status`,
                {},
                ['flash', 'errors', 'orders']
            )
            const flash = response.flash?.orders
            const flashPayload = (flash?.payload ?? {}) as { order?: DashboardOrder }

            if (flashPayload.order) {
                replaceOrderState(flashPayload.order)
            }

            toast?.add?.({
                title: 'Status pembayaran diperbarui',
                description: flash?.message ?? 'Status terbaru berhasil dimuat.',
                color: 'success',
                icon: 'i-lucide-badge-check',
            })
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Gagal memeriksa status pembayaran.'

            toast?.add?.({
                title: 'Cek status gagal',
                description: message,
                color: 'error',
                icon: 'i-lucide-x-circle',
            })
        } finally {
            checkingPaymentOrderId.value = null
        }
    }

    async function payNow(order: DashboardOrder): Promise<void> {
        if (payingOrderId.value !== null) {
            return
        }

        if (!canPayNow(order)) {
            return
        }

        if (!options.midtrans.value.client_key) {
            toast?.add?.({
                title: 'Midtrans belum aktif',
                description: 'Client key Midtrans belum tersedia.',
                color: 'error',
                icon: 'i-lucide-x-circle',
            })
            return
        }

        payingOrderId.value = String(order.id)

        try {
            if (isDetailOpen.value && selectedOrder.value && String(selectedOrder.value.id) === String(order.id)) {
                isDetailOpen.value = false
                await nextTick()
            }

            const response = await inertiaPost(`/dashboard/orders/${order.id}/pay-now`)
            const flash = response.flash?.orders
            const payload = (flash?.payload ?? {}) as {
                snapToken?: string | null
                redirectUrl?: string | null
                successUrl?: string | null
                pendingUrl?: string | null
            }

            if (payload.redirectUrl) {
                window.location.assign(payload.redirectUrl)
                return
            }

            if (!payload.snapToken) {
                throw new Error(flash?.message ?? 'Token Midtrans tidak tersedia untuk order ini.')
            }

            const snapLoaded = await ensureSnapLoaded()

            if (!snapLoaded) {
                throw new Error('Midtrans Snap gagal dimuat. Periksa konfigurasi client key.')
            }

            payingOrderId.value = null

            window.snap?.pay(payload.snapToken, {
                onSuccess: () => {
                    router.visit(payload.successUrl ?? '/dashboard')
                },
                onPending: () => {
                    router.visit(payload.pendingUrl ?? payload.successUrl ?? '/dashboard')
                },
                onError: () => {
                    toast?.add?.({
                        title: 'Pembayaran gagal',
                        description: 'Terjadi kesalahan saat memproses pembayaran Midtrans.',
                        color: 'error',
                        icon: 'i-lucide-x-circle',
                    })
                },
                onClose: () => {
                    toast?.add?.({
                        title: 'Pembayaran dibatalkan',
                        description: 'Kamu menutup popup pembayaran.',
                        color: 'warning',
                        icon: 'i-lucide-alert-circle',
                    })
                },
            })
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Gagal membuka pembayaran Midtrans.'

            toast?.add?.({
                title: 'Bayar sekarang gagal',
                description: message,
                color: 'error',
                icon: 'i-lucide-x-circle',
            })
        } finally {
            if (payingOrderId.value === String(order.id)) {
                payingOrderId.value = null
            }
        }
    }

    return {
        allOrders,
        hasMore,
        nextPage,
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
    }
}
