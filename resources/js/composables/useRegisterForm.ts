import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import type { FormError } from '@nuxt/ui'

export type RegisterData = {
    name: string
    username: string
    email: string
    telp: string
    nik: string
    gender: 'L' | 'P' | ''
    alamat: string
    referral_code: string
    password: string
    password_confirmation: string
    terms: boolean
}

export type RegisterValidationError = FormError<string>
export type RegisterForm = ReturnType<typeof useForm<RegisterData>>

export function useRegisterForm(referralCode?: string) {
    const toast = useToast()

    const form = useForm<RegisterData>({
        name: '',
        username: '',
        email: '',
        telp: '',
        nik: '',
        gender: '',
        alamat: '',
        referral_code: referralCode ?? '',
        password: '',
        password_confirmation: '',
        terms: false,
    })

    const firstError = computed<string | undefined>(() => Object.values(form.errors ?? {})[0])

    const watchedFields: Array<keyof RegisterData> = [
        'name',
        'username',
        'email',
        'telp',
        'gender',
        'password',
        'password_confirmation',
        'terms',
    ]

    for (const field of watchedFields) {
        watch(() => form[field], () => form.clearErrors(field))
    }

    function validate(state: Partial<RegisterData>): RegisterValidationError[] {
        const errors: RegisterValidationError[] = []

        const email = String(state.email ?? '').trim()
        const telp = String(state.telp ?? '').replace(/\s+/g, '')
        const username = String(state.username ?? '').trim()
        const password = String(state.password ?? '')
        const passwordConfirmation = String(state.password_confirmation ?? '')

        if (!String(state.name ?? '').trim()) {
            errors.push({ name: 'name', message: 'Nama wajib diisi.' })
        }

        if (!username) {
            errors.push({ name: 'username', message: 'Username wajib diisi.' })
        } else if (!/^[a-zA-Z0-9_.]{3,30}$/.test(username)) {
            errors.push({
                name: 'username',
                message: 'Username minimal 3 karakter (huruf/angka/underscore/titik).',
            })
        }

        if (!email) {
            errors.push({ name: 'email', message: 'Email wajib diisi.' })
        } else if (!/^\S+@\S+\.\S+$/.test(email)) {
            errors.push({ name: 'email', message: 'Format email tidak valid.' })
        }

        if (!telp) {
            errors.push({ name: 'telp', message: 'Nomor WhatsApp wajib diisi.' })
        } else if (!/^[0-9+]{8,16}$/.test(telp)) {
            errors.push({ name: 'telp', message: 'Nomor WhatsApp tidak valid.' })
        }

        if (state.gender !== 'L' && state.gender !== 'P') {
            errors.push({ name: 'gender', message: 'Silakan pilih jenis kelamin.' })
        }

        if (!password || password.length < 8) {
            errors.push({ name: 'password', message: 'Kata sandi minimal 8 karakter.' })
        }

        if (password !== passwordConfirmation) {
            errors.push({ name: 'password_confirmation', message: 'Konfirmasi kata sandi tidak cocok.' })
        }

        if (!state.terms) {
            errors.push({ name: 'terms', message: 'Anda harus menyetujui Syarat & Ketentuan.' })
        }

        return errors
    }

    function onSubmit(): void {
        form.clearErrors()

        form.post('/register', {
            preserveScroll: true,
            onError: () => {
                toast.add({
                    title: 'Pendaftaran gagal',
                    description: String(firstError.value ?? 'Periksa kembali data yang Anda isi.'),
                    color: 'error',
                })
            },
            onSuccess: () => {
                toast.add({
                    title: 'Berhasil!',
                    description: 'Akun Anda berhasil dibuat. Selamat bergabung!',
                    color: 'success',
                })
            },
            onFinish: () => form.reset('password', 'password_confirmation'),
        })
    }

    return {
        form,
        firstError,
        validate,
        onSubmit,
    }
}
