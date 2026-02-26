<script setup lang="ts">
import type { NetworkTreeStatsSummary } from '@/composables/useNetworkTree'
import { computed } from 'vue';

const props = defineProps<{
  stats: NetworkTreeStatsSummary
}>()

/**
 * Memetakan data ke format yang lebih reaktif dan mudah dikelola.
 * Di Nuxt UI v4, kita memanfaatkan sistem warna 'neutral', 'primary', dan 'info'.
 */
const statistics = computed(() => [
  {
    label: 'Total Jaringan',
    value: props.stats.totalDownlines,
    icon: 'i-lucide-users',
    color: 'primary',
    description: 'Akumulasi mitra aktif'
  },
  {
    label: 'Kaki Kiri',
    value: props.stats.totalLeft,
    icon: 'i-lucide-arrow-down-left',
    color: 'blue',
    description: 'Pertumbuhan sisi kiri'
  },
  {
    label: 'Kaki Kanan',
    value: props.stats.totalRight,
    icon: 'i-lucide-arrow-down-right',
    color: 'emerald',
    description: 'Pertumbuhan sisi kanan'
  }
])
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <UCard
      v-for="stat in statistics"
      :key="stat.label"
      class="relative overflow-hidden group"
      :ui="{
        root: 'rounded-2xl transition-all duration-300 hover:shadow-sm',
        body: 'p-4 sm:p-5'
      }"
    >
      <div
        class="absolute -right-4 -top-4 size-24 blur-3xl opacity-10 transition-opacity group-hover:opacity-20"
        :class="stat.color === 'primary' ? 'bg-primary-500' : stat.color === 'blue' ? 'bg-blue-500' : 'bg-emerald-500'"
      />

      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
            {{ stat.label }}
          </p>

          <div class="flex items-baseline gap-1">
            <h3 class="text-2xl sm:text-3xl font-black tracking-tighter text-gray-900 dark:text-white">
              {{ stat.value.toLocaleString('id-ID') }}
            </h3>
            <span class="text-[10px] font-medium text-gray-400">Mitra</span>
          </div>

          <p class="text-[10px] text-gray-400 hidden sm:block">
            {{ stat.description }}
          </p>
        </div>

        <div
          class="p-2.5 rounded-xl ring-1 ring-inset shadow-xs transition-transform duration-500 group-hover:scale-110"
          :class="[
            stat.color === 'primary' ? 'bg-primary-50 dark:bg-primary-950/50 ring-primary-500/20 text-primary-600' :
            stat.color === 'blue' ? 'bg-blue-50 dark:bg-blue-950/50 ring-blue-500/20 text-blue-600' :
            'bg-emerald-50 dark:bg-emerald-950/50 ring-emerald-500/20 text-emerald-600'
          ]"
        >
          <UIcon :name="stat.icon" class="size-5 sm:size-6" />
        </div>
      </div>

      <div class="mt-4 h-1 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
        <div
          class="h-full transition-all duration-1000"
          :class="[
            stat.color === 'primary' ? 'bg-primary-500' :
            stat.color === 'blue' ? 'bg-blue-500' :
            'bg-emerald-500'
          ]"
          :style="{ width: '65%' }"
        />
      </div>
    </UCard>
  </div>
</template>
