<script setup lang="ts">
import { computed } from 'vue'
import type { DashboardMitraMember } from '@/types/dashboard'
import { useDashboardMitra } from '@/composables/useDashboardMitra'
import MitraHeaderPanel from '@/components/dashboard/mitra/MitraHeaderPanel.vue'
import MitraFilterTabsCard from '@/components/dashboard/mitra/MitraFilterTabsCard.vue'
import MitraMembersList from '@/components/dashboard/mitra/MitraMembersList.vue'
import MitraDetailModal from '@/components/dashboard/mitra/MitraDetailModal.vue'
import MitraPlacementModal from '@/components/dashboard/mitra/MitraPlacementModal.vue'

type TabKey = 'active' | 'passive' | 'prospect'

const props = withDefaults(
    defineProps<{
        activeMembers?: DashboardMitraMember[]
        passiveMembers?: DashboardMitraMember[]
        prospectMembers?: DashboardMitraMember[]
        hasLeft?: boolean
        hasRight?: boolean
        currentCustomerId?: number | null
    }>(),
    {
        activeMembers: () => [],
        passiveMembers: () => [],
        prospectMembers: () => [],
        hasLeft: false,
        hasRight: false,
        currentCustomerId: null,
    }
)

const {
    activeTab,
    q,
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
} = useDashboardMitra({
    activeMembers: computed(() => props.activeMembers),
    passiveMembers: computed(() => props.passiveMembers),
    prospectMembers: computed(() => props.prospectMembers),
    hasLeft: computed(() => props.hasLeft),
    hasRight: computed(() => props.hasRight),
    currentCustomerId: computed(() => props.currentCustomerId),
})

function onSelectedPositionChange(value: 'left' | 'right' | null): void {
    selectedPosition.value = value
}

function onActiveTabChange(value: TabKey): void {
    activeTab.value = value
}

function onSearchQueryChange(value: string): void {
    q.value = value
}
</script>

<template>
    <UCard class="rounded-3xl overflow-hidden">
        <template #header>
            <MitraHeaderPanel
                :total-members="totalMembers"
                :active-tab="activeTab"
                :tab-badge="tabStatusBadge(activeTab)"
            />
        </template>

        <div class="space-y-4">
            <MitraFilterTabsCard
                :tabs="tabs"
                :active-tab="activeTab"
                :hint-text="hintText"
                :search-query="q"
                @update:active-tab="onActiveTabChange"
                @update:search-query="onSearchQueryChange"
            />

            <MitraMembersList
                :members="filteredMembers"
                :active-tab="activeTab"
                :has-left="hasLeft"
                :has-right="hasRight"
                :format-date="formatDate"
                :format-currency="formatCurrency"
                :get-position-badge="getPositionBadge"
                :tab-status-badge="tabStatusBadge"
                @open-detail="openDetail"
                @place-member="openPlacementDialog"
            />
        </div>
    </UCard>

    <MitraDetailModal
        v-model:open="isDetailOpen"
        :detail-member="detailMember"
        :active-tab="activeTab"
        :has-left="hasLeft"
        :has-right="hasRight"
        :format-date="formatDate"
        :format-currency="formatCurrency"
        :get-position-badge="getPositionBadge"
        :tab-status-badge="tabStatusBadge"
        :member-state="memberState"
        @close="closeDetail"
        @place-member="openPlacementFromDetail"
    />

    <MitraPlacementModal
        v-model:open="showPlacementDialog"
        :selected-member="selectedMember"
        :selected-position="selectedPosition"
        :has-left="hasLeft"
        :has-right="hasRight"
        :processing="placementForm.processing"
        :upline-id="placementForm.upline_id"
        @update:selected-position="onSelectedPositionChange"
        @close="closePlacementDialog"
        @submit="placeToBinaryTree"
    />
</template>
