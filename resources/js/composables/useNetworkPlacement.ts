import { computed, ref, type Ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import type { DashboardMitraMember } from '@/types/dashboard'

export function useNetworkPlacement(
    passiveMembers: Ref<DashboardMitraMember[]>,
    currentCustomerId: Ref<number | null>,
) {
    const toast = useToast()

    const showPlacementDialog = ref(false)
    const selectedUplineId = ref<number | null>(null)
    const selectedPosition = ref<'left' | 'right' | null>(null)
    const selectedMember = ref<DashboardMitraMember | null>(null)
    const memberSearchQuery = ref('')

    const placementForm = useForm({
        member_id: 0,
        upline_id: 0,
        position: '' as 'left' | 'right' | '',
    })

    const filteredPassiveMembers = computed(() => {
        const query = memberSearchQuery.value.trim().toLowerCase()

        if (query === '') {
            return passiveMembers.value
        }

        return passiveMembers.value.filter((member) => {
            const haystack = `${member.name} ${member.email} ${member.phone ?? ''} ${member.username ?? ''}`.toLowerCase()

            return haystack.includes(query)
        })
    })

    function applyPlacementContext(payload: { uplineId: number; position: 'left' | 'right' }): void {
        selectedUplineId.value = payload.uplineId
        selectedPosition.value = payload.position
        selectedMember.value = null
        memberSearchQuery.value = ''
        showPlacementDialog.value = true
    }

    function openPlacementDialog(payload: { uplineId: number; position: 'left' | 'right' }): void {
        if (!currentCustomerId.value) {
            toast.add({
                title: 'Upline tidak valid',
                description: 'Data customer login tidak tersedia.',
                color: 'error',
                icon: 'i-lucide-x-circle',
            })

            return
        }

        if (passiveMembers.value.length === 0) {
            router.reload({
                only: ['passiveMembers'],
                onSuccess: () => {
                    if (passiveMembers.value.length === 0) {
                        toast.add({
                            title: 'Member pasif belum tersedia',
                            description: 'Tidak ada member status pasif yang bisa ditempatkan saat ini.',
                            color: 'warning',
                            icon: 'i-lucide-alert-circle',
                        })

                        return
                    }

                    applyPlacementContext(payload)
                },
            })

            return
        }

        applyPlacementContext(payload)
    }

    function closePlacementDialog(): void {
        showPlacementDialog.value = false
        selectedUplineId.value = null
        selectedPosition.value = null
        selectedMember.value = null
        memberSearchQuery.value = ''
        placementForm.reset()
    }

    function selectMember(member: DashboardMitraMember): void {
        selectedMember.value = member
    }

    function placeMemberToBinaryTree(): void {
        if (!selectedMember.value || !selectedPosition.value || !selectedUplineId.value) {
            toast.add({
                title: 'Data belum lengkap',
                description: 'Pilih member dan posisi placement terlebih dahulu.',
                color: 'warning',
                icon: 'i-lucide-alert-circle',
            })

            return
        }

        placementForm.member_id = selectedMember.value.id
        placementForm.upline_id = selectedUplineId.value
        placementForm.position = selectedPosition.value

        placementForm.post('/mlm/place-member', {
            preserveScroll: true,
            onSuccess: () => {
                toast.add({
                    title: 'Placement berhasil',
                    description: `${selectedMember.value?.name} ditempatkan ke posisi ${selectedPosition.value === 'left' ? 'kiri' : 'kanan'}.`,
                    color: 'success',
                    icon: 'i-lucide-check-circle-2',
                })

                closePlacementDialog()

                router.reload({
                    only: ['binaryTree', 'networkTreeStats', 'activeMembers', 'passiveMembers', 'prospectMembers', 'hasLeft', 'hasRight'],
                })
            },
            onError: (errors: Record<string, unknown>) => {
                const errorMessage = typeof errors.error === 'string' ? errors.error : 'Gagal memproses placement member.'

                toast.add({
                    title: 'Placement gagal',
                    description: errorMessage,
                    color: 'error',
                    icon: 'i-lucide-x-circle',
                })
            },
        })
    }

    return {
        showPlacementDialog,
        selectedUplineId,
        selectedPosition,
        selectedMember,
        memberSearchQuery,
        placementForm,
        filteredPassiveMembers,
        openPlacementDialog,
        closePlacementDialog,
        selectMember,
        placeMemberToBinaryTree,
    }
}
