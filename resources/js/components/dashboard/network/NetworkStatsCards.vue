<script setup lang="ts">
import { computed } from 'vue'
import type { NetworkTreeStatsSummary } from '@/composables/useNetworkTree'

const props = defineProps<{
    stats: NetworkTreeStatsSummary
}>()

const statistics = computed(() => [
    {
        label: 'Total Jaringan',
        value: props.stats.totalDownlines,
        icon: 'i-lucide-users',
        color: 'primary',
    },
    {
        label: 'Kaki Kiri',
        value: props.stats.totalLeft,
        icon: 'i-lucide-arrow-down-left',
        color: 'blue',
    },
    {
        label: 'Kaki Kanan',
        value: props.stats.totalRight,
        icon: 'i-lucide-arrow-down-right',
        color: 'emerald',
    },
])
</script>

<template>
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
        <UCard
            v-for="stat in statistics"
            :key="stat.label"
            class="rounded-xl"
            :ui="{
                body: 'p-3',
            }"
        >
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-semibold uppercase tracking-wider text-muted">{{ stat.label }}</p>
                    <p class="mt-0.5 text-xl font-bold text-highlighted">
                        {{ stat.value.toLocaleString('id-ID') }}
                    </p>
                </div>

                <div
                    class="rounded-lg p-1.5 ring-1 ring-inset"
                    :class="[
                        stat.color === 'primary' ? 'bg-primary-50 text-primary-600 ring-primary-500/20' :
                        stat.color === 'blue' ? 'bg-blue-50 text-blue-600 ring-blue-500/20' :
                        'bg-emerald-50 text-emerald-600 ring-emerald-500/20'
                    ]"
                >
                    <UIcon :name="stat.icon" class="size-4" />
                </div>
            </div>
        </UCard>
    </div>
</template>
