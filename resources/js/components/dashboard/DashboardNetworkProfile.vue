<script setup lang="ts">
import type { Customer, NetworkProfile } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const { formatIDR, copyToClipboard } = useDashboard()

defineProps<{
    customer?: Customer | null
    networkProfile?: NetworkProfile
}>()
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <UIcon name="i-lucide-circle-user-round" class="size-5 text-gray-500 dark:text-gray-300" />
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Profil Network</p>
                </div>
                <UButton color="neutral" variant="ghost" size="xs" class="rounded-xl" icon="i-lucide-arrow-right">
                    Lengkapi Profil
                </UButton>
            </div>
        </template>

        <div class="grid grid-cols-2 gap-2">
            <div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Nama</p>
                <p class="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white">
                    {{ customer?.name ?? '—' }}
                </p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Username</p>
                <p class="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white">
                    {{ networkProfile?.username ?? '—' }}
                </p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Level</p>
                <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ networkProfile?.level ?? '—' }}
                </p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5">
                <p class="text-xs text-gray-500 dark:text-gray-400">Kode Referral</p>
                <div class="mt-0.5 flex items-center gap-1.5">
                    <p class="truncate font-mono text-sm font-semibold tracking-wider text-gray-900 dark:text-white">
                        {{ networkProfile?.referral_code ?? '—' }}
                    </p>
                    <UButton v-if="networkProfile?.referral_code" color="neutral" variant="ghost" size="xs"
                        icon="i-lucide-copy" class="shrink-0 rounded-lg"
                        @click="copyToClipboard(networkProfile?.referral_code ?? '')" />
                </div>
            </div>
            <div class="col-span-2 rounded-xl bg-primary-50 dark:bg-primary-950/30 px-3 py-2.5">
                <p class="text-xs text-primary-600 dark:text-primary-400">Saldo</p>
                <p class="mt-0.5 text-lg font-extrabold text-primary-700 dark:text-primary-300">
                    {{ formatIDR(networkProfile?.balance ?? 0) }}
                </p>
            </div>
        </div>
    </UCard>
</template>
