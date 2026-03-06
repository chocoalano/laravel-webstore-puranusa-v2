<script setup lang="ts">
import NetworkGojsTree from '@/components/dashboard/network/NetworkGojsTree.vue'
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
        maxDepth: 5,
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
    <NetworkGojsTree
        v-if="currentTree"
        :node="currentTree"
        :collapsed-ids="collapsedIds"
        :max-depth="maxDepth"
        :allow-placement="allowPlacement"
        :zoom="zoom"
        @member-click="emit('memberClick', $event)"
        @open-placement="emit('openPlacement', $event)"
        @toggle-expand="emit('toggleExpand', $event)"
    />

    <UEmpty
        v-else
        icon="i-lucide-network"
        title="Jaringan belum tersedia"
        description="Belum ada data node tree untuk akun ini."
        :ui="{ root: 'rounded-2xl py-12' }"
    />
</template>
