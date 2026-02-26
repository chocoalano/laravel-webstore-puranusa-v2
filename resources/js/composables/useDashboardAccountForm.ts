import { computed, reactive, ref, watch, type ComputedRef } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import type { Customer } from '@/types/dashboard'

type ServerErrorValue = string | string[] | null | undefined

type AccountFlashPayload = {
    action?: string | null
    message?: string | null
}

type InertiaSharedProps = {
    csrf_token?: string
    flash?: {
        account?: AccountFlashPayload | null
    }
}

type UseDashboardAccountFormOptions = {
    customer: ComputedRef<Customer | null | undefined>
}

export type DashboardAccountGenderOption = {
    label: string
    value: string
}

export type DashboardAccountFormState = {
    username: string
    name: string
    nik: string
    gender: string
    email: string
    phone: string
    bank_name: string
    bank_account: string
    npwp_nama: string
    npwp_number: string
    npwp_jk: string
    npwp_date: string
    npwp_alamat: string
    npwp_menikah: string
    npwp_anak: string
    npwp_kerja: string
    npwp_office: string
}

function normalizeGender(value: string | null | undefined): string {
    const normalized = (value ?? '').trim().toUpperCase()

    if (['L', 'LAKI-LAKI', 'MALE', 'M'].includes(normalized)) {
        return 'L'
    }

    if (['P', 'PEREMPUAN', 'FEMALE', 'F'].includes(normalized)) {
        return 'P'
    }

    return 'L'
}

function normalizeNpwpGender(value: number | string | null | undefined): string {
    const raw = typeof value === 'number' ? String(value) : (value ?? '').trim()

    if (raw === '1' || raw.toUpperCase() === 'L') {
        return '1'
    }

    if (raw === '2' || raw.toUpperCase() === 'P') {
        return '2'
    }

    return ''
}

function normalizeNpwpFlag(value: string | null | undefined): string {
    const normalized = (value ?? '').trim().toUpperCase()

    if (normalized === 'Y' || normalized === 'N') {
        return normalized
    }

    return ''
}

function toErrorMessage(value: ServerErrorValue): string {
    if (Array.isArray(value)) {
        return value.map((item) => String(item)).join(' ')
    }

    return value ? String(value) : ''
}

function firstErrorMessage(errors: Record<string, string | string[] | undefined>): string {
    const first = Object.values(errors).find((value) => value !== undefined)

    if (Array.isArray(first)) {
        return first[0] ?? 'Gagal memperbarui profil akun.'
    }

    return first ?? 'Gagal memperbarui profil akun.'
}

export function useDashboardAccountForm(options: UseDashboardAccountFormOptions) {
    const toast = useToast()
    const page = usePage<InertiaSharedProps>()

    const submitting = ref(false)
    const errors = ref<Record<string, string>>({})

    const genderItems = computed<DashboardAccountGenderOption[]>(() => [
        { label: 'Laki-laki', value: 'L' },
        { label: 'Perempuan', value: 'P' },
    ])

    const npwpGenderItems = computed<DashboardAccountGenderOption[]>(() => [
        { label: 'Laki-laki', value: '1' },
        { label: 'Perempuan', value: '2' },
    ])

    const yesNoItems = computed<DashboardAccountGenderOption[]>(() => [
        { label: 'Ya', value: 'Y' },
        { label: 'Tidak', value: 'N' },
    ])

    const form = reactive<DashboardAccountFormState>({
        username: '',
        name: '',
        nik: '',
        gender: 'L',
        email: '',
        phone: '',
        bank_name: '',
        bank_account: '',
        npwp_nama: '',
        npwp_number: '',
        npwp_jk: '',
        npwp_date: '',
        npwp_alamat: '',
        npwp_menikah: '',
        npwp_anak: '',
        npwp_kerja: '',
        npwp_office: '',
    })

    watch(
        options.customer,
        (customer) => {
            form.username = customer?.username ?? ''
            form.name = customer?.name ?? ''
            form.nik = customer?.nik ?? ''
            form.gender = normalizeGender(customer?.gender)
            form.email = customer?.email ?? ''
            form.phone = customer?.phone ?? ''
            form.bank_name = customer?.bank_name ?? ''
            form.bank_account = customer?.bank_account ?? ''
            form.npwp_nama = customer?.npwp?.nama ?? ''
            form.npwp_number = customer?.npwp?.npwp ?? ''
            form.npwp_jk = normalizeNpwpGender(customer?.npwp?.jk)
            form.npwp_date = customer?.npwp?.npwp_date ?? ''
            form.npwp_alamat = customer?.npwp?.alamat ?? ''
            form.npwp_menikah = normalizeNpwpFlag(customer?.npwp?.menikah)
            form.npwp_anak = customer?.npwp?.anak ?? ''
            form.npwp_kerja = normalizeNpwpFlag(customer?.npwp?.kerja)
            form.npwp_office = customer?.npwp?.office ?? ''
        },
        { immediate: true }
    )

    function clearErrors(): void {
        errors.value = {}
    }

    function resetToCurrentCustomerData(): void {
        const customer = options.customer.value

        form.username = customer?.username ?? ''
        form.name = customer?.name ?? ''
        form.nik = customer?.nik ?? ''
        form.gender = normalizeGender(customer?.gender)
        form.email = customer?.email ?? ''
        form.phone = customer?.phone ?? ''
        form.bank_name = customer?.bank_name ?? ''
        form.bank_account = customer?.bank_account ?? ''
        form.npwp_nama = customer?.npwp?.nama ?? ''
        form.npwp_number = customer?.npwp?.npwp ?? ''
        form.npwp_jk = normalizeNpwpGender(customer?.npwp?.jk)
        form.npwp_date = customer?.npwp?.npwp_date ?? ''
        form.npwp_alamat = customer?.npwp?.alamat ?? ''
        form.npwp_menikah = normalizeNpwpFlag(customer?.npwp?.menikah)
        form.npwp_anak = customer?.npwp?.anak ?? ''
        form.npwp_kerja = normalizeNpwpFlag(customer?.npwp?.kerja)
        form.npwp_office = customer?.npwp?.office ?? ''
        clearErrors()
    }

    function validate(): boolean {
        const validationErrors: Record<string, string> = {}
        const username = form.username.trim()
        const name = form.name.trim()
        const nik = form.nik.replace(/\D/g, '')
        const gender = form.gender.trim().toUpperCase()
        const email = form.email.trim()
        const phone = form.phone.replace(/\s+/g, '')
        const bankName = form.bank_name.trim()
        const bankAccount = form.bank_account.replace(/\D/g, '')
        const npwpNama = form.npwp_nama.trim()
        const npwpNumber = form.npwp_number.trim()
        const npwpJk = form.npwp_jk.trim()
        const npwpDate = form.npwp_date.trim()
        const npwpAlamat = form.npwp_alamat.trim()
        const npwpMenikah = form.npwp_menikah.trim().toUpperCase()
        const npwpAnak = form.npwp_anak.trim()
        const npwpKerja = form.npwp_kerja.trim().toUpperCase()
        const npwpOffice = form.npwp_office.trim()

        if (!/^[a-zA-Z0-9_.]{3,30}$/.test(username)) {
            validationErrors.username = 'Username 3-30 karakter dan hanya huruf/angka/underscore/titik.'
        }

        if (name.length === 0) {
            validationErrors.name = 'Nama lengkap wajib diisi.'
        }

        if (!/^\d{16}$/.test(nik)) {
            validationErrors.nik = 'NIK harus 16 digit angka.'
        }

        if (!['L', 'P'].includes(gender)) {
            validationErrors.gender = 'Jenis kelamin wajib dipilih.'
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            validationErrors.email = 'Format email tidak valid.'
        }

        if (!/^[0-9+]{8,20}$/.test(phone)) {
            validationErrors.phone = 'Nomor telepon/WhatsApp harus 8-20 karakter (angka atau +).'
        }

        if (bankName.length === 0) {
            validationErrors.bank_name = 'Nama bank wajib diisi.'
        }

        if (!/^\d{5,50}$/.test(bankAccount)) {
            validationErrors.bank_account = 'Nomor rekening harus 5-50 digit angka.'
        }

        if (npwpNumber.length > 0 && !/^[0-9.\-]{10,30}$/.test(npwpNumber)) {
            validationErrors.npwp_number = 'Nomor NPWP tidak valid.'
        }

        if (npwpJk.length > 0 && !['1', '2'].includes(npwpJk)) {
            validationErrors.npwp_jk = 'Jenis kelamin NPWP tidak valid.'
        }

        if (npwpDate.length > 0 && Number.isNaN(Date.parse(npwpDate))) {
            validationErrors.npwp_date = 'Tanggal NPWP tidak valid.'
        }

        if (npwpMenikah.length > 0 && !['Y', 'N'].includes(npwpMenikah)) {
            validationErrors.npwp_menikah = 'Status pernikahan NPWP tidak valid.'
        }

        if (npwpKerja.length > 0 && !['Y', 'N'].includes(npwpKerja)) {
            validationErrors.npwp_kerja = 'Status kerja NPWP tidak valid.'
        }

        if (npwpAnak.length > 0 && !/^\d{1,2}$/.test(npwpAnak)) {
            validationErrors.npwp_anak = 'Jumlah anak NPWP harus berupa angka.'
        }

        if (npwpNama.length > 255) {
            validationErrors.npwp_nama = 'Nama NPWP maksimal 255 karakter.'
        }

        if (npwpAlamat.length > 1000) {
            validationErrors.npwp_alamat = 'Alamat NPWP maksimal 1000 karakter.'
        }

        if (npwpOffice.length > 255) {
            validationErrors.npwp_office = 'Nama kantor maksimal 255 karakter.'
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

    function submit(): void {
        clearErrors()

        if (!validate()) {
            return
        }

        submitting.value = true

        router.post('/dashboard/account/profile', {
            _token: page.props.csrf_token,
            username: form.username.trim().toLowerCase(),
            name: form.name.trim(),
            nik: form.nik.replace(/\D/g, ''),
            gender: form.gender.trim().toUpperCase(),
            email: form.email.trim().toLowerCase(),
            phone: form.phone.replace(/\s+/g, ''),
            bank_name: form.bank_name.trim(),
            bank_account: form.bank_account.replace(/\D/g, ''),
            npwp_nama: form.npwp_nama.trim(),
            npwp_number: form.npwp_number.trim(),
            npwp_jk: form.npwp_jk ? Number(form.npwp_jk) : null,
            npwp_date: form.npwp_date.trim(),
            npwp_alamat: form.npwp_alamat.trim(),
            npwp_menikah: form.npwp_menikah.trim().toUpperCase(),
            npwp_anak: form.npwp_anak.trim(),
            npwp_kerja: form.npwp_kerja.trim().toUpperCase(),
            npwp_office: form.npwp_office.trim(),
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                const flashMessage = page.props.flash?.account?.message
                const message = typeof flashMessage === 'string' && flashMessage.length > 0
                    ? flashMessage
                    : 'Profil akun berhasil diperbarui.'

                toast.add({
                    title: 'Berhasil',
                    description: message,
                    color: 'success',
                })
            },
            onError: (serverErrors) => {
                normalizeErrors(serverErrors as Record<string, ServerErrorValue>)
                toast.add({
                    title: 'Gagal',
                    description: firstErrorMessage(serverErrors as Record<string, string | string[] | undefined>),
                    color: 'error',
                })
            },
            onFinish: () => {
                submitting.value = false
            },
        })
    }

    return {
        form,
        errors,
        submitting,
        genderItems,
        npwpGenderItems,
        yesNoItems,
        submit,
        resetToCurrentCustomerData,
    }
}
