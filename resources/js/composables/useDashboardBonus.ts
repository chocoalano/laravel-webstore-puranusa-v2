import { computed, h, ref, watch, type ComputedRef } from 'vue'
import type { TableColumn, TabsItem } from '@nuxt/ui'
import { useDashboard } from '@/composables/useDashboard'
import type {
    DashboardBonusRow,
    DashboardBonusStat,
    DashboardBonusTables,
    DashboardBonusType,
} from '@/types/dashboard'

type BonusTab = DashboardBonusType | 'all'

type UseDashboardBonusOptions = {
    bonusStats: ComputedRef<DashboardBonusStat[]>
    bonusTables: ComputedRef<DashboardBonusTables>
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

function detailSummary(row: DashboardBonusRow): string {
    const parts: string[] = []

    if (row.from_member?.name) {
        parts.push(row.from_member.name)
    }

    if (row.from_member?.email) {
        parts.push(row.from_member.email)
    }

    if (row.meta?.level !== undefined && row.meta.level !== null) {
        parts.push(`Level ${row.meta.level}`)
    }

    if (row.meta?.pairing_count !== undefined && row.meta.pairing_count !== null) {
        parts.push(`Pair ${row.meta.pairing_count}`)
    }

    if (row.meta?.order_id !== undefined && row.meta.order_id !== null) {
        parts.push(`Order #${row.meta.order_id}`)
    }

    if (row.meta?.reward_name) {
        parts.push(`Reward ${row.meta.reward_name}`)
    }

    if (row.meta?.reward_type) {
        parts.push(`Type ${row.meta.reward_type}`)
    }

    return parts.join(' • ')
}

export function useDashboardBonus(options: UseDashboardBonusOptions) {
    const { formatIDR } = useDashboard()

    const categoryDefinitions: Array<{ key: DashboardBonusType; label: string; icon: string }> = [
        { key: 'referral_incentive', label: 'Referral Incentive', icon: 'i-lucide-users' },
        { key: 'team_affiliate_commission', label: 'Team Affiliate Commission', icon: 'i-lucide-handshake' },
        { key: 'partner_team_commission', label: 'Partner Team Commission', icon: 'i-lucide-network' },
        { key: 'cashback_commission', label: 'Cashback Commission', icon: 'i-lucide-percent' },
        { key: 'promotions_rewards', label: 'Promotions Rewards', icon: 'i-lucide-gift' },
        { key: 'retail_commission', label: 'Retail Commission', icon: 'i-lucide-store' },
        { key: 'lifetime_cash_rewards', label: 'Lifetime Cash Rewards', icon: 'i-lucide-trophy' },
    ]

    const statDefinitions: Array<{ key: DashboardBonusStat['key']; title: string; icon: string }> = [
        ...categoryDefinitions.map((item) => ({
            key: item.key,
            title: item.label,
            icon: item.icon,
        })),
        { key: 'total_bonus', title: 'Total Bonus', icon: 'i-lucide-wallet-cards' },
    ]

    const tabs: TabsItem[] = [
        { label: 'Semua', value: 'all', icon: 'i-lucide-layout-grid' },
        ...categoryDefinitions.map((item) => ({
            label: item.label,
            value: item.key,
            icon: item.icon,
        })),
    ]

    const activeTab = ref<BonusTab>('all')
    const searchQuery = ref('')
    const page = ref(1)
    const itemsPerPage = 10

    const displayedStats = computed(() =>
        statDefinitions.map((definition) => {
            const existing = options.bonusStats.value.find((item) => item.key === definition.key)

            return {
                key: definition.key,
                title: definition.title,
                icon: definition.icon,
                amount: Number(existing?.amount ?? 0),
                count: Number(existing?.count ?? 0),
            }
        })
    )

    const rowsByType = computed<DashboardBonusTables>(() => ({
        referral_incentive: [...options.bonusTables.value.referral_incentive],
        team_affiliate_commission: [...options.bonusTables.value.team_affiliate_commission],
        partner_team_commission: [...options.bonusTables.value.partner_team_commission],
        cashback_commission: [...options.bonusTables.value.cashback_commission],
        promotions_rewards: [...options.bonusTables.value.promotions_rewards],
        retail_commission: [...options.bonusTables.value.retail_commission],
        lifetime_cash_rewards: [...options.bonusTables.value.lifetime_cash_rewards],
    }))

    const allRows = computed<DashboardBonusRow[]>(() =>
        categoryDefinitions
            .flatMap((item) => rowsByType.value[item.key])
            .sort((left, right) => {
                const leftTime = left.created_at ? new Date(left.created_at).getTime() : 0
                const rightTime = right.created_at ? new Date(right.created_at).getTime() : 0

                if (leftTime === rightTime) {
                    return Number(right.id) - Number(left.id)
                }

                return rightTime - leftTime
            })
    )

    const filteredRows = computed<DashboardBonusRow[]>(() => {
        const keyword = searchQuery.value.trim().toLowerCase()
        const dataset = activeTab.value === 'all'
            ? allRows.value
            : rowsByType.value[activeTab.value] ?? []

        if (keyword === '') {
            return dataset
        }

        return dataset.filter((row) => {
            const haystack = [
                row.type_label,
                row.description ?? '',
                row.from_member?.name ?? '',
                row.from_member?.email ?? '',
                row.meta?.reward_name ?? '',
                row.meta?.reward_type ?? '',
            ].join(' ').toLowerCase()

            return haystack.includes(keyword)
        })
    })

    watch([activeTab, searchQuery], () => {
        page.value = 1
    })

    const paginatedRows = computed(() => {
        const start = (page.value - 1) * itemsPerPage
        return filteredRows.value.slice(start, start + itemsPerPage)
    })

    const columns: TableColumn<DashboardBonusRow>[] = [
        {
            id: 'created_at',
            accessorKey: 'created_at',
            header: 'Tanggal',
            cell: ({ row }) => h('span', { class: 'text-sm text-muted' }, formatDateTime(row.original.created_at)),
        },
        {
            id: 'description',
            accessorKey: 'description',
            header: 'Keterangan',
            cell: ({ row }) => {
                const original = row.original
                const subtitle = detailSummary(original)

                return h('div', { class: 'min-w-0 flex flex-col' }, [
                    h(
                        'span',
                        { class: 'truncate text-sm font-semibold text-highlighted' },
                        original.type_label
                    ),
                    h(
                        'span',
                        { class: 'truncate text-xs text-muted' },
                        original.description && original.description.trim() !== ''
                            ? original.description
                            : '-'
                    ),
                    h(
                        'span',
                        { class: 'truncate text-[11px] text-muted' },
                        subtitle !== '' ? subtitle : '-'
                    ),
                ])
            },
        },
        {
            id: 'amount',
            accessorKey: 'amount',
            header: 'Nominal',
            meta: {
                class: {
                    th: 'text-right',
                    td: 'text-right',
                },
            },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'font-bold text-primary tabular-nums' },
                    formatIDR(Number(row.original.amount ?? 0))
                ),
        },
        {
            id: 'status',
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const isReleased = row.original.status === 'released'

                return h(
                    'span',
                    {
                        class: [
                            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                            isReleased
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                        ],
                    },
                    row.original.status_label
                )
            },
        },
    ]

    return {
        activeTab,
        searchQuery,
        page,
        itemsPerPage,
        tabs,
        displayedStats,
        filteredRows,
        paginatedRows,
        columns,
        formatIDR,
    }
}
