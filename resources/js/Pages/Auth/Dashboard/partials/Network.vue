<script setup lang="ts">
import { computed } from 'vue'
import type { DashboardMitraMember, DashboardNetworkTreeNode, DashboardNetworkTreeStats } from '@/types/dashboard'
import { useNetworkPlacement } from '@/composables/useNetworkPlacement'
import { useNetworkTree } from '@/composables/useNetworkTree'
import NetworkHeaderControls from '@/components/dashboard/network/NetworkHeaderControls.vue'
import NetworkPlacementModal from '@/components/dashboard/network/NetworkPlacementModal.vue'
import NetworkStatsCards from '@/components/dashboard/network/NetworkStatsCards.vue'
import NetworkTreePanel from '@/components/dashboard/network/NetworkTreePanel.vue'

const props = withDefaults(
    defineProps<{
        binaryTree?: DashboardNetworkTreeNode | null
        networkTreeStats?: DashboardNetworkTreeStats | null
        passiveMembers?: DashboardMitraMember[]
        currentCustomerId?: number | null
    }>(),
    {
        binaryTree: null,
        networkTreeStats: null,
        passiveMembers: () => [],
        currentCustomerId: null,
    }
)

const binaryTree = computed(() => props.binaryTree ?? null)
const networkTreeStats = computed(() => props.networkTreeStats ?? null)
const passiveMembers = computed(() => props.passiveMembers ?? [])
const currentCustomerId = computed(() => props.currentCustomerId ?? null)

const {
    currentStats,
    isViewingMemberTree,
    selectedMemberForTree,
    maxLoadedLevel,
    treeSearchQuery,
    showTreeSearchResults,
    treeSearchResults,
    currentTree,
    zoom,
    collapsedIds,
    backToDefaultTree,
    focusToMember,
    toggleNode,
    expandAll,
    collapseAll,
    handleZoomIn,
    handleZoomOut,
    handleResetZoom,
    handleTreeSearchInput,
    selectTreeSearchResult,
    handleTreeSearchBlur,
} = useNetworkTree(binaryTree, networkTreeStats)

const {
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
} = useNetworkPlacement(passiveMembers, currentCustomerId)

function updateTreeSearchQuery(value: string): void {
    treeSearchQuery.value = value
}

function updatePlacementModalOpen(value: boolean): void {
    showPlacementDialog.value = value
}

function updatePlacementSearchQuery(value: string): void {
    memberSearchQuery.value = value
}
</script>

<template>
    <div class="space-y-4 sm:space-y-6">
        <NetworkStatsCards :stats="currentStats" />

        <UCard class="overflow-hidden rounded-3xl">
            <template #header>
                <NetworkHeaderControls
                    :is-viewing-member-tree="isViewingMemberTree"
                    :selected-member-for-tree="selectedMemberForTree"
                    :max-loaded-level="maxLoadedLevel"
                    :tree-search-query="treeSearchQuery"
                    :show-tree-search-results="showTreeSearchResults"
                    :tree-search-results="treeSearchResults"
                    @back="backToDefaultTree"
                    @update:tree-search-query="updateTreeSearchQuery"
                    @search-input="handleTreeSearchInput"
                    @search-focus="handleTreeSearchInput"
                    @search-blur="handleTreeSearchBlur"
                    @select-search-result="selectTreeSearchResult"
                    @expand-all="expandAll"
                    @collapse-all="collapseAll"
                    @zoom-out="handleZoomOut"
                    @zoom-in="handleZoomIn"
                    @reset-zoom="handleResetZoom"
                />
            </template>

            <div class="space-y-4 p-4 sm:p-6">
                <NetworkTreePanel
                    :current-tree="currentTree"
                    :zoom="zoom"
                    :collapsed-ids="collapsedIds"
                    :max-depth="6"
                    @member-click="focusToMember"
                    @open-placement="openPlacementDialog"
                    @toggle-expand="toggleNode"
                />

                <div class="rounded-2xl border border-default bg-elevated/20 p-3">
                    <div class="flex flex-col gap-2 text-xs text-muted sm:flex-row sm:items-center sm:justify-between">
                        <p>Klik node member untuk fokus subtree dan gunakan tombol expand/collapse untuk organization tree.</p>
                        <p>Placement hanya menampilkan member pasif yang belum ditempatkan.</p>
                        <p>Zoom saat ini: {{ Math.round(zoom * 100) }}%</p>
                    </div>
                </div>
            </div>
        </UCard>
    </div>

    <NetworkPlacementModal
        :open="showPlacementDialog"
        :selected-position="selectedPosition"
        :selected-upline-id="selectedUplineId"
        :member-search-query="memberSearchQuery"
        :filtered-passive-members="filteredPassiveMembers"
        :selected-member-id="selectedMember?.id ?? null"
        :processing="placementForm.processing"
        @update:open="updatePlacementModalOpen"
        @update:member-search-query="updatePlacementSearchQuery"
        @select-member="selectMember"
        @submit="placeMemberToBinaryTree"
        @cancel="closePlacementDialog"
    />
</template>
