<script setup lang="ts">
import { computed } from 'vue'
import type { DashboardNetworkTreeNode } from '@/types/dashboard'

defineOptions({
    name: 'NetworkTreeNode',
})

const props = withDefaults(
    defineProps<{
        node: DashboardNetworkTreeNode
        depth?: number
        maxDepth?: number
        collapsedIds?: number[]
        allowPlacement?: boolean
    }>(),
    {
        depth: 1,
        maxDepth: 5,
        collapsedIds: () => [],
        allowPlacement: true,
    }
)

const emit = defineEmits<{
    memberClick: [memberId: number]
    openPlacement: [payload: { uplineId: number; position: 'left' | 'right' }]
    toggleExpand: [memberId: number]
}>()

const canRenderChildren = computed(() => props.depth < props.maxDepth)
const hasChildNode = computed(() => Boolean(props.node.left || props.node.right || props.node.has_children))
const isCollapsed = computed(() => props.collapsedIds.includes(props.node.id))
const showChildren = computed(() => canRenderChildren.value && hasChildNode.value && !isCollapsed.value)
const packageLabel = computed(() => props.node.package_name ?? 'Member')
const levelLabel = computed(() => `L${props.node.level}`)
const isRightNode = computed(() => props.node.position === 'right')
const packageBadgeClass = computed(() =>
    isRightNode.value
        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30'
        : 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-300 dark:ring-blue-500/30'
)

function getSlotLabel(position: 'left' | 'right'): string {
    return position === 'left' ? '+ Kiri' : '+ Kanan'
}

function getSlotCardClass(position: 'left' | 'right'): string {
    return position === 'right'
        ? 'border-emerald-300/80 text-emerald-700 hover:bg-emerald-50/70 dark:border-emerald-500/35 dark:text-emerald-300 dark:hover:bg-emerald-500/10'
        : 'border-blue-300/80 text-blue-700 hover:bg-blue-50/70 dark:border-blue-500/35 dark:text-blue-300 dark:hover:bg-blue-500/10'
}
</script>

<template>
    <div class="flex flex-col items-center">
        <div class="relative space-y-1 text-center sm:space-y-1.5">
            <div class="absolute right-0 top-0 flex items-center gap-0.5">
                <UButton v-if="hasChildNode" size="xs" color="neutral" variant="ghost"
                    :icon="isCollapsed ? 'i-lucide-plus' : 'i-lucide-minus'" class="rounded-md"
                    @click="emit('toggleExpand', node.id)" />
                <UButton size="xs" color="neutral" variant="ghost" icon="i-lucide-scan-search" class="rounded-md"
                    @click="emit('memberClick', node.id)" />
            </div>

            <div class="space-y-0.5 pr-10">
                <p class="truncate text-[11px] font-semibold text-highlighted sm:text-sm">{{ node.name }}</p>
                <p class="truncate text-[10px] leading-tight text-muted sm:text-xs">{{ node.username }}</p>
                <p class="text-[10px] text-muted sm:text-xs">{{ levelLabel }}</p>
            </div>

            <div class="pt-0.5">
                <span
                    class="inline-flex max-w-full items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-medium sm:text-[11px]"
                    :class="packageBadgeClass">
                    <span class="truncate">{{ packageLabel }}</span>
                </span>
            </div>

            <div class="flex items-center justify-center gap-2.5 text-[10px] text-muted sm:text-[11px]">
                <span class="inline-flex items-center gap-1">
                    <span class="size-1.5 rounded-full bg-blue-500" />
                    L: {{ node.total_left }}
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="size-1.5 rounded-full bg-emerald-500" />
                    R: {{ node.total_right }}
                </span>
            </div>
        </div>

        <div v-if="showChildren" class="mt-2 w-full sm:mt-4">
            <div class="mx-auto h-2.5 w-px bg-gray-300 dark:bg-gray-700 sm:h-4" />

            <div class="relative grid grid-cols-2 gap-1 px-0 sm:gap-5 sm:px-2">
                <div
                    class="absolute left-[30%] right-[30%] top-0 h-px bg-gray-300 dark:bg-gray-700 sm:left-[26%] sm:right-[26%]" />

                <div class="relative flex justify-center pt-2.5 sm:pt-4">
                    <div
                        class="absolute left-[58%] top-0 h-2.5 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700 sm:left-[55%] sm:h-4" />

                    <NetworkTreeNode v-if="node.left" :node="node.left" :depth="depth + 1" :max-depth="maxDepth"
                        :collapsed-ids="collapsedIds" :allow-placement="allowPlacement"
                        @member-click="emit('memberClick', $event)" @open-placement="emit('openPlacement', $event)"
                        @toggle-expand="emit('toggleExpand', $event)" />

                    <UCard v-else-if="allowPlacement"
                        class="w-[7.7rem] rounded-xl border border-dashed bg-elevated/35 sm:w-44 sm:rounded-2xl"
                        :class="getSlotCardClass('left')" :ui="{ body: 'p-0' }">
                        <button type="button"
                            class="flex w-full items-center justify-center px-2 py-3 text-[11px] font-medium transition-colors sm:py-4 sm:text-sm"
                            @click="emit('openPlacement', { uplineId: node.id, position: 'left' })">
                            {{ getSlotLabel('left') }}
                        </button>
                    </UCard>
                </div>

                <div class="relative flex justify-center pt-2.5 sm:pt-4">
                    <div
                        class="absolute left-[42%] top-0 h-2.5 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700 sm:left-[45%] sm:h-4" />

                    <NetworkTreeNode v-if="node.right" :node="node.right" :depth="depth + 1" :max-depth="maxDepth"
                        :collapsed-ids="collapsedIds" :allow-placement="allowPlacement"
                        @member-click="emit('memberClick', $event)" @open-placement="emit('openPlacement', $event)"
                        @toggle-expand="emit('toggleExpand', $event)" />

                    <UCard v-else-if="allowPlacement"
                        class="w-[7.7rem] rounded-xl border border-dashed bg-elevated/35 sm:w-44 sm:rounded-2xl"
                        :class="getSlotCardClass('right')" :ui="{ body: 'p-0' }">
                        <button type="button"
                            class="flex w-full items-center justify-center px-2 py-3 text-[11px] font-medium transition-colors sm:py-4 sm:text-sm"
                            @click="emit('openPlacement', { uplineId: node.id, position: 'right' })">
                            {{ getSlotLabel('right') }}
                        </button>
                    </UCard>
                </div>
            </div>
        </div>
    </div>
</template>
