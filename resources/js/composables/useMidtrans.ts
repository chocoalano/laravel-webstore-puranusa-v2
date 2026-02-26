import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

declare global {
    interface Window {
        snap?: { pay: (token: string, opts?: Record<string, unknown>) => void }
    }
}

type MidtransEnv = 'sandbox' | 'production'

type CheckoutFlashPayload = {
    action?: string | null
    message?: string | null
    payload?: Record<string, unknown> | null
}

type InertiaSharedProps = {
    csrf_token?: string
    flash?: {
        checkout?: CheckoutFlashPayload | null
    }
}

function firstErrorMessage(errors: Record<string, string | string[] | undefined>): string {
    const first = Object.values(errors).find((value) => value !== undefined)

    if (Array.isArray(first)) {
        return first[0] ?? 'Validasi gagal.'
    }

    return first ?? 'Validasi gagal.'
}

export function useMidtrans(env: MidtransEnv, clientKey: string) {
    const page = usePage<InertiaSharedProps>()
    const isSubmitting = ref(false)
    const errorMessage = ref<string | null>(null)

    function getSnapSrc(): string {
        const host = env === 'production' ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com'

        return `${host}/snap/snap.js`
    }

    async function ensureSnapLoaded(): Promise<boolean> {
        if (window.snap?.pay) {
            return true
        }

        if (!clientKey) {
            return false
        }

        return new Promise((resolve) => {
            const existing = document.querySelector<HTMLScriptElement>('script[data-midtrans-snap="1"]')
            if (existing) {
                existing.addEventListener('load', () => resolve(!!window.snap?.pay))
                existing.addEventListener('error', () => resolve(false))
                return
            }

            const script = document.createElement('script')
            script.src = getSnapSrc()
            script.async = true
            script.setAttribute('data-midtrans-snap', '1')
            script.setAttribute('data-client-key', clientKey)
            script.onload = () => resolve(!!window.snap?.pay)
            script.onerror = () => resolve(false)
            document.head.appendChild(script)
        })
    }

    async function inertiaPost(
        url: string,
        payload: Record<string, unknown>
    ): Promise<InertiaSharedProps> {
        const csrfToken = String(page.props.csrf_token ?? '')

        return new Promise((resolve, reject) => {
            router.post(
                url,
                {
                    _token: csrfToken,
                    ...payload,
                },
                {
                    only: ['flash', 'errors'],
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    onSuccess: (nextPage) => {
                        const props = (nextPage?.props ?? {}) as InertiaSharedProps
                        resolve(props)
                    },
                    onError: (errors) => {
                        reject(new Error(firstErrorMessage(errors as Record<string, string | string[] | undefined>)))
                    },
                    onCancel: () => {
                        reject(new Error('Request dibatalkan.'))
                    },
                }
            )
        })
    }

    async function payViaMidtrans(payload: unknown): Promise<void> {
        errorMessage.value = null
        isSubmitting.value = true

        try {
            const requestPayload = typeof payload === 'object' && payload !== null
                ? (payload as Record<string, unknown>)
                : {}

            const response = await inertiaPost('/checkout/midtrans/token', requestPayload)
            const checkoutFlash = response.flash?.checkout
            const flashPayload = (checkoutFlash?.payload ?? {}) as {
                snapToken?: string
                successUrl?: string
                pendingUrl?: string
            }
            const snapToken = flashPayload.snapToken

            if (!snapToken) {
                throw new Error(checkoutFlash?.message ?? 'Snap token tidak ditemukan dari server.')
            }

            const snapOk = await ensureSnapLoaded()
            if (!snapOk) {
                throw new Error('Midtrans Snap gagal dimuat. Pastikan clientKey benar.')
            }

            isSubmitting.value = false

            window.snap?.pay(snapToken, {
                onSuccess: () => {
                    router.visit(flashPayload.successUrl ?? '/dashboard')
                },
                onPending: () => {
                    router.visit(flashPayload.pendingUrl ?? flashPayload.successUrl ?? '/dashboard')
                },
                onError: () => {
                    errorMessage.value = 'Pembayaran gagal. Silakan coba lagi.'
                },
                onClose: () => {
                    errorMessage.value = 'Pembayaran dibatalkan.'
                },
            })
        } catch (err: unknown) {
            errorMessage.value = err instanceof Error ? err.message : 'Terjadi kesalahan saat memproses pembayaran.'
        } finally {
            if (isSubmitting.value) {
                isSubmitting.value = false
            }
        }
    }

    async function payViaSaldo(payload: unknown): Promise<void> {
        errorMessage.value = null
        isSubmitting.value = true

        try {
            const requestPayload = typeof payload === 'object' && payload !== null
                ? (payload as Record<string, unknown>)
                : {}

            const response = await inertiaPost('/checkout/pay/saldo', requestPayload)
            const checkoutFlash = response.flash?.checkout
            const flashPayload = (checkoutFlash?.payload ?? {}) as { redirectTo?: string }

            router.visit(flashPayload.redirectTo ?? '/dashboard')
        } catch (err: unknown) {
            errorMessage.value = err instanceof Error ? err.message : 'Terjadi kesalahan saat memproses pembayaran.'
        } finally {
            isSubmitting.value = false
        }
    }

    return {
        isSubmitting,
        errorMessage,
        payViaMidtrans,
        payViaSaldo,
    }
}
