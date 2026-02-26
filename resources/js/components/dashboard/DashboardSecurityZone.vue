<script setup lang="ts">
import type { SecuritySummary } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const { formatDate } = useDashboard()

defineProps<{
    securitySummary?: SecuritySummary
}>()

defineEmits<{
    navigate: [section: string]
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

        <UCard class="rounded-2xl border border-rose-200 bg-rose-50/60 dark:border-rose-900/50 dark:bg-rose-950/30">
            <div class="flex items-start gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-rose-100 dark:bg-rose-950/50">
                    <UIcon name="i-lucide-user-x" class="size-5 text-rose-600 dark:text-rose-300" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-rose-800 dark:text-rose-200">Delete Account</p>
                    <p class="mt-1 text-xs text-rose-700 dark:text-rose-300">
                        Aksi ini permanen. Pastikan kamu sudah backup data penting.
                    </p>
                    <div class="mt-3">
                        <UButton color="error" variant="solid" size="sm" class="rounded-xl"
                            @click="$emit('navigate', 'delete')">
                            Hapus Akun
                        </UButton>
                    </div>
                </div>
            </div>
        </UCard>
    </UCard>
</template>
