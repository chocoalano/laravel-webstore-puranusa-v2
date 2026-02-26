<script setup lang="ts">
import type { PaymentMethod } from '@/types/checkout'

const props = defineProps<{
    saldo: number
    total: number
    midtransClientKey: string
    modelValue: PaymentMethod | null
}>()

const emit = defineEmits<{
    'update:modelValue': [method: PaymentMethod | null]
}>()

function formatIDR(n: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n)
}

const isSaldoEnough = () => props.saldo >= props.total
const saldoShortage = () => Math.max(0, props.total - props.saldo)
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div>
                <p class="text-base font-semibold text-gray-900 dark:text-white">Metode Pembayaran</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pilih metode pembayaran. Saldo hanya bisa dipilih jika mencukupi total.
                </p>
            </div>
        </template>

        <div class="space-y-3">
            <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                <!-- SALDO -->
                <button type="button"
                    class="w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40"
                    :class="[
                        isSaldoEnough() ? 'hover:bg-white dark:hover:bg-gray-950/55' : 'cursor-not-allowed opacity-60',
                        modelValue === 'saldo' ? 'border-primary-500 ring-2 ring-primary-500/20' : 'border-gray-200 dark:border-gray-800',
                    ]"
                    :disabled="!isSaldoEnough()"
                    @click="isSaldoEnough() && emit('update:modelValue', 'saldo')">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Saldo Ewallet</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Saldo: <span class="font-semibold">{{ formatIDR(saldo) }}</span>
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <UIcon name="i-lucide-wallet" class="size-4 text-gray-500 dark:text-gray-400" />
                            <UBadge :label="isSaldoEnough() ? 'Cukup' : 'Saldo kurang'"
                                :color="isSaldoEnough() ? 'success' : 'warning'" variant="soft" size="xs"
                                class="rounded-full" />
                        </div>
                    </div>
                    <p v-if="!isSaldoEnough()" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Kurang {{ formatIDR(saldoShortage()) }}
                    </p>
                </button>

                <!-- MIDTRANS -->
                <button type="button"
                    class="w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55"
                    :class="[
                        modelValue === 'midtrans' ? 'border-primary-500 ring-2 ring-primary-500/20' : 'border-gray-200 dark:border-gray-800',
                        !midtransClientKey ? 'cursor-not-allowed opacity-60' : '',
                    ]"
                    :disabled="!midtransClientKey"
                    @click="midtransClientKey && emit('update:modelValue', 'midtrans')">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Midtrans</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                GoPay, ShopeePay, Transfer, Kartu Kredit & lebih
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <UIcon name="i-lucide-credit-card" class="size-4 text-gray-500 dark:text-gray-400" />
                            <UBadge label="Snap" color="primary" variant="soft" size="xs" class="rounded-full" />
                        </div>
                    </div>
                </button>
            </div>

            <div v-if="!midtransClientKey"
                class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                Midtrans clientKey belum tersedia. Hubungi admin untuk mengaktifkan metode ini.
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300">
                <div class="flex items-start gap-2">
                    <UIcon name="i-lucide-shield-check" class="mt-0.5 size-4 text-gray-500 dark:text-gray-400" />
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white">Aman & terlindungi</p>
                        <ul class="mt-1 list-disc space-y-1 pl-5">
                            <li>Saldo ewallet langsung dipotong saat transaksi berhasil.</li>
                            <li>Midtrans Snap menampilkan popup pembayaran yang aman.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </UCard>
</template>
