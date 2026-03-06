<script setup lang="ts">
import type { NetworkTreeSearchResult } from '@/composables/useNetworkTree'
import type { DashboardNetworkTreeNode } from '@/types/dashboard'

withDefaults(
    defineProps<{
        isViewingMemberTree: boolean
        selectedMemberForTree: DashboardNetworkTreeNode | null
        maxLoadedLevel: number
        zoom?: number
        treeSearchQuery: string
        showTreeSearchResults: boolean
        treeSearchResults: NetworkTreeSearchResult[]
    }>(),
    {
        selectedMemberForTree: null,
        zoom: 1,
        treeSearchQuery: '',
        showTreeSearchResults: false,
        treeSearchResults: () => [],
    }
)

const emit = defineEmits<{
    back: []
    'update:treeSearchQuery': [value: string]
    searchInput: []
    searchFocus: []
    searchBlur: []
    selectSearchResult: [memberId: number]
    expandAll: []
    collapseAll: []
    zoomOut: []
    zoomIn: []
    resetZoom: []
}>()

function handleTreeSearchModelUpdate(value: string | number | null | undefined): void {
    emit('update:treeSearchQuery', String(value ?? ''))
}
</script>

<template>
    <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <UButton
                v-if="isViewingMemberTree"
                color="neutral"
                variant="outline"
                size="xs"
                icon="i-lucide-arrow-left"
                class="rounded-xl"
                @click="emit('back')"
            >
                Kembali
            </UButton>

            <div>
                <p class="text-xs font-semibold text-highlighted sm:text-sm">
                    {{ isViewingMemberTree && selectedMemberForTree ? `Jaringan ${selectedMemberForTree.name}` : 'Struktur Binary Tree MLM' }}
                </p>
                <p class="text-[11px] text-muted">
                    {{ isViewingMemberTree && selectedMemberForTree ? `@${selectedMemberForTree.username}` : `Data model customer • depth termuat hingga level ${maxLoadedLevel}` }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
            <div class="relative w-full sm:w-auto">
                <UInput
                    :model-value="treeSearchQuery"
                    icon="i-lucide-search"
                    placeholder="Cari member..."
                    size="xs"
                    class="w-full sm:w-52"
                    @update:model-value="handleTreeSearchModelUpdate"
                    @input="emit('searchInput')"
                    @focus="emit('searchFocus')"
                    @blur="emit('searchBlur')"
                />

                <div
                    v-if="showTreeSearchResults"
                    class="absolute left-1/2 top-full z-20 mt-1 w-64 max-w-[calc(100vw-1rem)] -translate-x-1/2 overflow-hidden rounded-xl border border-default bg-default shadow-lg sm:left-auto sm:right-0 sm:max-w-none sm:translate-x-0"
                >
                    <div v-if="treeSearchResults.length === 0" class="p-3 text-sm text-muted">
                        Tidak ditemukan member.
                    </div>

                    <button
                        v-for="member in treeSearchResults"
                        :key="member.id"
                        type="button"
                        class="w-full border-b border-default px-3 py-2 text-left transition hover:bg-elevated/70 last:border-b-0"
                        @mousedown.prevent="emit('selectSearchResult', member.id)"
                    >
                        <p class="truncate text-sm font-medium text-highlighted">{{ member.name }}</p>
                        <p class="truncate text-xs text-muted">@{{ member.username }} • {{ member.package_name }}</p>
                    </button>
                </div>
            </div>

            <span class="inline-flex items-center rounded-lg border border-default px-2 py-1 text-[10px] font-semibold text-muted">
                {{ Math.round(zoom * 100) }}%
            </span>

            <UButton color="neutral" variant="outline" size="xs" icon="i-lucide-unfold-vertical" class="rounded-lg" @click="emit('expandAll')">
                <span class="hidden sm:inline">Expand</span>
            </UButton>
            <UButton color="neutral" variant="outline" size="xs" icon="i-lucide-fold-vertical" class="rounded-lg" @click="emit('collapseAll')">
                <span class="hidden sm:inline">Collapse</span>
            </UButton>
            <UButton color="neutral" variant="outline" size="xs" icon="i-lucide-zoom-out" class="rounded-lg" @click="emit('zoomOut')" />
            <UButton color="neutral" variant="outline" size="xs" icon="i-lucide-zoom-in" class="rounded-lg" @click="emit('zoomIn')" />
            <UButton color="neutral" variant="ghost" size="xs" icon="i-lucide-rotate-ccw" class="rounded-lg" @click="emit('resetZoom')" />
        </div>
    </div>
</template>
