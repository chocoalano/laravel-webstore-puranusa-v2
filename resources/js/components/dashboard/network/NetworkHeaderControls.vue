<script setup lang="ts">
import type { NetworkTreeSearchResult } from '@/composables/useNetworkTree'
import type { DashboardNetworkTreeNode } from '@/types/dashboard'

withDefaults(
    defineProps<{
        isViewingMemberTree: boolean
        selectedMemberForTree: DashboardNetworkTreeNode | null
        maxLoadedLevel: number
        treeSearchQuery: string
        showTreeSearchResults: boolean
        treeSearchResults: NetworkTreeSearchResult[]
    }>(),
    {
        selectedMemberForTree: null,
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
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 sm:gap-3">
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
                <p class="text-sm font-semibold text-highlighted sm:text-base">
                    {{ isViewingMemberTree && selectedMemberForTree ? `Jaringan ${selectedMemberForTree.name}` : 'Struktur Binary Tree MLM' }}
                </p>
                <p class="text-xs text-muted">
                    {{ isViewingMemberTree && selectedMemberForTree ? `@${selectedMemberForTree.username}` : `Data model customer • depth termuat hingga level ${maxLoadedLevel}` }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <UInput
                    :model-value="treeSearchQuery"
                    icon="i-lucide-search"
                    placeholder="Cari member..."
                    size="sm"
                    class="w-56"
                    @update:model-value="handleTreeSearchModelUpdate"
                    @input="emit('searchInput')"
                    @focus="emit('searchFocus')"
                    @blur="emit('searchBlur')"
                />

                <div
                    v-if="showTreeSearchResults"
                    class="absolute right-0 top-full z-20 mt-1 w-72 overflow-hidden rounded-xl border border-default bg-default shadow-lg"
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

            <UButton color="neutral" variant="outline" size="sm" icon="i-lucide-unfold-vertical" class="rounded-xl" @click="emit('expandAll')">
                Expand
            </UButton>
            <UButton color="neutral" variant="outline" size="sm" icon="i-lucide-fold-vertical" class="rounded-xl" @click="emit('collapseAll')">
                Collapse
            </UButton>
            <UButton color="neutral" variant="outline" size="sm" icon="i-lucide-zoom-out" class="rounded-xl" @click="emit('zoomOut')" />
            <UButton color="neutral" variant="outline" size="sm" icon="i-lucide-zoom-in" class="rounded-xl" @click="emit('zoomIn')" />
            <UButton color="neutral" variant="ghost" size="sm" icon="i-lucide-rotate-ccw" class="rounded-xl" @click="emit('resetZoom')" />
        </div>
    </div>
</template>
