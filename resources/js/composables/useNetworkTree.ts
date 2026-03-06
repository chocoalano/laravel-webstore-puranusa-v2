import { computed, ref, watch, type Ref } from 'vue'
import type { DashboardNetworkTreeNode, DashboardNetworkTreeStats } from '@/types/dashboard'

export type NetworkTreeSearchResult = {
    id: number
    name: string
    username: string
    email: string
    package_name: string
    level: number
}

export type NetworkTreeStatsSummary = {
    totalDownlines: number
    totalLeft: number
    totalRight: number
}

function countNodes(node: DashboardNetworkTreeNode | null): number {
    if (!node) {
        return 0
    }

    return 1 + countNodes(node.left) + countNodes(node.right)
}

function findNodeById(node: DashboardNetworkTreeNode | null, nodeId: number): DashboardNetworkTreeNode | null {
    if (!node) {
        return null
    }

    if (node.id === nodeId) {
        return node
    }

    const leftNode = findNodeById(node.left, nodeId)

    if (leftNode) {
        return leftNode
    }

    return findNodeById(node.right, nodeId)
}

function flattenTree(node: DashboardNetworkTreeNode | null): NetworkTreeSearchResult[] {
    if (!node) {
        return []
    }

    return [
        {
            id: node.id,
            name: node.name,
            username: node.username,
            email: node.email ?? '-',
            package_name: node.package_name ?? 'Member',
            level: node.level,
        },
        ...flattenTree(node.left),
        ...flattenTree(node.right),
    ]
}

function collectNodeIds(node: DashboardNetworkTreeNode | null): number[] {
    if (!node) {
        return []
    }

    return [node.id, ...collectNodeIds(node.left), ...collectNodeIds(node.right)]
}

function resolveDefaultZoom(): number {
    if (typeof window === 'undefined') {
        return 0.8
    }

    return window.matchMedia('(max-width: 640px)').matches ? 0.5 : 0.8
}

export function useNetworkTree(
    binaryTree: Ref<DashboardNetworkTreeNode | null>,
    networkTreeStats: Ref<DashboardNetworkTreeStats | null>,
) {
    const activeRootId = ref<number | null>(null)
    const treeSearchQuery = ref('')
    const showTreeSearchResults = ref(false)

    const defaultZoom = resolveDefaultZoom()
    const zoom = ref(defaultZoom)
    const minZoom = 0.15
    const maxZoom = 1.6
    const zoomStep = 0.08

    const collapsedIds = ref<number[]>([])
    const hasInitializedCollapsedState = ref(false)

    const rootTree = computed(() => binaryTree.value ?? null)

    const currentTree = computed(() => {
        if (!rootTree.value) {
            return null
        }

        if (!activeRootId.value) {
            return rootTree.value
        }

        return findNodeById(rootTree.value, activeRootId.value) ?? rootTree.value
    })

    const isViewingMemberTree = computed(() => activeRootId.value !== null)
    const selectedMemberForTree = computed(() => currentTree.value)

    const allMemberSearchData = computed(() => flattenTree(rootTree.value))

    const maxLoadedLevel = computed(() => {
        const levels = allMemberSearchData.value.map((item) => item.level)

        if (levels.length === 0) {
            return 0
        }

        return Math.max(...levels)
    })

    const currentStats = computed<NetworkTreeStatsSummary>(() => {
        if (!currentTree.value) {
            return {
                totalDownlines: 0,
                totalLeft: 0,
                totalRight: 0,
            }
        }

        if (!isViewingMemberTree.value && networkTreeStats.value) {
            return {
                totalDownlines: networkTreeStats.value.total_downlines,
                totalLeft: networkTreeStats.value.left_count,
                totalRight: networkTreeStats.value.right_count,
            }
        }

        return {
            totalDownlines: Math.max(0, countNodes(currentTree.value) - 1),
            totalLeft: countNodes(currentTree.value.left),
            totalRight: countNodes(currentTree.value.right),
        }
    })

    const treeSearchResults = computed<NetworkTreeSearchResult[]>(() => {
        const query = treeSearchQuery.value.trim().toLowerCase()

        if (query.length < 2) {
            return []
        }

        return allMemberSearchData.value
            .filter((member) => {
                const haystack = `${member.name} ${member.username} ${member.email} ${member.package_name}`.toLowerCase()

                return haystack.includes(query)
            })
            .slice(0, 12)
    })

    function backToDefaultTree(): void {
        activeRootId.value = null
    }

    function focusToMember(memberId: number): void {
        activeRootId.value = memberId
        collapsedIds.value = collapsedIds.value.filter((id) => id !== memberId)
    }

    function toggleNode(memberId: number): void {
        if (collapsedIds.value.includes(memberId)) {
            collapsedIds.value = collapsedIds.value.filter((id) => id !== memberId)

            return
        }

        collapsedIds.value = [...collapsedIds.value, memberId]
    }

    function expandAll(): void {
        collapsedIds.value = []
    }

    function collapseAll(): void {
        if (!currentTree.value) {
            collapsedIds.value = []

            return
        }

        collapsedIds.value = collectNodeIds(currentTree.value).filter((id) => id !== currentTree.value?.id)
    }

    watch(
        currentTree,
        (tree) => {
            if (!tree) {
                collapsedIds.value = []
                hasInitializedCollapsedState.value = false

                return
            }

            if (hasInitializedCollapsedState.value) {
                return
            }

            collapsedIds.value = []
            hasInitializedCollapsedState.value = true
        },
        { immediate: true }
    )

    function handleZoomIn(): void {
        zoom.value = Math.min(maxZoom, Number((zoom.value + zoomStep).toFixed(2)))
    }

    function handleZoomOut(): void {
        zoom.value = Math.max(minZoom, Number((zoom.value - zoomStep).toFixed(2)))
    }

    function handleResetZoom(): void {
        zoom.value = defaultZoom
    }

    function handleTreeSearchInput(): void {
        showTreeSearchResults.value = treeSearchQuery.value.trim().length >= 2
    }

    function selectTreeSearchResult(memberId: number): void {
        treeSearchQuery.value = ''
        showTreeSearchResults.value = false
        focusToMember(memberId)
    }

    function handleTreeSearchBlur(): void {
        window.setTimeout(() => {
            showTreeSearchResults.value = false
        }, 120)
    }

    return {
        activeRootId,
        treeSearchQuery,
        showTreeSearchResults,
        zoom,
        collapsedIds,
        rootTree,
        currentTree,
        isViewingMemberTree,
        selectedMemberForTree,
        allMemberSearchData,
        maxLoadedLevel,
        currentStats,
        treeSearchResults,
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
    }
}
