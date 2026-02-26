import { computed, onMounted, ref, watch } from 'vue'
import type { AddressMode, AddressPayload, CheckoutAddress, ShippingRate } from '@/types/checkout'

export function useCheckoutAddress(savedAddresses: CheckoutAddress[]) {
    const addressMode = ref<AddressMode>(savedAddresses.length ? 'saved' : 'manual')
    const selectedAddressId = ref<string | number | null>(null)

    // Manual form fields
    const recipientName = ref('')
    const phone = ref('')
    const addressLine = ref('')
    const postalCode = ref('')
    const notes = ref('')

    // Cascading dropdowns
    const provinces = ref<string[]>([])
    const cities = ref<string[]>([])
    const districts = ref<string[]>([])
    const selectedProvince = ref('')
    const selectedCity = ref('')
    const selectedDistrict = ref('')

    const isLoadingProvinces = ref(false)
    const isLoadingCities = ref(false)
    const isLoadingDistricts = ref(false)

    // Shipping rates
    const shippingRates = ref<ShippingRate[]>([])
    const selectedRate = ref<ShippingRate | null>(null)
    const isLoadingRates = ref(false)
    const shippingError = ref<string | null>(null)

    onMounted(() => {
        const defaultAddress = savedAddresses.find((a) => a.is_default) ?? savedAddresses[0]
        if (defaultAddress) {
            selectedAddressId.value = defaultAddress.id
        }
        loadProvinces()
    })

    async function loadProvinces(): Promise<void> {
        isLoadingProvinces.value = true
        try {
            const res = await fetch('/checkout/shipping/provinces')
            if (!res.ok) return
            provinces.value = await res.json()
        } catch {
            // silently ignore; user will see empty dropdown
        } finally {
            isLoadingProvinces.value = false
        }
    }

    async function loadCities(province: string): Promise<void> {
        cities.value = []
        districts.value = []
        selectedCity.value = ''
        selectedDistrict.value = ''
        if (!province) return

        isLoadingCities.value = true
        try {
            const res = await fetch(`/checkout/shipping/cities?province=${encodeURIComponent(province)}`)
            if (!res.ok) return
            cities.value = await res.json()
        } catch {
            // silently ignore
        } finally {
            isLoadingCities.value = false
        }
    }

    async function loadDistricts(province: string, city: string): Promise<void> {
        districts.value = []
        selectedDistrict.value = ''
        if (!province || !city) return

        isLoadingDistricts.value = true
        try {
            const url = `/checkout/shipping/districts?province=${encodeURIComponent(province)}&city=${encodeURIComponent(city)}`
            const res = await fetch(url)
            if (!res.ok) return
            districts.value = await res.json()
        } catch {
            // silently ignore
        } finally {
            isLoadingDistricts.value = false
        }
    }

    async function loadShippingRates(province: string, city: string, district?: string): Promise<void> {
        shippingRates.value = []
        selectedRate.value = null
        shippingError.value = null
        if (!province || !city) return

        isLoadingRates.value = true
        try {
            const params = new URLSearchParams({ province, city })
            if (district) params.set('district', district)
            const res = await fetch(`/checkout/shipping/cost?${params}`)

            if (!res.ok) {
                const data = await res.json().catch(() => ({}))
                shippingError.value = (data as { message?: string })?.message ?? 'Tujuan pengiriman tidak tersedia.'
                return
            }

            shippingRates.value = await res.json()
        } catch {
            shippingError.value = 'Gagal memuat tarif ongkir.'
        } finally {
            isLoadingRates.value = false
        }
    }

    watch(selectedProvince, (val) => {
        loadCities(val)
    })

    watch(selectedCity, async (val) => {
        if (val && selectedProvince.value) {
            await loadDistricts(selectedProvince.value, val)

            if (addressMode.value === 'manual') {
                await loadShippingRates(selectedProvince.value, val)
            }
        } else {
            shippingRates.value = []
            selectedRate.value = null
        }
    })

    // Manual mode: load rates when district is selected (or when city selected if no districts exist)
    watch(selectedDistrict, (district) => {
        if (addressMode.value === 'manual' && selectedProvince.value && selectedCity.value) {
            loadShippingRates(selectedProvince.value, selectedCity.value, district || undefined)
        }
    })

    // Saved mode: load rates when an address is selected
    watch(selectedAddressId, (id) => {
        if (addressMode.value !== 'saved' || !id) return
        const address = savedAddresses.find((a) => a.id === id)
        if (address?.province && address?.city) {
            loadShippingRates(address.province, address.city)
        }
    })

    // Reset rates when switching modes
    watch(addressMode, () => {
        shippingRates.value = []
        selectedRate.value = null
        shippingError.value = null
    })

    const selectedAddress = computed<CheckoutAddress | null>(() => {
        if (!selectedAddressId.value) return null
        return savedAddresses.find((a) => a.id === selectedAddressId.value) ?? null
    })

    const addressPayload = computed<AddressPayload | null>(() => {
        if (addressMode.value === 'saved') {
            if (!selectedAddress.value) return null
            return { address_mode: 'saved', address_id: selectedAddress.value.id }
        }

        return {
            address_mode: 'manual',
            recipient_name: recipientName.value.trim(),
            phone: phone.value.trim(),
            address_line: addressLine.value.trim(),
            province: selectedProvince.value,
            city: selectedCity.value,
            district: selectedDistrict.value.trim(),
            postal_code: postalCode.value.trim(),
            notes: notes.value.trim(),
        }
    })

    const isAddressValid = computed<boolean>(() => {
        if (addressMode.value === 'saved') return !!selectedAddress.value

        const p = addressPayload.value
        if (!p || p.address_mode !== 'manual') return false
        return !!(p.recipient_name && p.phone && p.address_line && p.province && p.city && p.postal_code)
    })

    return {
        addressMode,
        selectedAddressId,
        selectedAddress,
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
    }
}
