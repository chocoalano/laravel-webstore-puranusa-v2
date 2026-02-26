import { computed, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import type { FormError } from '@nuxt/ui'

export type LoginData = {
    username: string
    password: string
    remember: boolean
}

export type LoginValidationError = FormError<string>
export type LoginForm = ReturnType<typeof useForm<LoginData>>

export function useLoginForm() {
    const page = usePage()
    const toast = useToast()

    const form = useForm<LoginData>({
        username: '',
        password: '',
        remember: false,
    })

    const firstError = computed<string | undefined>(() => Object.values(form.errors ?? {})[0])
    const flashStatus = computed<string | undefined>(() => (page.props as any)?.status as string | undefined)

    watch(() => form.username, () => form.clearErrors('username'))
    watch(() => form.password, () => form.clearErrors('password'))

    function validate(state: Partial<LoginData>): LoginValidationError[] {
        const errors: LoginValidationError[] = []

        if (!state.username?.trim()) {
            errors.push({ name: 'username', message: 'Username wajib diisi.' })
        }
        if (!state.password) {
            errors.push({ name: 'password', message: 'Kata sandi wajib diisi.' })
        }

        return errors
    }

    function onSubmit(): void {
        form.post('/login', {
            preserveScroll: true,
            onFinish: () => form.reset('password'),
            onSuccess: () => {
                toast.add({
                    title: 'Berhasil masuk',
                    description: 'Selamat datang kembali, member Puranusa!',
                    color: 'success',
                })
            },
        })
    }

    return {
        form,
        firstError,
        flashStatus,
        validate,
        onSubmit,
    }
}
