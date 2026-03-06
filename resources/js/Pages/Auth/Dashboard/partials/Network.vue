<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
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
        networkTreeRootId?: number | null
    }>(),
    {
        binaryTree: null,
        networkTreeStats: null,
        passiveMembers: () => [],
        currentCustomerId: null,
        networkTreeRootId: null,
    }
)

const binaryTree = computed(() => props.binaryTree ?? null)
const networkTreeStats = computed(() => props.networkTreeStats ?? null)
const passiveMembers = computed(() => props.passiveMembers ?? [])
const currentCustomerId = computed(() => props.currentCustomerId ?? null)
const networkTreeRootId = computed(() => props.networkTreeRootId ?? null)

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
    toggleNode,
    expandAll,
    collapseAll,
    handleZoomIn,
    handleZoomOut,
    handleResetZoom,
    handleTreeSearchInput,
    selectTreeSearchResult,
    handleTreeSearchBlur,
} = useNetworkTree(binaryTree, networkTreeStats, currentCustomerId, networkTreeRootId)

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

function visitNetworkTree(memberId: number | null): void {
    const authCustomerId = currentCustomerId.value
    const normalizedTreeRootId =
        memberId !== null && authCustomerId !== null && memberId !== authCustomerId
            ? memberId
            : null

    if (normalizedTreeRootId === networkTreeRootId.value) {
        return
    }

    router.get('/dashboard', {
        section: 'network',
        ...(normalizedTreeRootId !== null ? { network_root_id: normalizedTreeRootId } : {}),
    }, {
        only: ['currentCustomerId', 'passiveMembers', 'binaryTree', 'networkTreeStats', 'networkTreeRootId'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

function backToDefaultTree(): void {
    visitNetworkTree(null)
}

function focusToMember(memberId: number): void {
    visitNetworkTree(memberId)
}

function handleSelectSearchResult(memberId: number): void {
    selectTreeSearchResult(memberId)
    focusToMember(memberId)
}

function updatePlacementModalOpen(value: boolean): void {
    showPlacementDialog.value = value
}

function updatePlacementSearchQuery(value: string): void {
    memberSearchQuery.value = value
}
</script>

<template>
    <div class="space-y-3 sm:space-y-4">
        <NetworkStatsCards :stats="currentStats" />

        <UCard class="overflow-hidden rounded-2xl">
            <template #header>
                <NetworkHeaderControls
                    :is-viewing-member-tree="isViewingMemberTree"
                    :selected-member-for-tree="selectedMemberForTree"
                    :max-loaded-level="maxLoadedLevel"
                    :zoom="zoom"
                    :tree-search-query="treeSearchQuery"
                    :show-tree-search-results="showTreeSearchResults"
                    :tree-search-results="treeSearchResults"
                    @back="backToDefaultTree"
                    @update:tree-search-query="updateTreeSearchQuery"
                    @search-input="handleTreeSearchInput"
                    @search-focus="handleTreeSearchInput"
                    @search-blur="handleTreeSearchBlur"
                    @select-search-result="handleSelectSearchResult"
                    @expand-all="expandAll"
                    @collapse-all="collapseAll"
                    @zoom-out="handleZoomOut"
                    @zoom-in="handleZoomIn"
                    @reset-zoom="handleResetZoom"
                />
            </template>

            <div class="space-y-3">
                <NetworkTreePanel
                    :current-tree="currentTree"
                    :zoom="zoom"
                    :collapsed-ids="collapsedIds"
                    :max-depth="5"
                    @member-click="focusToMember"
                    @open-placement="openPlacementDialog"
                    @toggle-expand="toggleNode"
                />

                <div class="rounded-xl border border-default bg-elevated/20 px-3 py-2">
                    <div class="flex flex-col gap-1 text-[11px] text-muted sm:flex-row sm:items-center sm:justify-between">
                        <p>Klik node member untuk fokus subtree.</p>
                        <p>Placement hanya untuk member pasif.</p>
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
