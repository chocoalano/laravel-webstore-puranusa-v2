<script setup lang="ts">
import { computed } from 'vue'
import type { NetworkStats } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const { formatIDR } = useDashboard()

const props = defineProps<{
    networkStats?: NetworkStats
}>()

defineEmits<{
    navigate: [section: string]
}>()

const statItems = computed(() => [
    { label: 'Jaringan Kiri', value: String(props.networkStats?.left_count ?? 0) },
    { label: 'Jaringan Kanan', value: String(props.networkStats?.right_count ?? 0) },
    { label: 'Total Downline', value: String(props.networkStats?.total_downline ?? 0) },
    { label: 'Omset Group', value: formatIDR(props.networkStats?.omset_group ?? 0) },
    { label: 'Omset NB Kiri', value: formatIDR(props.networkStats?.omset_nb_left ?? 0) },
    { label: 'Omset NB Kanan', value: formatIDR(props.networkStats?.omset_nb_right ?? 0) },
    { label: 'Omset Retail Kiri', value: formatIDR(props.networkStats?.omset_retail_left ?? 0) },
    { label: 'Omset Retail Kanan', value: formatIDR(props.networkStats?.omset_retail_right ?? 0) },
])
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <UIcon name="i-lucide-bar-chart-2" class="size-5 text-gray-500 dark:text-gray-300" />
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Statistik Jaringan</p>
                </div>
                <UButton color="neutral" variant="ghost" size="xs" class="rounded-xl" icon="i-lucide-arrow-right"
                    @click="$emit('navigate', 'network')">
                    Selengkapnya
                </UButton>
            </div>
        </template>

        <div class="grid grid-cols-2 gap-2">
            <div v-for="item in statItems" :key="item.label"
                class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.label }}</p>
                <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white">{{ item.value }}</p>
            </div>
        </div>
    </UCard>
</template>
