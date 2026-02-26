import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { CheckoutItem, CartTotals, CheckoutPageProps } from '@/types/checkout'

export function useCheckout() {
    const page = usePage<CheckoutPageProps>()

    const items = computed<CheckoutItem[]>(() => page.props.items ?? [])
    const cart = computed<CartTotals | null>(() => page.props.cart ?? null)
    const addresses = computed(() => page.props.addresses ?? [])
    const saldo = computed(() => page.props.saldo ?? 0)
    const midtrans = computed(() => page.props.midtrans)

    const itemCount = computed(() => items.value.reduce((acc, it) => acc + it.qty, 0))

    function formatIDR(n: number): string {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(n)
    }

    return { items, cart, addresses, saldo, midtrans, itemCount, formatIDR }
}
