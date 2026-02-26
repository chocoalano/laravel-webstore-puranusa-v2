import { computed, nextTick, reactive, ref, watch, type ComputedRef } from 'vue'
import { router } from '@inertiajs/vue3'
import type { DashboardAddress } from '@/types/dashboard'

export type DashboardAddressFormMode = 'create' | 'edit'

export type DashboardAddressSelectItem = {
    label: string
    value: number | string
}

export type DashboardAddressProvinceOption = {
    id: number
    label: string
}

export type DashboardAddressCityOption = {
    id: number
    province_id: number
    label: string
}

export type DashboardAddressDistrictOption = {
    province_id: number
    city_id: number
    label: string
    district_lion: string
}

export type DashboardAddressFormState = {
    label: string
    is_default: boolean
    recipient_name: string
    recipient_phone: string
    address_line1: string
    address_line2: string
    province_label: string
    province_id: number
    city_label: string
    city_id: number
    district: string
    district_lion: string
    postal_code: string
    country: string
    description: string
}

type ServerErrorValue = string | string[] | null | undefined

type UseDashboardAddressesOptions = {
    addresses: ComputedRef<DashboardAddress[]>
    provinces: ComputedRef<DashboardAddressProvinceOption[]>
    cities: ComputedRef<DashboardAddressCityOption[]>
    districts: ComputedRef<DashboardAddressDistrictOption[]>
}

function toErrorMessage(value: ServerErrorValue): string {
    if (Array.isArray(value)) {
        return value.map((item) => String(item)).join(' ')
    }

    return value ? String(value) : ''
}

export function useDashboardAddresses(options: UseDashboardAddressesOptions) {
    const formOpen = ref(false)
    const deleteOpen = ref(false)
    const blockedOpen = ref(false)

    const formMode = ref<DashboardAddressFormMode>('create')
    const submitting = ref(false)
    const deleting = ref(false)

    const settingDefault = ref<Record<string, boolean>>({})
    const selectedForEdit = ref<DashboardAddress | null>(null)
    const selectedForDelete = ref<DashboardAddress | null>(null)

    const errors = ref<Record<string, string>>({})
    const isHydratingForm = ref(false)

    const form = reactive<DashboardAddressFormState>({
        label: '',
        is_default: false,
        recipient_name: '',
        recipient_phone: '',
        address_line1: '',
        address_line2: '',
        province_label: '',
        province_id: 0,
        city_label: '',
        city_id: 0,
        district: '',
        district_lion: '',
        postal_code: '',
        country: 'Indonesia',
        description: '',
    })

    const provinceItems = computed<DashboardAddressSelectItem[]>(() =>
        options.provinces.value.map((province) => ({
            label: province.label,
            value: province.id,
        }))
    )

    const cityItems = computed<DashboardAddressSelectItem[]>(() => {
        const selectedProvinceId = Number(form.province_id || 0)

        if (!selectedProvinceId) {
            return []
        }

        return options.cities.value
            .filter((city) => Number(city.province_id) === selectedProvinceId)
            .map((city) => ({
                label: city.label,
                value: city.id,
            }))
    })

    const districtItems = computed<DashboardAddressSelectItem[]>(() => {
        const selectedProvinceId = Number(form.province_id || 0)
        const selectedCityId = Number(form.city_id || 0)

        if (!selectedProvinceId || !selectedCityId) {
            return []
        }

        return options.districts.value
            .filter(
                (district) =>
                    Number(district.province_id) === selectedProvinceId &&
                    Number(district.city_id) === selectedCityId
            )
            .map((district) => ({
                label: district.label,
                value: district.label,
            }))
    })

    const otherAddressesForDefault = computed<DashboardAddress[]>(() => {
        const currentAddress = selectedForDelete.value

        if (!currentAddress) {
            return []
        }

        return options.addresses.value.filter((address) => address.id !== currentAddress.id)
    })

    watch(
        () => form.province_id,
        (provinceIdRaw) => {
            if (isHydratingForm.value) {
                return
            }

            const provinceId = Number(provinceIdRaw || 0)

            if (!provinceId) {
                form.province_label = ''
                form.city_id = 0
                form.city_label = ''
                form.district = ''
                form.district_lion = ''

                return
            }

            const province = provinceItems.value.find((item) => item.value === provinceId)
            if (province) {
                form.province_label = province.label
            }

            form.city_id = 0
            form.city_label = ''
            form.district = ''
            form.district_lion = ''
        }
    )

    watch(
        () => form.city_id,
        (cityIdRaw) => {
            if (isHydratingForm.value) {
                return
            }

            const cityId = Number(cityIdRaw || 0)

            if (!cityId) {
                form.city_label = ''
                form.district = ''
                form.district_lion = ''

                return
            }

            const city = cityItems.value.find((item) => item.value === cityId)
            if (city) {
                form.city_label = city.label
            }

            form.district = ''
            form.district_lion = ''
        }
    )

    watch(
        () => form.district,
        (districtLabel) => {
            if (isHydratingForm.value) {
                return
            }

            const normalizedDistrict = districtLabel.trim()

            if (!normalizedDistrict || !form.city_id || !form.province_id) {
                form.district_lion = ''

                return
            }

            const district = options.districts.value.find(
                (item) =>
                    Number(item.province_id) === Number(form.province_id) &&
                    Number(item.city_id) === Number(form.city_id) &&
                    item.label === normalizedDistrict
            )

            form.district_lion = district?.district_lion ?? ''
        }
    )

    function clearErrors(): void {
        errors.value = {}
    }

    function resetForm(): void {
        form.label = ''
        form.is_default = false
        form.recipient_name = ''
        form.recipient_phone = ''
        form.address_line1 = ''
        form.address_line2 = ''
        form.province_label = ''
        form.province_id = 0
        form.city_label = ''
        form.city_id = 0
        form.district = ''
        form.district_lion = ''
        form.postal_code = ''
        form.country = 'Indonesia'
        form.description = ''
        clearErrors()
    }

    function fillForm(address: DashboardAddress): void {
        isHydratingForm.value = true

        form.label = address.label ?? ''
        form.is_default = !!address.is_default
        form.recipient_name = address.recipient_name ?? ''
        form.recipient_phone = address.recipient_phone ?? ''
        form.address_line1 = address.address_line1 ?? ''
        form.address_line2 = address.address_line2 ?? ''
        form.province_label = address.province_label ?? ''
        form.province_id = Number(address.province_id ?? 0)
        form.city_label = address.city_label ?? ''
        form.city_id = Number(address.city_id ?? 0)
        form.district = address.district ?? ''
        form.district_lion = address.district_lion ?? ''
        form.postal_code = address.postal_code ?? ''
        form.country = address.country ?? 'Indonesia'
        form.description = address.description ?? ''
        clearErrors()

        nextTick(() => {
            isHydratingForm.value = false
        })
    }

    function validate(): boolean {
        const validationErrors: Record<string, string> = {}

        if (!form.recipient_name.trim()) {
            validationErrors.recipient_name = 'Nama penerima wajib diisi.'
        }

        if (!form.recipient_phone.trim()) {
            validationErrors.recipient_phone = 'No. HP wajib diisi.'
        } else if (form.recipient_phone.replace(/\D/g, '').length < 8) {
            validationErrors.recipient_phone = 'No. HP terlalu pendek.'
        }

        if (!form.address_line1.trim()) {
            validationErrors.address_line1 = 'Alamat utama wajib diisi.'
        }

        if (!form.province_id) {
            validationErrors.province_id = 'Provinsi wajib dipilih/diisi.'
        }

        if (!form.city_id) {
            validationErrors.city_id = 'Kota/Kab wajib dipilih/diisi.'
        }

        if (!form.district.trim()) {
            validationErrors.district = 'Kecamatan wajib dipilih.'
        }

        if (form.postal_code && form.postal_code.trim().length > 0 && form.postal_code.trim().length < 5) {
            validationErrors.postal_code = 'Kode pos minimal 5 karakter.'
        }

        errors.value = validationErrors

        return Object.keys(validationErrors).length === 0
    }

    function normalizeErrors(serverErrors: Record<string, ServerErrorValue>): void {
        errors.value = Object.fromEntries(
            Object.entries(serverErrors ?? {})
                .map(([field, message]) => [field, toErrorMessage(message)])
                .filter((entry) => entry[1].length > 0)
        )
    }

    function reloadAddressData(): void {
        router.reload({
            only: ['addresses', 'defaultAddress', 'provinces', 'cities', 'districts'],
        })
    }

    function openCreate(): void {
        formMode.value = 'create'
        selectedForEdit.value = null
        resetForm()
        formOpen.value = true
    }

    function openEdit(address: DashboardAddress): void {
        formMode.value = 'edit'
        selectedForEdit.value = address
        fillForm(address)
        formOpen.value = true
    }

    function submitForm(): void {
        clearErrors()

        if (!validate()) {
            return
        }

        submitting.value = true

        const payload = {
            label: form.label || null,
            is_default: !!form.is_default,
            recipient_name: form.recipient_name,
            recipient_phone: form.recipient_phone,
            address_line1: form.address_line1,
            address_line2: form.address_line2 || null,
            province_label: form.province_label,
            province_id: Number(form.province_id),
            city_label: form.city_label,
            city_id: Number(form.city_id),
            district: form.district || null,
            district_lion: form.district_lion || null,
            postal_code: form.postal_code || null,
            country: form.country || 'Indonesia',
            description: form.description || null,
        }

        const onSuccess = (): void => {
            formOpen.value = false
            resetForm()
            reloadAddressData()
        }

        const onError = (serverErrors: Record<string, ServerErrorValue>): void => {
            normalizeErrors(serverErrors)
        }

        const onFinish = (): void => {
            submitting.value = false
        }

        if (formMode.value === 'create') {
            router.post('/account/addresses', payload, {
                preserveScroll: true,
                onSuccess,
                onError,
                onFinish,
            })

            return
        }

        const addressId = selectedForEdit.value?.id

        if (!addressId) {
            submitting.value = false

            return
        }

        router.put(`/account/addresses/${addressId}`, payload, {
            preserveScroll: true,
            onSuccess,
            onError,
            onFinish,
        })
    }

    function setAsDefault(address: DashboardAddress): void {
        const addressKey = String(address.id)
        settingDefault.value[addressKey] = true

        router.post(`/account/addresses/${address.id}/default`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                reloadAddressData()
            },
            onFinish: () => {
                settingDefault.value[addressKey] = false
            },
        })
    }

    function requestDelete(address: DashboardAddress): void {
        selectedForDelete.value = address

        if (address.is_default) {
            blockedOpen.value = true

            return
        }

        deleteOpen.value = true
    }

    function confirmDelete(): void {
        const address = selectedForDelete.value

        if (!address) {
            return
        }

        deleting.value = true

        router.delete(`/account/addresses/${address.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                deleteOpen.value = false
                selectedForDelete.value = null
                reloadAddressData()
            },
            onFinish: () => {
                deleting.value = false
            },
        })
    }

    function setDefaultThenContinueDelete(newDefaultAddress: DashboardAddress): void {
        if (!selectedForDelete.value) {
            return
        }

        const addressKey = String(newDefaultAddress.id)
        settingDefault.value[addressKey] = true

        router.post(`/account/addresses/${newDefaultAddress.id}/default`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                blockedOpen.value = false

                router.reload({
                    only: ['addresses', 'defaultAddress', 'provinces', 'cities', 'districts'],
                    onSuccess: () => {
                        nextTick(() => {
                            deleteOpen.value = true
                        })
                    },
                })
            },
            onFinish: () => {
                settingDefault.value[addressKey] = false
            },
        })
    }

    function closeBlockedAndOpenCreate(): void {
        blockedOpen.value = false

        nextTick(() => {
            openCreate()
        })
    }

    return {
        formOpen,
        deleteOpen,
        blockedOpen,
        formMode,
        submitting,
        deleting,
        settingDefault,
        selectedForDelete,
        otherAddressesForDefault,
        form,
        errors,
        provinceItems,
        cityItems,
        districtItems,
        resetForm,
        openCreate,
        openEdit,
        submitForm,
        setAsDefault,
        requestDelete,
        confirmDelete,
        setDefaultThenContinueDelete,
        closeBlockedAndOpenCreate,
    }
}
