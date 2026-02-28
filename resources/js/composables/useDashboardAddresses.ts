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
    id: number
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
}

function toErrorMessage(value: ServerErrorValue): string {
    if (Array.isArray(value)) {
        return value.map((item) => String(item)).join(' ')
    }

    return value ? String(value) : ''
}

function toPositiveInt(value: unknown): number {
    const numeric = Number(value)

    if (!Number.isFinite(numeric) || numeric <= 0) {
        return 0
    }

    return Math.trunc(numeric)
}

function toRecord(value: unknown): Record<string, unknown> | null {
    if (value && typeof value === 'object' && !Array.isArray(value)) {
        return value as Record<string, unknown>
    }

    return null
}

function toTrimmedString(value: unknown): string {
    return typeof value === 'string' ? value.trim() : ''
}

function normalizeCityLabelForDistrictLion(cityLabel: string): string {
    return cityLabel.replace(/^(kota|kabupaten)\s+/i, '').trim()
}

function formatDistrictLion(districtLabel: string, cityLabel: string): string {
    const normalizedDistrict = districtLabel.trim().toUpperCase()

    if (normalizedDistrict === '') {
        return ''
    }

    const normalizedCity = normalizeCityLabelForDistrictLion(cityLabel).toUpperCase()

    if (normalizedCity === '') {
        return normalizedDistrict
    }

    return `${normalizedDistrict}, ${normalizedCity}`
}

async function fetchOptions(url: string): Promise<unknown[]> {
    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            return []
        }

        const payload: unknown = await response.json()

        return Array.isArray(payload) ? payload : []
    } catch {
        return []
    }
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

    const loadingProvinces = ref(false)
    const loadingCities = ref(false)
    const loadingDistricts = ref(false)

    const provinceOptions = ref<DashboardAddressProvinceOption[]>([])
    const cityOptions = ref<DashboardAddressCityOption[]>([])
    const districtOptions = ref<DashboardAddressDistrictOption[]>([])

    const cityRequestIndex = ref(0)
    const districtRequestIndex = ref(0)

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
        provinceOptions.value.map((province) => ({
            label: province.label,
            value: province.id,
        }))
    )

    const cityItems = computed<DashboardAddressSelectItem[]>(() =>
        cityOptions.value.map((city) => ({
            label: city.label,
            value: city.id,
        }))
    )

    const districtItems = computed<DashboardAddressSelectItem[]>(() =>
        districtOptions.value.map((district) => ({
            label: district.label,
            value: district.label,
        }))
    )

    const otherAddressesForDefault = computed<DashboardAddress[]>(() => {
        const currentAddress = selectedForDelete.value

        if (!currentAddress) {
            return []
        }

        return options.addresses.value.filter((address) => address.id !== currentAddress.id)
    })

    async function loadProvinceOptions(): Promise<void> {
        loadingProvinces.value = true

        const rows = await fetchOptions('/account/addresses/options/provinces')

        provinceOptions.value = rows
            .map((row) => {
                const record = toRecord(row)

                if (!record) {
                    return null
                }

                const id = toPositiveInt(record.id)
                const label = toTrimmedString(record.label)

                if (!id || label === '') {
                    return null
                }

                return {
                    id,
                    label,
                } satisfies DashboardAddressProvinceOption
            })
            .filter((item): item is DashboardAddressProvinceOption => item !== null)

        loadingProvinces.value = false
    }

    async function loadCityOptions(provinceIdRaw: number): Promise<void> {
        const provinceId = toPositiveInt(provinceIdRaw)
        cityRequestIndex.value += 1
        const currentRequest = cityRequestIndex.value

        cityOptions.value = []
        districtOptions.value = []

        if (!provinceId) {
            loadingCities.value = false
            loadingDistricts.value = false

            return
        }

        loadingCities.value = true

        const rows = await fetchOptions(`/account/addresses/options/cities?province_id=${provinceId}`)

        if (currentRequest !== cityRequestIndex.value) {
            return
        }

        cityOptions.value = rows
            .map((row) => {
                const record = toRecord(row)

                if (!record) {
                    return null
                }

                const id = toPositiveInt(record.id)
                const provinceIdFromApi = toPositiveInt(record.province_id)
                const label = toTrimmedString(record.label)

                if (!id || !provinceIdFromApi || label === '') {
                    return null
                }

                return {
                    id,
                    province_id: provinceIdFromApi,
                    label,
                } satisfies DashboardAddressCityOption
            })
            .filter((item): item is DashboardAddressCityOption => item !== null)

        loadingCities.value = false
    }

    async function loadDistrictOptions(cityIdRaw: number): Promise<void> {
        const cityId = toPositiveInt(cityIdRaw)
        districtRequestIndex.value += 1
        const currentRequest = districtRequestIndex.value

        districtOptions.value = []

        if (!cityId) {
            loadingDistricts.value = false

            return
        }

        loadingDistricts.value = true

        const rows = await fetchOptions(`/account/addresses/options/districts?city_id=${cityId}`)

        if (currentRequest !== districtRequestIndex.value) {
            return
        }

        districtOptions.value = rows
            .map((row) => {
                const record = toRecord(row)

                if (!record) {
                    return null
                }

                const id = toPositiveInt(record.id)
                const cityIdFromApi = toPositiveInt(record.city_id)
                const label = toTrimmedString(record.label)

                if (!id || !cityIdFromApi || label === '') {
                    return null
                }

                return {
                    id,
                    city_id: cityIdFromApi,
                    label,
                    district_lion: formatDistrictLion(label, form.city_label),
                } satisfies DashboardAddressDistrictOption
            })
            .filter((item): item is DashboardAddressDistrictOption => item !== null)

        loadingDistricts.value = false
    }

    watch(
        () => form.province_id,
        (provinceIdRaw) => {
            if (isHydratingForm.value) {
                return
            }

            const provinceId = toPositiveInt(provinceIdRaw)

            if (!provinceId) {
                form.province_label = ''
                form.city_id = 0
                form.city_label = ''
                form.district = ''
                form.district_lion = ''
                cityOptions.value = []
                districtOptions.value = []

                return
            }

            const province = provinceOptions.value.find((item) => item.id === provinceId)
            if (province) {
                form.province_label = province.label
            }

            form.city_id = 0
            form.city_label = ''
            form.district = ''
            form.district_lion = ''

            void loadCityOptions(provinceId)
        }
    )

    watch(
        () => form.city_id,
        (cityIdRaw) => {
            if (isHydratingForm.value) {
                return
            }

            const cityId = toPositiveInt(cityIdRaw)

            if (!cityId) {
                form.city_label = ''
                form.district = ''
                form.district_lion = ''
                districtOptions.value = []

                return
            }

            const city = cityOptions.value.find((item) => item.id === cityId)
            if (city) {
                form.city_label = city.label
            }

            form.district = ''
            form.district_lion = ''

            void loadDistrictOptions(cityId)
        }
    )

    watch(
        () => form.district,
        (districtLabel) => {
            if (isHydratingForm.value) {
                return
            }

            const normalizedDistrict = districtLabel.trim()

            if (!normalizedDistrict || !form.city_id) {
                form.district_lion = ''

                return
            }

            const district = districtOptions.value.find((item) => item.label === normalizedDistrict)

            form.district_lion = district?.district_lion ?? formatDistrictLion(normalizedDistrict, form.city_label)
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
        cityOptions.value = []
        districtOptions.value = []
        clearErrors()
    }

    async function fillForm(address: DashboardAddress): Promise<void> {
        isHydratingForm.value = true

        form.label = address.label ?? ''
        form.is_default = !!address.is_default
        form.recipient_name = address.recipient_name ?? ''
        form.recipient_phone = address.recipient_phone ?? ''
        form.address_line1 = address.address_line1 ?? ''
        form.address_line2 = address.address_line2 ?? ''
        form.province_label = address.province_label ?? ''
        form.province_id = toPositiveInt(address.province_id ?? 0)
        form.city_label = address.city_label ?? ''
        form.city_id = toPositiveInt(address.city_id ?? 0)
        form.district = address.district ?? ''
        form.district_lion = formatDistrictLion(address.district ?? '', address.city_label ?? '')
        form.postal_code = address.postal_code ?? ''
        form.country = address.country ?? 'Indonesia'
        form.description = address.description ?? ''
        clearErrors()

        await loadProvinceOptions()

        if (form.province_id) {
            await loadCityOptions(form.province_id)
        }

        if (form.city_id) {
            await loadDistrictOptions(form.city_id)
        }

        if (form.province_label.trim() === '') {
            const province = provinceOptions.value.find((item) => item.id === form.province_id)
            form.province_label = province?.label ?? ''
        }

        if (form.city_label.trim() === '') {
            const city = cityOptions.value.find((item) => item.id === form.city_id)
            form.city_label = city?.label ?? ''
        }

        if (form.district.trim() !== '') {
            const district = districtOptions.value.find((item) => item.label === form.district)
            form.district_lion = district?.district_lion ?? formatDistrictLion(form.district, form.city_label)
        }

        await nextTick()
        isHydratingForm.value = false
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
            validationErrors.province_id = 'Provinsi wajib dipilih.'
        }

        if (!form.province_label.trim()) {
            validationErrors.province_label = 'Label provinsi wajib diisi dari opsi.'
        }

        if (!form.city_id) {
            validationErrors.city_id = 'Kota/Kab wajib dipilih.'
        }

        if (!form.city_label.trim()) {
            validationErrors.city_label = 'Label kota wajib diisi dari opsi.'
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
            only: ['addresses', 'defaultAddress'],
        })
    }

    function openCreate(): void {
        formMode.value = 'create'
        selectedForEdit.value = null
        resetForm()
        formOpen.value = true
        void loadProvinceOptions()
    }

    function openEdit(address: DashboardAddress): void {
        formMode.value = 'edit'
        selectedForEdit.value = address
        formOpen.value = true
        void fillForm(address)
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
            province_label: form.province_label.trim(),
            province_id: toPositiveInt(form.province_id),
            city_label: form.city_label.trim(),
            city_id: toPositiveInt(form.city_id),
            district: form.district.trim(),
            district_lion: formatDistrictLion(form.district, form.city_label) || null,
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
                    only: ['addresses', 'defaultAddress'],
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
        loadingProvinces,
        loadingCities,
        loadingDistricts,
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
