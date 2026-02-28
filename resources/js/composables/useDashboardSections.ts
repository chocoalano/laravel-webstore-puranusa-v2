import { computed, defineAsyncComponent, onBeforeUnmount, onMounted, ref, watch, type ComputedRef } from 'vue'
import { router } from '@inertiajs/vue3'
import type { DashboardAsideLink, DashboardPageProps, DashboardSectionKey } from '@/types/dashboard'

const componentMap: Record<DashboardSectionKey, ReturnType<typeof defineAsyncComponent>> = {
    dashboard: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/DashboardHome.vue')),
    form_account: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/FormAccount.vue')),
    orders: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Orders.vue')),
    promo: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Promo.vue')),
    wallet: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Wallet.vue')),
    zenner: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Zenner.vue')),
    mitra: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Mitra.vue')),
    network: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Network.vue')),
    bonus: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Bonus.vue')),
    lifetime: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Lifetime.vue')),
    addresses: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/Addresses.vue')),
    delete: defineAsyncComponent(() => import('@/Pages/Auth/Dashboard/partials/DeleteAccount.vue')),
}

const dashboardPropKeys = [
    'customer',
    'defaultAddress',
    'stats',
    'networkProfile',
    'networkStats',
    'securitySummary',
]

const addressPropKeys = [
    'addresses',
    'defaultAddress',
]

const formAccountPropKeys = ['customer', 'defaultAddress']
const ordersPropKeys = ['orders']
const promoPropKeys = ['promos']
const zennerPropKeys = ['zennerCategories', 'zennerContents']
const walletPropKeys = ['customer', 'stats', 'walletTransactions', 'hasPendingWithdrawal', 'midtrans']
const bonusPropKeys = ['bonusStats', 'bonusTables']
const lifetimePropKeys = ['lifetimeRewards']
const mitraPropKeys = ['currentCustomerId', 'activeMembers', 'passiveMembers', 'prospectMembers', 'hasLeft', 'hasRight']
const networkPropKeys = ['currentCustomerId', 'passiveMembers', 'binaryTree', 'networkTreeStats']

function isDashboardSection(value: string): value is DashboardSectionKey {
    return Object.prototype.hasOwnProperty.call(componentMap, value)
}

function resolveInitialSection(url: string): DashboardSectionKey {
    const query = url.split('?')[1] ?? ''
    const section = new URLSearchParams(query).get('section')

    if (section && isDashboardSection(section)) {
        return section
    }

    return 'dashboard'
}

export function useDashboardSections(props: ComputedRef<DashboardPageProps>, initialUrl: string) {
    const active = ref<DashboardSectionKey>(resolveInitialSection(initialUrl))

    const currentComponent = computed(() => componentMap[active.value] ?? componentMap.dashboard)

    const currentComponentProps = computed<Record<string, unknown>>(() => {
        switch (active.value) {
            case 'dashboard':
                return {
                    customer: props.value.customer,
                    defaultAddress: props.value.defaultAddress,
                    stats: props.value.stats,
                    networkProfile: props.value.networkProfile,
                    networkStats: props.value.networkStats,
                    securitySummary: props.value.securitySummary,
                }
            case 'addresses':
                return {
                    addresses: props.value.addresses,
                }
            case 'form_account':
                return {
                    customer: props.value.customer,
                    defaultAddress: props.value.defaultAddress,
                }
            case 'orders':
                return {
                    orders: props.value.orders,
                    midtrans: props.value.midtrans,
                }
            case 'wallet':
                return {
                    customer: props.value.customer,
                    transactions: props.value.walletTransactions,
                    hasPendingWithdrawal: props.value.hasPendingWithdrawal,
                    walletBalance: props.value.stats?.wallet_balance,
                    midtrans: props.value.midtrans,
                }
            case 'bonus':
                return {
                    bonusStats: props.value.bonusStats,
                    bonusTables: props.value.bonusTables,
                }
            case 'lifetime':
                return {
                    lifetimeRewards: props.value.lifetimeRewards,
                }
            case 'promo':
                return {
                    promos: props.value.promos,
                }
            case 'zenner':
                return {
                    categories: props.value.zennerCategories,
                    contents: props.value.zennerContents,
                }
            case 'mitra':
                return {
                    activeMembers: props.value.activeMembers,
                    passiveMembers: props.value.passiveMembers,
                    prospectMembers: props.value.prospectMembers,
                    hasLeft: props.value.hasLeft,
                    hasRight: props.value.hasRight,
                    currentCustomerId: props.value.currentCustomerId,
                }
            case 'network':
                return {
                    binaryTree: props.value.binaryTree,
                    networkTreeStats: props.value.networkTreeStats,
                    passiveMembers: props.value.passiveMembers,
                    currentCustomerId: props.value.currentCustomerId,
                }
            default:
                return {}
        }
    })

    const currentComponentListeners = computed<Record<string, unknown>>(() => {
        if (active.value !== 'dashboard') {
            return {}
        }

        return {
            navigate: (value: string): void => {
                if (isDashboardSection(value)) {
                    setActive(value)
                }
            },
        }
    })

    const asideLinks = computed<DashboardAsideLink[]>(() => [
        { label: 'Akun', type: 'label' },
        { label: 'Info Pengguna', icon: 'i-lucide-user', value: 'dashboard' },
        { label: 'Form Pengguna', icon: 'i-lucide-form', value: 'form_account' },
        { label: 'Order', icon: 'i-lucide-package-search', value: 'orders' },
        { label: 'Promo', icon: 'i-lucide-ticket', value: 'promo' },
        { label: 'Wallet', icon: 'i-lucide-wallet', value: 'wallet' },
        { label: 'Alamat', icon: 'i-lucide-map-pin', value: 'addresses' },
        { label: 'Mitra & Network', type: 'label' },
        { label: 'Zenner', icon: 'i-lucide-sparkles', value: 'zenner' },
        { label: 'Mitra', icon: 'i-lucide-handshake', value: 'mitra' },
        { label: 'Network', icon: 'i-lucide-network', value: 'network' },
        { label: 'Bonus', icon: 'i-lucide-coins', value: 'bonus' },
        { label: 'Lifetime', icon: 'i-lucide-trophy', value: 'lifetime' },
        { label: 'Keamanan', type: 'label' },
        { label: 'Delete Account', icon: 'i-lucide-user-x', value: 'delete', color: 'error' },
    ])

    function getSectionOnlyProps(section: DashboardSectionKey): string[] {
        switch (section) {
            case 'dashboard':
                return dashboardPropKeys
            case 'addresses':
                return addressPropKeys
            case 'form_account':
                return formAccountPropKeys
            case 'orders':
                return ordersPropKeys
            case 'promo':
                return promoPropKeys
            case 'zenner':
                return zennerPropKeys
            case 'wallet':
                return walletPropKeys
            case 'bonus':
                return bonusPropKeys
            case 'lifetime':
                return lifetimePropKeys
            case 'mitra':
                return mitraPropKeys
            case 'network':
                return networkPropKeys
            default:
                return []
        }
    }

    function buildSectionQuery(section: DashboardSectionKey): Record<string, string | number> {
        const query: Record<string, string | number> = { section }

        if (section === 'orders') {
            const page = Number(props.value.orders?.current_page ?? 1)

            if (page > 1) {
                query.orders_page = page
            }

            return query
        }

        if (section === 'wallet') {
            const walletPayload = props.value.walletTransactions
            const page = Number(walletPayload?.current_page ?? 1)
            const filters = walletPayload?.filters
            const search = (filters?.search ?? '').trim()
            const type = (filters?.type ?? '').trim()
            const status = (filters?.status ?? '').trim()

            if (page > 1) {
                query.wallet_page = page
            }

            if (search !== '') {
                query.wallet_search = search
            }

            if (type !== '' && type !== 'all') {
                query.wallet_type = type
            }

            if (status !== '' && status !== 'all') {
                query.wallet_status = status
            }
        }

        return query
    }

    function visitSection(section: DashboardSectionKey): void {
        router.get('/dashboard', buildSectionQuery(section), {
            only: getSectionOnlyProps(section),
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }

    function reloadDashboardSnapshot(): void {
        if (active.value !== 'dashboard') {
            return
        }

        router.reload({
            only: dashboardPropKeys,
        })
    }

    function reloadAddressSnapshot(): void {
        if (active.value !== 'addresses') {
            return
        }

        router.reload({
            only: addressPropKeys,
        })
    }

    function reloadFormAccountSnapshot(): void {
        if (active.value !== 'form_account') {
            return
        }

        router.reload({
            only: formAccountPropKeys,
        })
    }

    function reloadMitraSnapshot(): void {
        if (active.value !== 'mitra') {
            return
        }

        router.reload({
            only: mitraPropKeys,
        })
    }

    function reloadNetworkSnapshot(): void {
        if (active.value !== 'network') {
            return
        }

        router.reload({
            only: networkPropKeys,
        })
    }

    function reloadPromoSnapshot(): void {
        if (active.value !== 'promo') {
            return
        }

        router.reload({
            only: promoPropKeys,
        })
    }

    function reloadZennerSnapshot(): void {
        if (active.value !== 'zenner') {
            return
        }

        router.reload({
            only: zennerPropKeys,
        })
    }

    function reloadWalletSnapshot(): void {
        if (active.value !== 'wallet') {
            return
        }

        router.reload({
            only: walletPropKeys,
        })
    }

    function reloadBonusSnapshot(): void {
        if (active.value !== 'bonus') {
            return
        }

        router.reload({
            only: bonusPropKeys,
        })
    }

    function reloadLifetimeSnapshot(): void {
        if (active.value !== 'lifetime') {
            return
        }

        router.reload({
            only: lifetimePropKeys,
        })
    }

    function reloadOrdersSnapshot(page = 1): void {
        if (active.value !== 'orders') {
            return
        }

        router.get('/dashboard', {
            section: 'orders',
            orders_page: page,
        }, {
            only: ordersPropKeys,
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }

    let dashboardPollTimer: number | undefined

    onMounted(() => {
        dashboardPollTimer = window.setInterval(() => {
            if (document.visibilityState !== 'visible') {
                return
            }

            reloadDashboardSnapshot()
            reloadAddressSnapshot()
            reloadFormAccountSnapshot()
            reloadPromoSnapshot()
            reloadZennerSnapshot()
            reloadMitraSnapshot()
            reloadNetworkSnapshot()
            reloadWalletSnapshot()
            reloadBonusSnapshot()
            reloadLifetimeSnapshot()
        }, 30000)

        if (active.value === 'orders' && !props.value.orders) {
            reloadOrdersSnapshot(1)
        }

        if (active.value === 'promo' && !props.value.promos) {
            reloadPromoSnapshot()
        }

        if (active.value === 'form_account' && !props.value.customer) {
            reloadFormAccountSnapshot()
        }

        if (active.value === 'zenner' && !props.value.zennerContents) {
            reloadZennerSnapshot()
        }

        if (active.value === 'wallet' && !props.value.walletTransactions) {
            reloadWalletSnapshot()
        }

        if (active.value === 'bonus' && (!props.value.bonusStats || !props.value.bonusTables)) {
            reloadBonusSnapshot()
        }

        if (active.value === 'lifetime' && !props.value.lifetimeRewards) {
            reloadLifetimeSnapshot()
        }

        if (active.value === 'network' && !props.value.binaryTree) {
            reloadNetworkSnapshot()
        }
    })

    onBeforeUnmount(() => {
        if (dashboardPollTimer !== undefined) {
            window.clearInterval(dashboardPollTimer)
        }
    })

    watch(active, (current, previous) => {
        if (current === previous) {
            return
        }

        visitSection(current)
    })

    function setActive(section: DashboardSectionKey): void {
        if (section === active.value) {
            visitSection(section)
            return
        }

        active.value = section
    }

    return {
        active,
        currentComponent,
        currentComponentProps,
        currentComponentListeners,
        asideLinks,
        setActive,
    }
}
