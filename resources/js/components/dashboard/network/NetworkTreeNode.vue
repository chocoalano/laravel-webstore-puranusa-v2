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
</script>

<template>
    <div class="flex flex-col items-center">
        <UCard class="w-40 max-w-[calc(100vw-3rem)] rounded-xl border border-default sm:w-60 sm:max-w-none sm:rounded-2xl lg:w-65">
            <div class="space-y-1 sm:space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-highlighted sm:text-sm">{{ node.name }}</p>
                        <p class="truncate text-[10px] text-muted sm:text-[11px]">@{{ node.username }}</p>
                    </div>

                    <div class="flex items-center gap-1">
                        <UButton
                            v-if="hasChildNode"
                            size="xs"
                            color="neutral"
                            variant="ghost"
                            :icon="isCollapsed ? 'i-lucide-plus' : 'i-lucide-minus'"
                            class="rounded-lg"
                            @click="emit('toggleExpand', node.id)"
                        />
                        <UButton
                            size="xs"
                            color="neutral"
                            variant="ghost"
                            icon="i-lucide-scan-search"
                            class="rounded-lg"
                            @click="emit('memberClick', node.id)"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap gap-0.5 sm:gap-1">
                    <UBadge color="neutral" variant="subtle" size="xs" class="rounded-full">
                        {{ packageLabel }}
                    </UBadge>
                    <UBadge color="primary" variant="subtle" size="xs" class="rounded-full">
                        {{ levelLabel }}
                    </UBadge>
                    <UBadge color="success" variant="subtle" size="xs" class="rounded-full">
                        L {{ node.total_left }}
                    </UBadge>
                    <UBadge color="info" variant="subtle" size="xs" class="rounded-full">
                        R {{ node.total_right }}
                    </UBadge>
                </div>
            </div>
        </UCard>

        <div
            v-if="showChildren"
            class="mt-3 w-full sm:mt-4"
        >
            <div class="mx-auto h-3 w-px bg-gray-300 dark:bg-gray-700 sm:h-4" />

            <div class="relative grid grid-cols-2 gap-1 px-0.5 sm:gap-6 sm:px-2">
                <div class="absolute left-[29%] right-[29%] top-0 h-px bg-gray-300 dark:bg-gray-700 sm:left-[27%] sm:right-[27%]" />

                <div class="relative flex justify-center pt-3 sm:pt-4">
                    <div class="absolute left-[58%] top-0 h-3 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700 sm:left-[55%] sm:h-4" />

                    <NetworkTreeNode
                        v-if="node.left"
                        :node="node.left"
                        :depth="depth + 1"
                        :max-depth="maxDepth"
                        :collapsed-ids="collapsedIds"
                        :allow-placement="allowPlacement"
                        @member-click="emit('memberClick', $event)"
                        @open-placement="emit('openPlacement', $event)"
                        @toggle-expand="emit('toggleExpand', $event)"
                    />

                    <UCard
                        v-else-if="allowPlacement"
                        class="w-36 rounded-xl border border-dashed border-default bg-elevated/40 sm:w-52 sm:rounded-2xl lg:w-55"
                    >
                        <div class="flex flex-col items-center gap-1.5 py-1.5 text-center sm:gap-2 sm:py-2">
                            <UIcon name="i-lucide-user-round-plus" class="size-4 text-muted sm:size-5" />
                            <p class="text-[11px] text-muted sm:text-xs">Slot kiri kosong</p>
                            <UButton
                                size="xs"
                                color="primary"
                                variant="soft"
                                icon="i-lucide-plus"
                                class="rounded-lg sm:rounded-xl"
                                @click="emit('openPlacement', { uplineId: node.id, position: 'left' })"
                            >
                                Tempatkan
                            </UButton>
                        </div>
                    </UCard>
                </div>

                <div class="relative flex justify-center pt-3 sm:pt-4">
                    <div class="absolute left-[42%] top-0 h-3 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700 sm:left-[45%] sm:h-4" />

                    <NetworkTreeNode
                        v-if="node.right"
                        :node="node.right"
                        :depth="depth + 1"
                        :max-depth="maxDepth"
                        :collapsed-ids="collapsedIds"
                        :allow-placement="allowPlacement"
                        @member-click="emit('memberClick', $event)"
                        @open-placement="emit('openPlacement', $event)"
                        @toggle-expand="emit('toggleExpand', $event)"
                    />

                    <UCard
                        v-else-if="allowPlacement"
                        class="w-36 rounded-xl border border-dashed border-default bg-elevated/40 sm:w-52 sm:rounded-2xl lg:w-55"
                    >
                        <div class="flex flex-col items-center gap-1.5 py-1.5 text-center sm:gap-2 sm:py-2">
                            <UIcon name="i-lucide-user-round-plus" class="size-4 text-muted sm:size-5" />
                            <p class="text-[11px] text-muted sm:text-xs">Slot kanan kosong</p>
                            <UButton
                                size="xs"
                                color="primary"
                                variant="soft"
                                icon="i-lucide-plus"
                                class="rounded-lg sm:rounded-xl"
                                @click="emit('openPlacement', { uplineId: node.id, position: 'right' })"
                            >
                                Tempatkan
                            </UButton>
                        </div>
                    </UCard>
                </div>
            </div>
        </div>
    </div>
</template>
