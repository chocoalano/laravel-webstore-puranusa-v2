<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import NetworkTreeNode from '@/components/dashboard/network/NetworkTreeNode.vue'
import type { DashboardNetworkTreeNode } from '@/types/dashboard'

type PlacementPosition = 'left' | 'right'
type NodeSide = 'root' | 'left' | 'right'

type GoTreeNodeData = {
    key: string
    category: 'member' | 'placeholder'
    memberId: number | null
    uplineId: number | null
    position: PlacementPosition | null
    side: NodeSide
    name: string
    username: string
    packageName: string
    levelLabel: string
    totalLeft: number
    totalRight: number
    hasChildNode: boolean
    isCollapsed: boolean
    label: string
}

type GoTreeLinkData = {
    from: string
    to: string
}

declare global {
    interface Window {
        go?: any
    }
}

const props = withDefaults(
    defineProps<{
        node: DashboardNetworkTreeNode
        collapsedIds?: number[]
        maxDepth?: number
        allowPlacement?: boolean
        zoom?: number
    }>(),
    {
        collapsedIds: () => [],
        maxDepth: 5,
        allowPlacement: true,
        zoom: 1,
    }
)

const emit = defineEmits<{
    memberClick: [memberId: number]
    openPlacement: [payload: { uplineId: number; position: PlacementPosition }]
    toggleExpand: [memberId: number]
}>()

const diagramContainer = ref<HTMLDivElement | null>(null)
const loadError = ref<string | null>(null)

let goLib: any = null
let diagram: any = null
let hasUnmounted = false
let goLoadPromise: Promise<any> | null = null

function getNodeBorderColor(side: NodeSide): string {
    return side === 'right' ? '#22c55e' : '#3b82f6'
}

function getPackageFillColor(side: NodeSide): string {
    return side === 'right' ? '#ecfdf5' : '#eff6ff'
}

function getPackageBorderColor(side: NodeSide): string {
    return side === 'right' ? '#86efac' : '#93c5fd'
}

function getPackageTextColor(side: NodeSide): string {
    return side === 'right' ? '#15803d' : '#1d4ed8'
}

function loadGoJs(): Promise<any> {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('GoJS hanya tersedia di browser.'))
    }

    if (window.go) {
        return Promise.resolve(window.go)
    }

    if (!goLoadPromise) {
        goLoadPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script')
            script.src = 'https://unpkg.com/gojs/release/go.js'
            script.async = true
            script.onload = () => {
                if (window.go) {
                    resolve(window.go)
                    return
                }

                reject(new Error('GoJS gagal dimuat dari CDN.'))
            }
            script.onerror = () => reject(new Error('Gagal memuat GoJS dari CDN.'))
            document.head.appendChild(script)
        })
    }

    return goLoadPromise
}

function toGraphData(rootNode: DashboardNetworkTreeNode): { nodes: GoTreeNodeData[]; links: GoTreeLinkData[] } {
    const nodes: GoTreeNodeData[] = []
    const links: GoTreeLinkData[] = []
    const collapsedIdSet = new Set(props.collapsedIds)

    const traverse = (
        node: DashboardNetworkTreeNode,
        parentKey: string | null,
        side: NodeSide,
        depth: number
    ): void => {
        const key = `member-${node.id}`
        const isCollapsed = collapsedIdSet.has(node.id)
        const hasChildNode = Boolean(node.left || node.right || node.has_children)

        nodes.push({
            key,
            category: 'member',
            memberId: node.id,
            uplineId: null,
            position: null,
            side,
            name: node.name,
            username: node.username,
            packageName: node.package_name ?? 'Member',
            levelLabel: `L${node.level}`,
            totalLeft: node.total_left,
            totalRight: node.total_right,
            hasChildNode,
            isCollapsed,
            label: '',
        })

        if (parentKey) {
            links.push({ from: parentKey, to: key })
        }

        if (depth >= props.maxDepth || isCollapsed) {
            return
        }

        const appendChild = (position: PlacementPosition): void => {
            const child = position === 'left' ? node.left : node.right

            if (child) {
                traverse(child, key, position, depth + 1)
                return
            }

            if (!props.allowPlacement) {
                return
            }

            const slotKey = `slot-${node.id}-${position}`
            const isRight = position === 'right'

            nodes.push({
                key: slotKey,
                category: 'placeholder',
                memberId: null,
                uplineId: node.id,
                position,
                side: isRight ? 'right' : 'left',
                name: '',
                username: '',
                packageName: '',
                levelLabel: '',
                totalLeft: 0,
                totalRight: 0,
                hasChildNode: false,
                isCollapsed: false,
                label: isRight ? '+ Kanan' : '+ Kiri',
            })

            links.push({ from: key, to: slotKey })
        }

        appendChild('left')
        appendChild('right')
    }

    traverse(rootNode, null, 'root', 1)

    return { nodes, links }
}

function initDiagram(go: any): void {
    if (!diagramContainer.value) {
        return
    }

    const $ = go.GraphObject.make

    diagram = $(
        go.Diagram,
        diagramContainer.value,
        {
            isReadOnly: true,
            allowMove: false,
            allowCopy: false,
            allowDelete: false,
            allowInsert: false,
            allowSelect: false,
            allowHorizontalScroll: true,
            allowVerticalScroll: true,
            minScale: 0.12,
            maxScale: 1.8,
            'animationManager.isEnabled': false,
            contentAlignment: go.Spot.TopCenter,
            padding: 6,
            layout: $(
                go.TreeLayout,
                {
                    angle: 90,
                    layerSpacing: 14,
                    nodeSpacing: 10,
                    compaction: go.TreeCompaction.Block,
                    arrangement: go.TreeArrangement.FixedRoots,
                }
            ),
        }
    )

    diagram.toolManager.mouseWheelBehavior = go.ToolManager.WheelZoom

    diagram.linkTemplate = $(
        go.Link,
        {
            routing: go.Routing.Orthogonal,
            corner: 6,
            selectable: false,
            layerName: 'Background',
        },
        $(go.Shape, { stroke: '#cbd5e1', strokeWidth: 1.1 })
    )

    diagram.nodeTemplateMap.add(
        'member',
        $(
            go.Node,
            'Spot',
            {
                cursor: 'pointer',
                click: (_event: unknown, obj: any) => {
                    const memberId = obj?.data?.memberId

                    if (typeof memberId === 'number') {
                        emit('memberClick', memberId)
                    }
                },
            },
                $(
                    go.Panel,
                    'Auto',
                    $(go.Shape, 'RoundedRectangle', {
                    parameter1: 9,
                    fill: '#ffffff',
                    strokeWidth: 2,
                    width: 120,
                    minSize: new go.Size(120, 84),
                }, new go.Binding('stroke', 'side', getNodeBorderColor)),
                $(
                    go.Panel,
                    'Vertical',
                    {
                        margin: 5,
                        width: 108,
                        defaultAlignment: go.Spot.Center,
                    },
                    $(
                        go.TextBlock,
                        {
                            width: 106,
                            maxLines: 1,
                            overflow: go.TextOverflow.Ellipsis,
                            textAlign: 'center',
                            font: '600 10px Inter, system-ui, sans-serif',
                            stroke: '#0f172a',
                        },
                        new go.Binding('text', 'name')
                    ),
                    $(
                        go.TextBlock,
                        {
                            width: 106,
                            maxLines: 1,
                            overflow: go.TextOverflow.Ellipsis,
                            textAlign: 'center',
                            margin: new go.Margin(1, 0, 0, 0),
                            font: '9px Inter, system-ui, sans-serif',
                            stroke: '#64748b',
                        },
                        new go.Binding('text', 'username')
                    ),
                    $(
                        go.TextBlock,
                        {
                            margin: new go.Margin(0, 0, 3, 0),
                            font: '9px Inter, system-ui, sans-serif',
                            stroke: '#94a3b8',
                        },
                        new go.Binding('text', 'levelLabel')
                    ),
                    $(
                        go.Panel,
                        'Auto',
                        { margin: new go.Margin(0, 0, 3, 0) },
                        $(go.Shape, 'RoundedRectangle', {
                            parameter1: 6,
                            strokeWidth: 1,
                        }, new go.Binding('fill', 'side', getPackageFillColor), new go.Binding('stroke', 'side', getPackageBorderColor)),
                        $(
                            go.TextBlock,
                            {
                                width: 84,
                                maxLines: 1,
                                overflow: go.TextOverflow.Ellipsis,
                                textAlign: 'center',
                                margin: new go.Margin(2, 6, 2, 6),
                                font: '500 9px Inter, system-ui, sans-serif',
                            },
                            new go.Binding('stroke', 'side', getPackageTextColor),
                            new go.Binding('text', 'packageName')
                        )
                    ),
                    $(
                        go.Panel,
                        'Horizontal',
                        { defaultAlignment: go.Spot.Center },
                        $(go.Shape, 'Circle', { desiredSize: new go.Size(5, 5), fill: '#3b82f6', strokeWidth: 0 }),
                        $(
                            go.TextBlock,
                            {
                                margin: new go.Margin(0, 3, 0, 2),
                                font: '9px Inter, system-ui, sans-serif',
                                stroke: '#64748b',
                            },
                            new go.Binding('text', 'totalLeft', (value: number) => `L: ${value}`)
                        ),
                        $(go.Shape, 'Circle', { desiredSize: new go.Size(5, 5), fill: '#22c55e', strokeWidth: 0 }),
                        $(
                            go.TextBlock,
                            {
                                margin: new go.Margin(0, 0, 0, 2),
                                font: '9px Inter, system-ui, sans-serif',
                                stroke: '#64748b',
                            },
                            new go.Binding('text', 'totalRight', (value: number) => `R: ${value}`)
                        )
                    )
                )
            ),
            $(
                go.Panel,
                'Auto',
                {
                    alignment: new go.Spot(1, 0, -4, 4),
                    cursor: 'pointer',
                    click: (event: any, obj: any) => {
                        event.handled = true

                        const memberId = obj?.part?.data?.memberId

                        if (typeof memberId === 'number') {
                            emit('toggleExpand', memberId)
                        }
                    },
                },
                $(go.Shape, 'RoundedRectangle', {
                    parameter1: 5,
                    fill: '#f8fafc',
                    stroke: '#cbd5e1',
                    strokeWidth: 1,
                }),
                $(
                    go.TextBlock,
                    {
                        margin: 1,
                        font: '700 9px Inter, system-ui, sans-serif',
                        stroke: '#334155',
                    },
                    new go.Binding('text', 'isCollapsed', (collapsed: boolean) => (collapsed ? '+' : '−'))
                ),
                new go.Binding('visible', 'hasChildNode')
            )
        )
    )

    diagram.nodeTemplateMap.add(
        'placeholder',
        $(
            go.Node,
            'Auto',
            {
                cursor: 'pointer',
                click: (_event: unknown, obj: any) => {
                    const uplineId = obj?.data?.uplineId
                    const position = obj?.data?.position

                    if (typeof uplineId === 'number' && (position === 'left' || position === 'right')) {
                        emit('openPlacement', { uplineId, position })
                    }
                },
            },
            $(go.Shape, 'RoundedRectangle', {
                parameter1: 6,
                fill: '#f8fafc',
                strokeWidth: 1.2,
                strokeDashArray: [3, 3],
                width: 92,
                minSize: new go.Size(92, 30),
            }, new go.Binding('stroke', 'side', getPackageBorderColor)),
            $(
                go.TextBlock,
                {
                    margin: new go.Margin(6, 8, 6, 8),
                    textAlign: 'center',
                    font: '500 10px Inter, system-ui, sans-serif',
                },
                new go.Binding('stroke', 'side', getPackageTextColor),
                new go.Binding('text', 'label')
            )
        )
    )

    diagram.model = new go.GraphLinksModel([], [])
}

function renderModel(): void {
    if (!diagram || !goLib) {
        return
    }

    const graphData = toGraphData(props.node)
    diagram.model = new goLib.GraphLinksModel(graphData.nodes, graphData.links)
    diagram.scale = props.zoom
    diagram.requestUpdate()
}

onMounted(async () => {
    try {
        goLib = await loadGoJs()

        if (hasUnmounted) {
            return
        }

        await nextTick()

        initDiagram(goLib)
        renderModel()
    } catch (error) {
        loadError.value = error instanceof Error ? error.message : 'GoJS gagal dimuat.'
    }
})

onBeforeUnmount(() => {
    hasUnmounted = true

    if (diagram) {
        diagram.clear()
        diagram.div = null
        diagram = null
    }
})

watch(
    () => [props.node, props.collapsedIds, props.maxDepth, props.allowPlacement],
    () => {
        renderModel()
    }
)

watch(
    () => props.zoom,
    (nextZoom) => {
        if (!diagram) {
            return
        }

        diagram.scale = nextZoom
    }
)
</script>

<template>
    <div class="w-full">
        <div
            v-if="loadError"
            class="rounded-xl border border-warning/40 bg-warning/10 p-3 text-xs text-muted"
        >
            <p class="font-medium text-warning">GoJS tidak bisa dimuat. Menampilkan fallback tree.</p>
            <p class="mt-1">{{ loadError }}</p>
        </div>

        <div v-if="loadError" class="overflow-x-auto">
            <div class="mx-auto w-fit px-1.5 py-2">
                <NetworkTreeNode
                    :node="node"
                    :max-depth="maxDepth"
                    :collapsed-ids="collapsedIds"
                    :allow-placement="allowPlacement"
                    @member-click="emit('memberClick', $event)"
                    @open-placement="emit('openPlacement', $event)"
                    @toggle-expand="emit('toggleExpand', $event)"
                />
            </div>
        </div>

        <div
            v-show="!loadError"
            ref="diagramContainer"
            class="h-[56vh] min-h-85 w-full rounded-xl border border-default/70 bg-default"
        />
    </div>
</template>
