import { computed, h, resolveComponent, type ComputedRef } from 'vue'
import type { TableColumn } from '@nuxt/ui'
import { useDashboard } from '@/composables/useDashboard'
import type {
    DashboardLifetimeClaimed,
    DashboardLifetimeReward,
    DashboardLifetimeRewardsData,
} from '@/types/dashboard'

type UseDashboardLifetimeOptions = {
    lifetimeRewards: ComputedRef<DashboardLifetimeRewardsData>
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

function progressColor(value: number): 'error' | 'warning' | 'success' {
    if (value >= 100) {
        return 'success'
    }

    if (value >= 60) {
        return 'warning'
    }

    return 'error'
}

export function useDashboardLifetime(options: UseDashboardLifetimeOptions) {
    const { formatIDR } = useDashboard()

    const summary = computed(() => options.lifetimeRewards.value.summary)
    const rewards = computed(() => options.lifetimeRewards.value.rewards)
    const claimed = computed(() => options.lifetimeRewards.value.claimed)

    const UBadge = resolveComponent('UBadge')

    const rewardColumns: TableColumn<DashboardLifetimeReward>[] = [
        {
            id: 'name',
            accessorKey: 'name',
            header: 'Reward',
            cell: ({ row }) => {
                const item = row.original
                const statusText = item.is_claimed ? 'Sudah Diklaim' : (item.can_claim ? 'Siap Klaim' : 'Belum Tercapai')

                return h('div', { class: 'min-w-0 space-y-1' }, [
                    h('p', { class: 'truncate text-sm font-semibold text-highlighted' }, item.name),
                    h('p', { class: 'truncate text-xs text-muted' }, item.reward && item.reward !== '' ? item.reward : '-'),
                    h('p', { class: 'text-[11px] text-muted' }, statusText),
                ])
            },
        },
        {
            id: 'bv',
            accessorKey: 'bv',
            header: 'Target BV',
            meta: {
                class: {
                    th: 'text-right',
                    td: 'text-right',
                },
            },
            cell: ({ row }) => h('span', { class: 'font-mono text-xs text-muted' }, row.original.bv.toLocaleString('id-ID')),
        },
        {
            id: 'progress',
            accessorKey: 'progress_percent',
            header: 'Progress',
            cell: ({ row }) => {
                const item = row.original
                const progressText = `${item.progress_percent.toFixed(2)}%`

                return h('div', { class: 'min-w-0 space-y-1' }, [
                    h(
                        UBadge as any,
                        {
                            color: progressColor(item.progress_percent),
                            variant: 'subtle',
                            size: 'xs',
                            class: 'rounded-full',
                        },
                        () => progressText
                    ),
                    h('p', { class: 'truncate text-[11px] text-muted' }, `Kiri ${formatIDR(item.accumulated_left)}`),
                    h('p', { class: 'truncate text-[11px] text-muted' }, `Kanan ${formatIDR(item.accumulated_right)}`),
                ])
            },
        },
        {
            id: 'status',
            accessorKey: 'can_claim',
            header: 'Status',
            cell: ({ row }) => {
                const item = row.original
                const color = item.is_claimed ? 'neutral' : (item.can_claim ? 'success' : 'warning')
                const label = item.is_claimed ? 'Claimed' : (item.can_claim ? 'Can Claim' : 'Locked')

                return h(
                    UBadge as any,
                    {
                        color,
                        variant: 'subtle',
                        size: 'sm',
                        class: 'rounded-full',
                    },
                    () => label
                )
            },
        },
    ]

    const claimedColumns: TableColumn<DashboardLifetimeClaimed>[] = [
        {
            id: 'created_at',
            accessorKey: 'created_at',
            header: 'Tanggal',
            cell: ({ row }) => h('span', { class: 'text-sm text-muted' }, formatDateTime(row.original.created_at)),
        },
        {
            id: 'reward',
            accessorKey: 'reward',
            header: 'Reward',
            cell: ({ row }) =>
                h('div', { class: 'min-w-0 space-y-1' }, [
                    h('p', { class: 'truncate text-sm font-semibold text-highlighted' }, row.original.reward ?? '-'),
                    h('p', { class: 'truncate text-xs text-muted' }, row.original.description ?? '-'),
                ]),
        },
        {
            id: 'bv',
            accessorKey: 'bv',
            header: 'BV',
            meta: {
                class: {
                    th: 'text-right',
                    td: 'text-right',
                },
            },
            cell: ({ row }) => h('span', { class: 'font-mono text-xs text-muted' }, row.original.bv.toLocaleString('id-ID')),
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
            cell: ({ row }) => h('span', { class: 'font-bold text-primary tabular-nums' }, formatIDR(row.original.amount)),
        },
        {
            id: 'status',
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) =>
                h(
                    UBadge as any,
                    {
                        color: row.original.status === 'released' ? 'success' : 'warning',
                        variant: 'subtle',
                        size: 'sm',
                        class: 'rounded-full',
                    },
                    () => row.original.status_label
                ),
        },
    ]

    return {
        summary,
        rewards,
        claimed,
        rewardColumns,
        claimedColumns,
        formatIDR,
    }
}
