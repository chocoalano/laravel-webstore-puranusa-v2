<script setup lang="ts">
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import CheckoutItemsList from '@/components/checkout/CheckoutItemsList.vue'
import CheckoutAddressPanel from '@/components/checkout/CheckoutAddressPanel.vue'
import CheckoutPaymentPanel from '@/components/checkout/CheckoutPaymentPanel.vue'
import CheckoutSummary from '@/components/checkout/CheckoutSummary.vue'
import { useCheckout } from '@/composables/useCheckout'
import { useMidtrans } from '@/composables/useMidtrans'
import type { AddressPayload, OrderPlanType, PaymentMethod, ShippingRate } from '@/types/checkout'

defineOptions({ layout: AppLayout })

const { items, cart, addresses, saldo, midtrans, itemCount } = useCheckout()

const { isSubmitting, errorMessage, payViaMidtrans, payViaSaldo } = useMidtrans(
    midtrans.value.env,
    midtrans.value.client_key,
)

// Address state — driven by CheckoutAddressPanel via events
const addressPayload = ref<AddressPayload | null>(null)
const isAddressValid = ref(false)
const selectedRate = ref<ShippingRate | null>(null)

// Payment method — driven by CheckoutPaymentPanel via v-model
const selectedMethod = ref<PaymentMethod | null>(null)
const selectedPlan = ref<OrderPlanType>('planA')

const shippingCost = computed(() => selectedRate.value?.total_tariff ?? cart.value?.shipping ?? 0)
const total = computed(() => (cart.value?.subtotal ?? 0) + shippingCost.value + (cart.value?.tax ?? 0) - (cart.value?.discount ?? 0))

async function payNow(): Promise<void> {
    if (!addressPayload.value || !selectedMethod.value) return

    const payload = {
        ...addressPayload.value,
        order_type: selectedPlan.value,
        shipping_service_code: selectedRate.value?.product ?? '',
        shipping_cost: shippingCost.value,
        shipping_etd: selectedRate.value?.estimasi_sla ?? '',
    }

    if (selectedMethod.value === 'saldo') {
        await payViaSaldo(payload)
    } else {
        await payViaMidtrans(payload)
    }
}
</script>

<template>
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 mt-8">
        <UPage class="min-h-screen bg-gray-50/60 dark:bg-gray-950 transition-colors duration-300">
            <UPageHeader class="p-5" title="Checkout" description="Alamat → Pembayaran → Konfirmasi">
                <template #right>
                    <div class="flex items-center gap-2">
                        <UBadge v-if="midtrans.env === 'sandbox'" label="Sandbox" color="warning" variant="soft"
                            class="rounded-full" />
                        <UBadge :label="`${itemCount} item`" color="neutral" variant="soft" class="rounded-full" />
                    </div>
                </template>
            </UPageHeader>

            <UPageBody class="p-5">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                    <!-- LEFT: Alamat, Produk, Pembayaran -->
                    <div class="space-y-5 lg:col-span-8">
                        <CheckoutAddressPanel
                            :addresses="addresses"
                            :shipping-fee="cart?.shipping ?? 0"
                            @update:payload="addressPayload = $event"
                            @update:is-valid="isAddressValid = $event"
                            @update:rate="selectedRate = $event" />

                        <CheckoutItemsList :items="items" :cart="cart" />

                        <CheckoutPaymentPanel
                            :saldo="saldo"
                            :total="total"
                            :midtrans-client-key="midtrans.client_key"
                            :model-value="selectedMethod"
                            @update:model-value="selectedMethod = $event" />
                    </div>

                    <!-- RIGHT: Ringkasan -->
                    <div class="lg:col-span-4">
                        <CheckoutSummary
                            :cart="cart"
                            :saldo="saldo"
                            :selected-plan="selectedPlan"
                            :selected-method="selectedMethod"
                            :selected-rate="selectedRate"
                            :is-address-valid="isAddressValid"
                            :is-submitting="isSubmitting"
                            :error-message="errorMessage"
                            :midtrans-env="midtrans.env"
                            :shipping-cost="shippingCost"
                            :total="total"
                            @update:selected-plan="selectedPlan = $event"
                            @pay="payNow" />
                    </div>
                </div>
            </UPageBody>
        </UPage>
    </div>
</template>
