import { computed, ref } from 'vue'
import type { PaymentMethod } from '@/types/checkout'

export function useCheckoutPayment(saldo: number, getTotal: () => number) {
    const selectedMethod = ref<PaymentMethod | null>(null)

    const isSaldoEnough = computed(() => saldo >= getTotal())
    const saldoShortage = computed(() => Math.max(0, getTotal() - saldo))

    return {
        selectedMethod,
        isSaldoEnough,
        saldoShortage,
    }
}
