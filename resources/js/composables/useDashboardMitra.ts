import { computed, ref, watch, type ComputedRef } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import type { DashboardMitraMember } from '@/types/dashboard'

type TabKey = 'active' | 'passive' | 'prospect'
type TabItem = { value: TabKey; label: string; icon: string; count: number }

type UseDashboardMitraOptions = {
    activeMembers: ComputedRef<DashboardMitraMember[]>
    passiveMembers: ComputedRef<DashboardMitraMember[]>
    prospectMembers: ComputedRef<DashboardMitraMember[]>
    hasLeft: ComputedRef<boolean>
    hasRight: ComputedRef<boolean>
    currentCustomerId: ComputedRef<number | null | undefined>
}

export function useDashboardMitra(options: UseDashboardMitraOptions) {
    const toast = useToast()

    const activeTab = ref<TabKey>('active')
    const q = ref('')

    const counts = computed(() => ({
        active: options.activeMembers.value.length,
        passive: options.passiveMembers.value.length,
        prospect: options.prospectMembers.value.length,
    }))

    const totalMembers = computed(() => counts.value.active + counts.value.passive + counts.value.prospect)

    const tabs = computed<TabItem[]>(() => [
        { value: 'active', label: 'Aktif', icon: 'i-lucide-check-circle-2', count: counts.value.active },
        { value: 'passive', label: 'Pasif', icon: 'i-lucide-clock', count: counts.value.passive },
        { value: 'prospect', label: 'Prospek', icon: 'i-lucide-user-plus', count: counts.value.prospect },
    ])

    const membersByTab = computed(() => {
        if (activeTab.value === 'active') {
            return options.activeMembers.value
        }

        if (activeTab.value === 'passive') {
            return options.passiveMembers.value
        }

        return options.prospectMembers.value
    })

    const filteredMembers = computed(() => {
        const keyword = q.value.trim().toLowerCase()
        const arr = [...membersByTab.value]

        if (!keyword) {
            return arr
        }

        return arr.filter((member) => {
            const hay = [
                member.name,
                member.username ?? '',
                member.email ?? '',
                member.phone ?? '',
                member.package_name ?? '',
                member.position ?? '',
                String(member.level ?? ''),
                String(member.total_left ?? ''),
                String(member.total_right ?? ''),
            ]
                .join(' ')
                .toLowerCase()

            return hay.includes(keyword)
        })
    })

    const hintText = computed(() => {
        if (activeTab.value === 'active') {
            return 'Sudah ditempatkan di binary tree.'
        }

        if (activeTab.value === 'passive') {
            return 'Belum ditempatkan, tapi sudah memiliki pembelian.'
        }

        return 'Member baru, belum ditempatkan dan belum pembelian.'
    })

    const isDetailOpen = ref(false)
    const detailMember = ref<DashboardMitraMember | null>(null)

    const showPlacementDialog = ref(false)
    const selectedMember = ref<DashboardMitraMember | null>(null)
    const selectedPosition = ref<'left' | 'right' | null>(null)

    const placementForm = useForm({
        member_id: 0,
        upline_id: 0,
        position: '' as 'left' | 'right' | '',
    })

    const hasLeft = computed(() => options.hasLeft.value)
    const hasRight = computed(() => options.hasRight.value)

    const formatDate = (dateString: string | null | undefined): string => {
        if (!dateString) {
            return '-'
        }

        const date = new Date(dateString)

        if (Number.isNaN(date.getTime())) {
            return dateString
        }

        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    }

    const formatCurrency = (amount: number) =>
        new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount)

    const getPositionBadge = (position: string | null | undefined) => {
        if (!position) {
            return { color: 'neutral' as const, variant: 'subtle' as const, text: 'Belum Ditempatkan' }
        }

        return {
            color: position === 'left' ? ('primary' as const) : ('info' as const),
            variant: 'soft' as const,
            text: position === 'left' ? 'Kiri' : 'Kanan',
        }
    }

    const tabStatusBadge = (tab: TabKey) => {
        if (tab === 'active') {
            return { color: 'success' as const, icon: 'i-lucide-check-circle-2', text: 'Aktif' }
        }

        if (tab === 'passive') {
            return { color: 'neutral' as const, icon: 'i-lucide-clock', text: 'Pasif' }
        }

        return { color: 'warning' as const, icon: 'i-lucide-user-plus', text: 'Prospek' }
    }

    function memberState(member: DashboardMitraMember) {
        const hasPlacement = member.has_placement === true || !!member.position
        const hasPurchase = member.has_purchase === true

        if (hasPlacement) {
            return { key: 'active' as const, ...tabStatusBadge('active') }
        }

        if (hasPurchase) {
            return { key: 'passive' as const, ...tabStatusBadge('passive') }
        }

        return { key: 'prospect' as const, ...tabStatusBadge('prospect') }
    }

    function openDetail(member: DashboardMitraMember): void {
        detailMember.value = member
        isDetailOpen.value = true
    }

    function closeDetail(): void {
        isDetailOpen.value = false
    }

    function openPlacementDialog(member: DashboardMitraMember): void {
        if (!options.currentCustomerId.value) {
            toast?.add?.({
                title: 'Gagal membuka modal',
                description: 'Data upline tidak ditemukan. Muat ulang halaman dan coba lagi.',
                color: 'error',
                icon: 'i-lucide-x-circle',
            })
            return
        }

        selectedMember.value = member
        selectedPosition.value = null
        placementForm.member_id = member.id
        placementForm.upline_id = options.currentCustomerId.value
        placementForm.position = ''
        showPlacementDialog.value = true
    }

    const closePlacementDialog = () => {
        showPlacementDialog.value = false
        selectedMember.value = null
        selectedPosition.value = null
        placementForm.reset()
    }

    const placeToBinaryTree = () => {
        if (!selectedMember.value || !selectedPosition.value) {
            toast?.add?.({ title: 'Pilih posisi terlebih dahulu', color: 'warning', icon: 'i-lucide-alert-circle' })
            return
        }

        if (!placementForm.upline_id) {
            toast?.add?.({
                title: 'Gagal',
                description: 'Data upline tidak valid.',
                color: 'error',
                icon: 'i-lucide-x-circle',
            })
            return
        }

        placementForm.position = selectedPosition.value

        placementForm.post('/mlm/place-member', {
            onSuccess: () => {
                toast?.add?.({
                    title: 'Berhasil',
                    description: `${selectedMember.value?.name} ditempatkan di posisi ${selectedPosition.value === 'left' ? 'Kiri' : 'Kanan'}`,
                    color: 'success',
                    icon: 'i-lucide-check-circle-2',
                })
                closePlacementDialog()
            },
            onError: (errors: any) => {
                toast?.add?.({
                    title: 'Gagal',
                    description: errors?.error || 'Gagal menempatkan member ke binary tree',
                    color: 'error',
                    icon: 'i-lucide-x-circle',
                })
            },
        })
    }

    function openPlacementFromDetail(): void {
        if (!detailMember.value) {
            return
        }

        const selected = detailMember.value
        closeDetail()
        openPlacementDialog(selected)
    }

    watch(isDetailOpen, (open) => {
        if (!open) {
            detailMember.value = null
        }
    })

    return {
        activeTab,
        q,
        counts,
        totalMembers,
        tabs,
        filteredMembers,
        hintText,
        formatDate,
        formatCurrency,
        getPositionBadge,
        tabStatusBadge,
        memberState,
        isDetailOpen,
        detailMember,
        openDetail,
        closeDetail,
        showPlacementDialog,
        selectedMember,
        selectedPosition,
        placementForm,
        hasLeft,
        hasRight,
        openPlacementDialog,
        closePlacementDialog,
        placeToBinaryTree,
        openPlacementFromDetail,
    }
}
