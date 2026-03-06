<script setup lang="ts">
import type { SecuritySummary } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const { formatDate } = useDashboard()

defineProps<{
    securitySummary?: SecuritySummary
}>()
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Keamanan Akun</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Lindungi akun dan data kamu. Gunakan menu Lock untuk pengaturan keamanan.
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <UBadge
                            :label="`Status akun: ${securitySummary?.account_status_label ?? 'Prospek'}`"
                            color="neutral"
                            variant="soft"
                            class="rounded-full" />
                        <UBadge
                            :label="securitySummary?.email_verified ? 'Email terverifikasi' : 'Email belum terverifikasi'"
                            :color="securitySummary?.email_verified ? 'success' : 'warning'"
                            variant="soft"
                            class="rounded-full" />
                    </div>
                </div>
                <UIcon name="i-lucide-shield" class="size-5 text-gray-500 dark:text-gray-300" />
            </div>
        </template>

        <div
            class="mb-4 rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300">
            <p class="font-semibold text-gray-900 dark:text-white">Status keamanan</p>
            <ul class="mt-1 list-disc space-y-1 pl-5">
                <li :class="securitySummary?.has_bank_account ? 'text-emerald-600 dark:text-emerald-400' : ''">
                    Data rekening {{ securitySummary?.has_bank_account ? 'sudah lengkap' : 'belum lengkap' }}
                </li>
                <li :class="securitySummary?.has_npwp ? 'text-emerald-600 dark:text-emerald-400' : ''">
                    NPWP {{ securitySummary?.has_npwp ? 'sudah terdaftar' : 'belum terdaftar' }}
                </li>
                <li>
                    Order terakhir:
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ securitySummary?.last_order_at ? formatDate(securitySummary.last_order_at) : 'Belum ada order' }}
                    </span>
                </li>
            </ul>
        </div>

    </UCard>
</template>
