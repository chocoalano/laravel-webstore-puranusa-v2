<script setup lang="ts">
import { computed } from 'vue'
import type { TableColumn, TabsItem } from '@nuxt/ui'
import type { DashboardBonusRow, DashboardBonusType } from '@/types/dashboard'

type BonusTab = DashboardBonusType | 'all'

const props = defineProps<{
    activeTab: BonusTab
    searchQuery: string
    page: number
    itemsPerPage: number
    tabs: TabsItem[]
    rows: DashboardBonusRow[]
    totalRows: number
    columns: TableColumn<DashboardBonusRow>[]
}>()

const emit = defineEmits<{
    (e: 'update:activeTab', value: BonusTab): void
    (e: 'update:searchQuery', value: string): void
    (e: 'update:page', value: number): void
}>()

function onSearchUpdate(value: string | number): void {
    emit('update:searchQuery', String(value ?? ''))
}

function onTabUpdate(value: string | number): void {
    emit('update:activeTab', String(value ?? 'all') as BonusTab)
}

function onPageUpdate(value: number): void {
    emit('update:page', value)
}

const maxPage = computed(() => {
    const perPage = Math.max(1, props.itemsPerPage)
    return Math.max(1, Math.ceil(props.totalRows / perPage))
})

const canPrev = computed(() => props.page > 1)
const canNext = computed(() => props.page < maxPage.value)

function goPrev(): void {
    if (!canPrev.value) {
        return
    }

    emit('update:page', props.page - 1)
}

function goNext(): void {
    if (!canNext.value) {
        return
    }

    emit('update:page', props.page + 1)
}
</script>

<template>
    <UCard class="overflow-hidden rounded-2xl" :ui="{ body: 'p-0 sm:p-0', header: 'px-4 py-4' }">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-base font-bold text-highlighted">Riwayat Bonus</h3>

                <div class="flex items-center gap-2">
                    <UInput :model-value="props.searchQuery" icon="i-lucide-search"
                        placeholder="Cari deskripsi, member, reward..." size="sm" class="w-full sm:w-72"
                        @update:model-value="onSearchUpdate" />
                </div>
            </div>
        </template>

        <div class="border-b border-default">
            <div class="overflow-x-auto px-2 py-1 sm:px-0 sm:py-0">
                <UTabs :model-value="props.activeTab" :items="props.tabs" value-key="value" :content="false"
                    class="w-max min-w-full" @update:model-value="onTabUpdate" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <UTable :data="props.rows" :columns="props.columns" class="w-full min-w-180">
                <template #empty>
                    <div class="flex flex-col items-center justify-center py-10">
                        <UIcon name="i-lucide-database-backup" class="mb-2 size-8 text-muted" />
                        <p class="text-sm text-muted">Belum ada data bonus untuk kategori ini.</p>
                    </div>
                </template>
            </UTable>
        </div>

        <template #footer>
            <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-xs text-muted">Total {{ props.totalRows }} data</span>

                <div
                    class="flex items-center justify-between gap-4 py-2 sm:hidden border-t border-gray-100 dark:border-gray-800">
                    <UButton icon="i-lucide-arrow-left" color="neutral" variant="ghost" size="sm" :disabled="!canPrev"
                        @click="goPrev" class="rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900">
                        <span class="text-xs font-medium">Prev</span>
                    </UButton>

                    <div class="flex flex-col items-center">
                        <span class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Halaman</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-sm font-black text-primary-600 dark:text-primary-400">{{ props.page
                                }}</span>
                            <span class="text-xs text-gray-300 dark:text-gray-600">/</span>
                            <span class="text-xs font-medium text-gray-500">{{ maxPage }}</span>
                        </div>
                    </div>

                    <UButton icon="i-lucide-arrow-right" color="neutral" variant="ghost" size="sm" trailing
                        :disabled="!canNext" @click="goNext"
                        class="rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900">
                        <span class="text-xs font-medium">Next</span>
                    </UButton>
                </div>

                <UPagination class="hidden sm:flex" :page="props.page" :total="props.totalRows"
                    :items-per-page="props.itemsPerPage" size="sm" @update:page="onPageUpdate" />
            </div>
        </template>
    </UCard>
</template>
