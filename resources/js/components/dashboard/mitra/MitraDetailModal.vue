<script setup lang="ts">
import type { DashboardMitraMember } from '@/types/dashboard'

type TabKey = 'active' | 'passive' | 'prospect'

const props = withDefaults(
    defineProps<{
        open: boolean
        detailMember: DashboardMitraMember | null
        activeTab: TabKey
        hasLeft: boolean
        hasRight: boolean
        formatDate: (value: string | null | undefined) => string
        formatCurrency: (value: number) => string
        getPositionBadge: (position: string | null | undefined) => {
            color: 'neutral' | 'primary' | 'info'
            variant: 'subtle' | 'soft'
            text: string
        }
        tabStatusBadge: (tab: TabKey) => {
            color: 'success' | 'neutral' | 'warning'
            icon: string
            text: string
        }
        memberState: (member: DashboardMitraMember) => {
            key: TabKey
            color: 'success' | 'neutral' | 'warning'
            icon: string
            text: string
        }
    }>(),
    {
        detailMember: null,
    }
)

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'close'): void
    (e: 'place-member'): void
}>()

function closeModal(): void {
    emit('close')
    emit('update:open', false)
}

function placeMember(): void {
    emit('place-member')
}
</script>

<template>
    <UModal
        :open="open"
        :title="detailMember ? `Detail Member: ${detailMember.name}` : 'Detail Member'"
        description="Informasi lengkap member dalam jaringan."
        scrollable
        :ui="{ overlay: 'fixed inset-0 z-[9998] bg-black/50 backdrop-blur-sm', content: 'fixed z-[9999] w-full max-w-3xl' }"
        @update:open="(value) => emit('update:open', value)"
    >
        <template #body>
            <div v-if="detailMember" class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <UBadge :color="memberState(detailMember).color" variant="soft" class="rounded-2xl">
                        <UIcon :name="memberState(detailMember).icon" class="mr-1 size-3.5" />
                        {{ memberState(detailMember).text }}
                    </UBadge>

                    <UBadge :color="getPositionBadge(detailMember.position).color" :variant="getPositionBadge(detailMember.position).variant" class="rounded-2xl">
                        {{ getPositionBadge(detailMember.position).text }}
                    </UBadge>

                    <UBadge v-if="detailMember.level" color="neutral" variant="subtle" class="rounded-2xl">
                        Level {{ detailMember.level }}
                    </UBadge>

                    <UBadge v-if="detailMember.package_name" color="primary" variant="subtle" class="rounded-2xl">
                        <UIcon name="i-lucide-badge" class="mr-1 size-3.5" />
                        {{ detailMember.package_name }}
                    </UBadge>

                    <UBadge v-if="detailMember.has_purchase" color="primary" variant="soft" class="rounded-2xl">
                        <UIcon name="i-lucide-shopping-bag" class="mr-1 size-3.5" />
                        Sudah Belanja
                    </UBadge>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <UCard class="rounded-2xl" :ui="{ root: 'border border-default bg-elevated/20', body: 'p-3' }">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Identitas</p>
                        <div class="mt-2 space-y-1.5 text-sm">
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Nama</span>
                                <span class="font-semibold text-highlighted">{{ detailMember.name }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Username</span>
                                <span class="font-semibold text-highlighted">@{{ detailMember.username }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Bergabung</span>
                                <span class="font-semibold text-highlighted">{{ formatDate(detailMember.joined_at) }}</span>
                            </p>
                        </div>
                    </UCard>

                    <UCard class="rounded-2xl" :ui="{ root: 'border border-default bg-elevated/20', body: 'p-3' }">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Kontak</p>
                        <div class="mt-2 space-y-1.5 text-sm">
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Email</span>
                                <span class="font-semibold text-highlighted truncate">{{ detailMember.email }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-muted">Telepon</span>
                                <span class="font-semibold text-highlighted">{{ detailMember.phone ?? '-' }}</span>
                            </p>
                        </div>
                    </UCard>
                </div>

                <UCard class="rounded-2xl" :ui="{ root: 'border border-default bg-elevated/20', body: 'p-3' }">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Ringkasan</p>
                        <p class="text-sm font-black tabular-nums text-primary">{{ formatCurrency(detailMember.omzet ?? 0) }}</p>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                        <div class="rounded-xl border border-default bg-elevated/10 px-3 py-2">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-muted">Downline kiri</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums text-highlighted">{{ detailMember.total_left ?? 0 }}</p>
                        </div>
                        <div class="rounded-xl border border-default bg-elevated/10 px-3 py-2">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-muted">Downline kanan</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums text-highlighted">{{ detailMember.total_right ?? 0 }}</p>
                        </div>
                        <div class="rounded-xl border border-default bg-elevated/10 px-3 py-2">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-muted">Posisi</p>
                            <p class="mt-0.5 text-sm font-bold text-highlighted">
                                {{ detailMember.position ? (detailMember.position === 'left' ? 'Kiri' : 'Kanan') : 'Belum' }}
                            </p>
                        </div>
                    </div>
                </UCard>
            </div>
        </template>

        <template #footer>
            <div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <UButton color="neutral" variant="outline" class="rounded-xl" @click="closeModal">
                    Tutup
                </UButton>

                <UButton
                    v-if="detailMember && activeTab === 'passive' && (!hasLeft || !hasRight)"
                    color="primary"
                    variant="soft"
                    class="rounded-xl"
                    icon="i-lucide-git-branch"
                    @click="placeMember"
                >
                    Tempatkan Member
                </UButton>
            </div>
        </template>
    </UModal>
</template>
