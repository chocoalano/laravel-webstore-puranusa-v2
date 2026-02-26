import { ref } from 'vue'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'

/**
 * Composable untuk fitur berbagi produk.
 * Menggunakan Web Share API jika tersedia, fallback ke clipboard.
 */
export function useShare() {
    const toast = useToast()
    const isSharing = ref(false)

    async function share(title: string): Promise<void> {
        if (isSharing.value) return

        isSharing.value = true
        const url = window.location.href

        try {
            if (navigator.share) {
                await navigator.share({ title, url })
            } else {
                await navigator.clipboard.writeText(url)
                toast.add({
                    title: 'Link disalin!',
                    description: 'Link produk berhasil disalin ke clipboard.',
                    color: 'success',
                })
            }
        } catch {
            // User membatalkan share dialog — tidak perlu aksi
        } finally {
            isSharing.value = false
        }
    }

    return { isSharing, share }
}
