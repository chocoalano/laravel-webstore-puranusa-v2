import { computed, ref, type ComputedRef } from 'vue'
import type { DashboardPromo, DashboardPromoType } from '@/types/dashboard'

export type DashboardPromoFilterType = DashboardPromoType | 'all'

export type DashboardPromoTypeMeta = {
    label: string
    color: string
    icon: string
    accentClass: string
    iconClass: string
}

type UseDashboardPromoOptions = {
    promos: ComputedRef<DashboardPromo[]>
}

function isExpired(expiresAt?: string | null): boolean {
    if (!expiresAt) {
        return false
    }

    return Date.parse(expiresAt) < Date.now()
}

export function useDashboardPromo(options: UseDashboardPromoOptions) {
    const searchQuery = ref('')
    const selectedType = ref<DashboardPromoFilterType>('all')
    const onlyAvailable = ref(true)
    const copiedCode = ref<string | null>(null)

    const typeMeta: Record<DashboardPromoType, DashboardPromoTypeMeta> = {
        voucher: {
            label: 'Voucher',
            color: 'primary',
            icon: 'i-lucide-ticket',
            accentClass: 'bg-primary',
            iconClass: 'text-primary',
        },
        discount: {
            label: 'Diskon',
            color: 'success',
            icon: 'i-lucide-percent',
            accentClass: 'bg-emerald-500',
            iconClass: 'text-emerald-500',
        },
        flash: {
            label: 'Flash Sale',
            color: 'warning',
            icon: 'i-lucide-zap',
            accentClass: 'bg-amber-500',
            iconClass: 'text-amber-500',
        },
        shipping: {
            label: 'Gratis Ongkir',
            color: 'info',
            icon: 'i-lucide-truck',
            accentClass: 'bg-sky-500',
            iconClass: 'text-sky-500',
        },
        bundle: {
            label: 'Bundle',
            color: 'neutral',
            icon: 'i-lucide-package',
            accentClass: 'bg-indigo-500',
            iconClass: 'text-indigo-500',
        },
        member: {
            label: 'Exclusive',
            color: 'primary',
            icon: 'i-lucide-crown',
            accentClass: 'bg-fuchsia-500',
            iconClass: 'text-fuchsia-500',
        },
    }

    const typeItems = computed<Array<{ label: string; value: DashboardPromoFilterType; icon: string }>>(() => [
        { label: 'Semua Tipe', value: 'all', icon: 'i-lucide-layers' },
        ...Object.entries(typeMeta).map(([value, meta]) => ({
            label: meta.label,
            value: value as DashboardPromoType,
            icon: meta.icon,
        })),
    ])

    const selectedTypeIcon = computed(() =>
        selectedType.value === 'all' ? 'i-lucide-filter' : typeMeta[selectedType.value].icon
    )

    const filteredPromos = computed<DashboardPromo[]>(() => {
        let data = [...options.promos.value]

        if (selectedType.value !== 'all') {
            data = data.filter((promo) => promo.type === selectedType.value)
        }

        const keyword = searchQuery.value.trim().toLowerCase()

        if (keyword !== '') {
            data = data.filter((promo) =>
                promo.title.toLowerCase().includes(keyword)
                || String(promo.code ?? '').toLowerCase().includes(keyword)
            )
        }

        if (onlyAvailable.value) {
            data = data.filter((promo) => !isExpired(promo.expires_at))
        }

        return data.sort((left, right) => Number(!!right.highlight) - Number(!!left.highlight))
    })

    function formatExpiry(expiresAt?: string | null): string {
        if (!expiresAt) {
            return 'Selamanya'
        }

        const parsed = Date.parse(expiresAt)

        if (Number.isNaN(parsed)) {
            return expiresAt
        }

        const date = new Date(parsed)
        return `Hingga ${date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })}`
    }

    let copiedTimer: ReturnType<typeof setTimeout> | null = null

    async function copyCode(code?: string | null): Promise<void> {
        if (!code || typeof navigator === 'undefined' || !navigator.clipboard) {
            return
        }

        await navigator.clipboard.writeText(code)
        copiedCode.value = code

        if (copiedTimer) {
            clearTimeout(copiedTimer)
        }

        copiedTimer = setTimeout(() => {
            copiedCode.value = null
        }, 2000)
    }

    function resetFilters(): void {
        searchQuery.value = ''
        selectedType.value = 'all'
        onlyAvailable.value = true
    }

    return {
        searchQuery,
        selectedType,
        onlyAvailable,
        copiedCode,
        typeMeta,
        typeItems,
        selectedTypeIcon,
        filteredPromos,
        formatExpiry,
        copyCode,
        resetFilters,
    }
}
