<script setup lang="ts">
import { nextTick, onMounted, ref, watch } from 'vue'
import NetworkTreeNode from '@/components/dashboard/network/NetworkTreeNode.vue'
import type { DashboardNetworkTreeNode } from '@/types/dashboard'

const props = withDefaults(
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

const scrollContainer = ref<HTMLElement | null>(null)

function centerTreeHorizontally(): void {
    const container = scrollContainer.value

    if (!container) {
        return
    }

    const centeredLeft = Math.max(0, (container.scrollWidth - container.clientWidth) / 2)
    container.scrollLeft = centeredLeft
}

function syncCenterOnNextFrame(): void {
    void nextTick(() => {
        centerTreeHorizontally()
    })
}

onMounted(() => {
    syncCenterOnNextFrame()
})

watch(
    () => [props.currentTree?.id, props.zoom, props.collapsedIds.join(',')],
    () => {
        syncCenterOnNextFrame()
    }
)
</script>

<template>
    <div class="rounded-2xl border border-default bg-elevated/20">
        <div ref="scrollContainer" class="overflow-x-auto">
            <div class="min-w-full w-max">
                <div
                    v-if="currentTree"
                    class="mx-auto w-fit px-2 py-3 transition-transform duration-200 sm:px-4 sm:py-5"
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
