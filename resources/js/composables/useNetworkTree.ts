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

function normalizeSearchText(value: string): string {
    return value.toLowerCase().replace(/\s+/g, ' ').trim()
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
    currentCustomerId: Ref<number | null>,
    networkTreeRootId: Ref<number | null>,
) {
    const treeSearchQuery = ref('')
    const showTreeSearchResults = ref(false)

    const defaultZoom = resolveDefaultZoom()
    const zoom = ref(defaultZoom)
    const minZoom = 0.15
    const maxZoom = 1.6
    const zoomStep = 0.08

    const collapsedIds = ref<number[]>([])

    const rootTree = computed(() => binaryTree.value ?? null)
    const currentTree = computed(() => rootTree.value)
    const isViewingMemberTree = computed(() => {
        const activeTreeRootId = networkTreeRootId.value
        const authCustomerId = currentCustomerId.value

        if (!activeTreeRootId || !authCustomerId) {
            return false
        }

        return activeTreeRootId !== authCustomerId
    })
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
            totalDownlines: Math.max(0, Number(currentTree.value.total_left ?? 0) + Number(currentTree.value.total_right ?? 0)),
            totalLeft: Math.max(0, Number(currentTree.value.total_left ?? 0)),
            totalRight: Math.max(0, Number(currentTree.value.total_right ?? 0)),
        }
    })

    const treeSearchResults = computed<NetworkTreeSearchResult[]>(() => {
        const rawQuery = normalizeSearchText(treeSearchQuery.value)
        const usernameQuery = rawQuery.replace(/^@+/, '')

        if (usernameQuery.length < 2) {
            return []
        }

        return allMemberSearchData.value
            .filter((member) => {
                const haystack = normalizeSearchText(
                    `${member.name} ${member.username} @${member.username} ${member.email} ${member.package_name}`
                )

                return haystack.includes(rawQuery) || haystack.includes(usernameQuery)
            })
            .slice(0, 12)
    })

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
        (tree, previousTree) => {
            if (!tree) {
                collapsedIds.value = []

                return
            }

            if (previousTree && previousTree.id === tree.id) {
                return
            }

            collapsedIds.value = []
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
        if (memberId <= 0) {
            return
        }

        treeSearchQuery.value = ''
        showTreeSearchResults.value = false
    }

    function handleTreeSearchBlur(): void {
        window.setTimeout(() => {
            showTreeSearchResults.value = false
        }, 120)
    }

    return {
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
