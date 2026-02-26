<script setup lang="ts">
import { watch } from 'vue'
import type { AddressPayload, CheckoutAddress, ShippingRate } from '@/types/checkout'
import { useCheckoutAddress } from '@/composables/useCheckoutAddress'

const props = defineProps<{
    addresses: CheckoutAddress[]
    shippingFee: number
}>()

const emit = defineEmits<{
    'update:payload': [payload: AddressPayload | null]
    'update:isValid': [valid: boolean]
    'update:rate': [rate: ShippingRate | null]
}>()

const {
    addressMode,
    selectedAddressId,
    addressPayload,
    isAddressValid,
    recipientName,
    phone,
    addressLine,
    postalCode,
    notes,
    provinces,
    cities,
    districts,
    selectedProvince,
    selectedCity,
    selectedDistrict,
    isLoadingProvinces,
    isLoadingCities,
    isLoadingDistricts,
    shippingRates,
    selectedRate,
    isLoadingRates,
    shippingError,
} = useCheckoutAddress(props.addresses)

watch(addressPayload, (val) => emit('update:payload', val), { immediate: true })
watch(isAddressValid, (val) => emit('update:isValid', val), { immediate: true })
watch(selectedRate, (val) => emit('update:rate', val), { immediate: true })

function formatIDR(n: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n)
}
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Alamat Pengiriman</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Pilih alamat tersimpan atau isi manual. Pastikan nomor HP aktif untuk kurir.
                    </p>
                </div>

                <div class="w-full sm:w-auto">
                    <div
                        class="grid w-full grid-cols-2 rounded-2xl border border-gray-200 bg-white/70 p-1 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                        <UButton class="w-full rounded-xl" size="sm" color="neutral"
                            :variant="addressMode === 'saved' ? 'solid' : 'ghost'" @click="addressMode = 'saved'">
                            Pilih alamat
                        </UButton>
                        <UButton class="w-full rounded-xl" size="sm" color="neutral"
                            :variant="addressMode === 'manual' ? 'solid' : 'ghost'" @click="addressMode = 'manual'">
                            Isi manual
                        </UButton>
                    </div>
                </div>
            </div>
        </template>

        <!-- SAVED ADDRESSES -->
        <div v-if="addressMode === 'saved'" class="space-y-3">
            <div v-if="addresses.length === 0"
                class="w-full rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
                Belum ada alamat tersimpan. Silakan pilih "Isi manual".
            </div>

            <div v-else class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                <button v-for="a in addresses" :key="a.id" type="button"
                    class="w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55"
                    :class="[
                        selectedAddressId === a.id
                            ? 'border-primary-500 ring-2 ring-primary-500/20'
                            : 'border-gray-200 dark:border-gray-800',
                    ]"
                    @click="selectedAddressId = a.id">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                {{ a.label }}
                                <span v-if="a.is_default" class="ml-2 text-xs text-gray-500 dark:text-gray-400">(Default)</span>
                            </p>
                            <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                {{ a.recipient_name }} • {{ a.phone }}
                            </p>
                        </div>
                        <UBadge v-if="selectedAddressId === a.id" label="Dipilih" color="primary" variant="soft"
                            size="xs" class="shrink-0 rounded-full" />
                    </div>

                    <p class="mt-2 line-clamp-2 text-sm text-gray-700 dark:text-gray-200">
                        {{ a.address_line }}, {{ a.city }}, {{ a.province }}, {{ a.postal_code }}
                    </p>

                    <p v-if="a.description" class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">
                        Catatan: {{ a.description }}
                    </p>
                </button>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Tip: gunakan alamat "Default" untuk checkout lebih cepat.
                </p>
                <UButton to="/dashboard" color="neutral" variant="ghost" class="rounded-xl" size="sm">
                    Kelola alamat
                </UButton>
            </div>
        </div>

        <!-- MANUAL FORM -->
        <div v-else class="space-y-4">
            <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                <UFormField label="Nama penerima" required class="w-full">
                    <UInput v-model="recipientName" placeholder="Nama lengkap" class="w-full" />
                </UFormField>

                <UFormField label="No. HP" required class="w-full">
                    <UInput v-model="phone" placeholder="08xxxxxxxxxx" class="w-full" />
                </UFormField>
            </div>

            <UFormField label="Alamat lengkap" required class="w-full">
                <UTextarea v-model="addressLine" placeholder="Jalan, RT/RW, nomor rumah, patokan..." :rows="3"
                    class="w-full" />
            </UFormField>

            <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3">
                <UFormField label="Provinsi" required class="w-full">
                    <USelect v-model="selectedProvince"
                        :items="provinces.map(p => ({ label: p, value: p }))"
                        placeholder="Pilih provinsi"
                        :loading="isLoadingProvinces"
                        class="w-full" />
                </UFormField>

                <UFormField label="Kota/Kab" required class="w-full">
                    <USelect v-model="selectedCity"
                        :items="cities.map(c => ({ label: c, value: c }))"
                        placeholder="Pilih kota"
                        :loading="isLoadingCities"
                        :disabled="!selectedProvince"
                        class="w-full" />
                </UFormField>

                <UFormField label="Kecamatan" class="w-full">
                    <USelect v-model="selectedDistrict"
                        :items="districts.map(d => ({ label: d, value: d }))"
                        placeholder="Pilih kecamatan"
                        :loading="isLoadingDistricts"
                        :disabled="!selectedCity"
                        class="w-full" />
                </UFormField>
            </div>

            <UFormField label="Kode pos" required class="w-full sm:w-1/3">
                <UInput v-model="postalCode" placeholder="12345" class="w-full" />
            </UFormField>

            <UFormField label="Catatan kurir (opsional)" class="w-full">
                <UInput v-model="notes" placeholder="Contoh: titip satpam / pagar hitam" class="w-full" />
            </UFormField>

            <div
                class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300">
                <div class="flex items-start gap-2">
                    <UIcon name="i-lucide-info" class="mt-0.5 size-4 text-gray-500 dark:text-gray-400" />
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white">Panduan singkat</p>
                        <ul class="mt-1 list-disc space-y-1 pl-5">
                            <li>Masukkan alamat sedetail mungkin (RT/RW dan patokan membantu kurir).</li>
                            <li>Nomor HP dipakai untuk konfirmasi pengantaran.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- SHIPPING RATES -->
        <div v-if="isLoadingRates" class="mt-4">
            <div
                class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                <UIcon name="i-lucide-loader-circle" class="size-4 animate-spin text-gray-400" />
                <p class="text-sm text-gray-500 dark:text-gray-400">Memuat tarif ongkir Lion Parcel…</p>
            </div>
        </div>

        <div v-else-if="shippingError" class="mt-4">
            <div
                class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                <div class="flex items-center gap-2">
                    <UIcon name="i-lucide-triangle-alert" class="size-4 shrink-0" />
                    {{ shippingError }}
                </div>
            </div>
        </div>

        <div v-else-if="shippingRates.length > 0" class="mt-4 space-y-3">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Layanan Pengiriman (Lion Parcel)</p>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <button v-for="rate in shippingRates" :key="rate.product" type="button"
                    class="w-full rounded-2xl border p-3 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55"
                    :class="[
                        selectedRate?.product === rate.product
                            ? 'border-primary-500 ring-2 ring-primary-500/20'
                            : 'border-gray-200 dark:border-gray-800',
                    ]"
                    @click="selectedRate = rate">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ rate.product }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ rate.estimasi_sla }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <p class="whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                {{ formatIDR(rate.total_tariff) }}
                            </p>
                            <UBadge v-if="selectedRate?.product === rate.product" label="Dipilih"
                                color="primary" variant="soft" size="xs" class="rounded-full" />
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <template #footer>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Status alamat:
                    <span
                        :class="isAddressValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                        {{ isAddressValid ? 'Lengkap ✓' : 'Belum lengkap' }}
                    </span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <template v-if="selectedRate">
                        Ongkir:
                        <span class="font-semibold text-gray-900 dark:text-white">{{ formatIDR(selectedRate.total_tariff) }}</span>
                        via {{ selectedRate.product }}
                    </template>
                    <template v-else>
                        Ongkir: <span class="font-semibold text-gray-900 dark:text-white">{{ formatIDR(shippingFee) }}</span>
                    </template>
                </div>
            </div>
        </template>
    </UCard>
</template>
