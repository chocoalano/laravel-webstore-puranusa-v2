<script setup lang="ts">
import type { Stats } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const { formatIDR } = useDashboard()

defineProps<{
    stats?: Stats
}>()
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex items-center justify-between">
                <p class="text-base font-semibold text-gray-900 dark:text-white">Lifetime</p>
                <UIcon name="i-lucide-trophy" class="size-5 text-gray-500 dark:text-gray-300" />
            </div>
        </template>

        <div class="space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-300">Bonus lifetime</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ formatIDR(stats?.bonus_lifetime ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-300">Promo aktif</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ stats?.promo_active ?? 0 }}</span>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300">
                <p class="font-semibold text-gray-900 dark:text-white">Rekomendasi</p>
                <ul class="mt-1 list-disc space-y-1 pl-5">
                    <li>Aktifkan network untuk unlock bonus lebih besar.</li>
                    <li>Cek promo sebelum checkout agar lebih hemat.</li>
                </ul>
            </div>
        </div>
    </UCard>
</template>
