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
        maxDepth: 6,
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
        <UCard class="w-[260px] rounded-2xl border border-default shadow-sm">
            <div class="space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-highlighted">{{ node.name }}</p>
                        <p class="truncate text-[11px] text-muted">@{{ node.username }}</p>
                    </div>

                    <div class="flex items-center gap-1">
                        <UButton
                            v-if="hasChildNode"
                            size="xs"
                            color="neutral"
                            variant="ghost"
                            :icon="isCollapsed ? 'i-lucide-plus' : 'i-lucide-minus'"
                            @click="emit('toggleExpand', node.id)"
                        />
                        <UButton
                            size="xs"
                            color="neutral"
                            variant="ghost"
                            icon="i-lucide-scan-search"
                            @click="emit('memberClick', node.id)"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <UBadge color="neutral" variant="subtle" size="xs" class="rounded-full">
                        {{ packageLabel }}
                    </UBadge>
                    <UBadge color="primary" variant="subtle" size="xs" class="rounded-full">
                        {{ levelLabel }}
                    </UBadge>
                    <UBadge color="success" variant="subtle" size="xs" class="rounded-full">
                        Kiri {{ node.total_left }}
                    </UBadge>
                    <UBadge color="info" variant="subtle" size="xs" class="rounded-full">
                        Kanan {{ node.total_right }}
                    </UBadge>
                </div>
            </div>
        </UCard>

        <div
            v-if="showChildren"
            class="mt-4 w-full"
        >
            <div class="mx-auto h-4 w-px bg-gray-300 dark:bg-gray-700" />

            <div class="relative grid grid-cols-2 gap-4 px-2 sm:gap-8">
                <div class="absolute left-1/4 right-1/4 top-0 h-px bg-gray-300 dark:bg-gray-700" />

                <div class="relative flex justify-center pt-4">
                    <div class="absolute left-1/2 top-0 h-4 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700" />

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
                        class="w-[220px] rounded-2xl border border-dashed border-default bg-elevated/40"
                    >
                        <div class="flex flex-col items-center gap-2 py-2 text-center">
                            <UIcon name="i-lucide-user-round-plus" class="size-5 text-muted" />
                            <p class="text-xs text-muted">Slot kiri kosong</p>
                            <UButton
                                size="xs"
                                color="primary"
                                variant="soft"
                                icon="i-lucide-plus"
                                class="rounded-xl"
                                @click="emit('openPlacement', { uplineId: node.id, position: 'left' })"
                            >
                                Tempatkan
                            </UButton>
                        </div>
                    </UCard>
                </div>

                <div class="relative flex justify-center pt-4">
                    <div class="absolute left-1/2 top-0 h-4 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700" />

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
                        class="w-[220px] rounded-2xl border border-dashed border-default bg-elevated/40"
                    >
                        <div class="flex flex-col items-center gap-2 py-2 text-center">
                            <UIcon name="i-lucide-user-round-plus" class="size-5 text-muted" />
                            <p class="text-xs text-muted">Slot kanan kosong</p>
                            <UButton
                                size="xs"
                                color="primary"
                                variant="soft"
                                icon="i-lucide-plus"
                                class="rounded-xl"
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
