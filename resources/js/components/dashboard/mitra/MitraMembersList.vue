<script setup lang="ts">
import type { DashboardMitraMember } from '@/types/dashboard'
import MitraMemberCard from '@/components/dashboard/mitra/MitraMemberCard.vue'

type TabKey = 'active' | 'passive' | 'prospect'
type PositionBadge = {
    color: 'neutral' | 'primary' | 'info'
    variant: 'subtle' | 'soft'
    text: string
}
type TabBadge = {
    color: 'success' | 'neutral' | 'warning'
    icon: string
    text: string
}

const props = defineProps<{
    members: DashboardMitraMember[]
    activeTab: TabKey
    hasLeft: boolean
    hasRight: boolean
    formatDate: (value: string | null | undefined) => string
    formatCurrency: (value: number) => string
    getPositionBadge: (position: string | null | undefined) => PositionBadge
    tabStatusBadge: (tab: TabKey) => TabBadge
}>()

const emit = defineEmits<{
    (e: 'openDetail', member: DashboardMitraMember): void
    (e: 'placeMember', member: DashboardMitraMember): void
}>()

function emptyTitle(tab: TabKey): string {
    if (tab === 'active') {
        return 'Belum ada member aktif'
    }

    if (tab === 'passive') {
        return 'Belum ada member pasif'
    }

    return 'Belum ada member prospek'
}

function emptyDescription(tab: TabKey): string {
    if (tab === 'active') {
        return 'Member aktif adalah member yang sudah ditempatkan di binary tree.'
    }

    if (tab === 'passive') {
        return 'Member pasif adalah member yang belum ditempatkan di binary tree tapi sudah memiliki pembelian/order.'
    }

    return 'Member prospek adalah member yang baru mendaftar, belum ditempatkan di binary tree dan belum memiliki pembelian/order.'
}
</script>

<template>
    <UEmpty
        v-if="props.members.length === 0"
        :icon="props.tabStatusBadge(props.activeTab).icon"
        :title="emptyTitle(props.activeTab)"
        :description="emptyDescription(props.activeTab)"
        variant="outline"
        size="lg"
        :ui="{ root: 'rounded-2xl py-12' }"
    />

    <div v-else class="grid gap-3">
        <MitraMemberCard
            v-for="member in props.members"
            :key="member.id"
            :member="member"
            :active-tab="props.activeTab"
            :has-left="props.hasLeft"
            :has-right="props.hasRight"
            :format-date="props.formatDate"
            :format-currency="props.formatCurrency"
            :get-position-badge="props.getPositionBadge"
            :tab-status-badge="props.tabStatusBadge"
            @open-detail="(value) => emit('openDetail', value)"
            @place-member="(value) => emit('placeMember', value)"
        />
    </div>
</template>
