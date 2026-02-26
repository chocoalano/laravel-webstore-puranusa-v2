<script setup lang="ts">
import NetworkTreeNode from '@/components/dashboard/network/NetworkTreeNode.vue'
import type { DashboardNetworkTreeNode } from '@/types/dashboard'

withDefaults(
    defineProps<{
        currentTree: DashboardNetworkTreeNode | null
        zoom: number
        collapsedIds: number[]
        maxDepth?: number
        allowPlacement?: boolean
    }>(),
    {
        currentTree: null,
        collapsedIds: () => [],
        maxDepth: 6,
        allowPlacement: true,
    }
)

const emit = defineEmits<{
    memberClick: [memberId: number]
    openPlacement: [payload: { uplineId: number; position: 'left' | 'right' }]
    toggleExpand: [memberId: number]
}>()
</script>

<template>
    <div class="rounded-2xl border border-default bg-elevated/20 p-2 sm:p-4">
        <div class="overflow-auto">
            <div class="min-w-max p-3 sm:p-5">
                <div
                    v-if="currentTree"
                    class="mx-auto w-fit transition-transform duration-200"
                    :style="{ transform: `scale(${zoom})`, transformOrigin: 'top center' }"
                >
                    <NetworkTreeNode
                        :node="currentTree"
                        :max-depth="maxDepth"
                        :collapsed-ids="collapsedIds"
                        :allow-placement="allowPlacement"
                        @member-click="emit('memberClick', $event)"
                        @open-placement="emit('openPlacement', $event)"
                        @toggle-expand="emit('toggleExpand', $event)"
                    />
                </div>

                <UEmpty
                    v-else
                    icon="i-lucide-network"
                    title="Jaringan belum tersedia"
                    description="Belum ada data node tree untuk akun ini."
                    :ui="{ root: 'rounded-2xl py-16' }"
                />
            </div>
        </div>
    </div>
</template>
