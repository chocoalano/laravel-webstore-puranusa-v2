<script setup lang="ts">
import type { CartTotals, OrderPlanType, PaymentMethod, ShippingRate } from '@/types/checkout'

const props = defineProps<{
    cart?: CartTotals | null
    saldo: number
    selectedPlan: OrderPlanType
    selectedMethod: PaymentMethod | null
    selectedRate: ShippingRate | null
    isAddressValid: boolean
    isSubmitting: boolean
    errorMessage: string | null
    midtransEnv: 'sandbox' | 'production'
    shippingCost: number
    total: number
}>()

const emit = defineEmits<{
    pay: []
    'update:selectedPlan': [OrderPlanType]
}>()

function formatIDR(n: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n)
}

function selectPlan(plan: OrderPlanType): void {
    if (plan === props.selectedPlan) {
        return
    }

    emit('update:selectedPlan', plan)
}

function planButtonClass(plan: OrderPlanType): string {
    if (props.selectedPlan === plan) {
        return 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-500/70 dark:bg-primary-950/40 dark:text-primary-300'
    }

    return 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300 dark:hover:border-gray-700'
}

const isSaldoEnough = () => props.selectedMethod === 'saldo' && props.saldo >= props.total
const isPaymentValid = () => {
    if (!props.selectedMethod) return false
    if (props.selectedMethod === 'saldo') return isSaldoEnough()
    return true
}
const canPay = () => !!(
    props.cart &&
    props.total > 0 &&
    props.isAddressValid &&
    isPaymentValid() &&
    !props.isSubmitting
)
</script>

<template>
    <div class="lg:sticky lg:top-24 space-y-4">
        <UCard class="rounded-2xl">
            <template #header>
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">Ringkasan Pembayaran</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Total dihitung otomatis dari keranjang.
                        </p>
                    </div>
                    <UBadge v-if="midtransEnv === 'sandbox'" label="Sandbox" color="warning" variant="soft"
                        class="rounded-full shrink-0" />
                </div>
            </template>

            <div class="space-y-3">
                <div class="rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                    <p class="text-xs font-semibold text-gray-900 dark:text-white">Tipe Plan Order</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Pilih plan yang akan disimpan pada transaksi order.
                    </p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            class="rounded-xl border px-3 py-2 text-left transition-colors"
                            :class="planButtonClass('planA')"
                            @click="selectPlan('planA')"
                        >
                            <p class="text-sm font-semibold">Plan A</p>
                            <p class="text-xs opacity-80">Default order</p>
                        </button>
                        <button
                            type="button"
                            class="rounded-xl border px-3 py-2 text-left transition-colors"
                            :class="planButtonClass('planB')"
                            @click="selectPlan('planB')"
                        >
                            <p class="text-sm font-semibold">Plan B</p>
                            <p class="text-xs opacity-80">Alternatif plan</p>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-300">Subtotal</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatIDR(cart?.subtotal ?? 0) }}</span>
                </div>

                <div v-if="cart && cart.discount > 0" class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-300">Diskon</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                        -{{ formatIDR(cart.discount) }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-300">Pajak</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatIDR(cart?.tax ?? 0) }}</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-300">
                        Ongkir
                        <span v-if="selectedRate" class="text-xs text-gray-400 dark:text-gray-500">
                            ({{ selectedRate.product }})
                        </span>
                    </span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatIDR(shippingCost) }}</span>
                </div>

                <div class="my-1 border-t border-gray-200 dark:border-gray-800" />

                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Total</span>
                    <span class="whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white">
                        {{ formatIDR(total) }}
                    </span>
                </div>

                <!-- Saldo info -->
                <div v-if="selectedMethod === 'saldo'"
                    class="rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Ewallet</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ formatIDR(saldo) }}
                        <span v-if="!isSaldoEnough()"
                            class="ml-2 text-xs font-medium text-rose-600 dark:text-rose-400">
                            (Kurang {{ formatIDR(total - saldo) }})
                        </span>
                    </p>
                </div>

                <!-- Checklist -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300">
                    <p class="font-semibold text-gray-900 dark:text-white">Checklist sebelum bayar</p>
                    <ul class="mt-1 list-disc space-y-1 pl-5">
                        <li :class="isAddressValid ? 'text-emerald-600 dark:text-emerald-400' : ''">
                            Alamat pengiriman lengkap
                        </li>
                        <li :class="selectedRate ? 'text-emerald-600 dark:text-emerald-400' : ''">
                            Layanan pengiriman dipilih
                        </li>
                        <li :class="selectedMethod ? 'text-emerald-600 dark:text-emerald-400' : ''">
                            Metode pembayaran dipilih
                        </li>
                        <li :class="isPaymentValid() ? 'text-emerald-600 dark:text-emerald-400' : ''">
                            {{ selectedMethod === 'saldo' ? 'Saldo mencukupi total' : 'Siap membayar via Midtrans' }}
                        </li>
                    </ul>
                </div>

                <!-- Error -->
                <div v-if="errorMessage"
                    class="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200">
                    {{ errorMessage }}
                </div>

                <UButton color="primary" variant="solid" class="rounded-xl" size="lg" block
                    :disabled="!canPay()" :loading="isSubmitting" @click="emit('pay')">
                    {{ isSubmitting ? 'Memproses…' : 'Bayar Sekarang' }}
                </UButton>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ selectedMethod === 'midtrans'
                        ? 'Kamu akan diarahkan ke popup Midtrans Snap.'
                        : 'Saldo akan dipotong langsung.' }}
                </p>

                <UButton to="/cart" color="neutral" variant="outline" class="rounded-xl" block>
                    Kembali ke Keranjang
                </UButton>
            </div>
        </UCard>
    </div>
</template>
