import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useStoreData } from '@/composables/useStoreData'

export function useWishlist(initialIsInWishlist = false) {
    const { isLoggedIn } = useStoreData()
    const isInWishlist = ref(initialIsInWishlist)
    const isToggling = ref(false)
    const justWishlisted = ref(false)

    /** Sync state jika prop dari server berubah (mis. setelah reload) */
    watch(
        () => initialIsInWishlist,
        (val) => {
            isInWishlist.value = val
        },
    )

    function toggleWishlist(productId: number | string): void {
        if (isToggling.value) return

        if (!isLoggedIn.value) {
            router.visit('/login')
            return
        }

        isToggling.value = true

        // Optimistic update
        const previousState = isInWishlist.value
        isInWishlist.value = !previousState

        if (isInWishlist.value) {
            justWishlisted.value = true
            setTimeout(() => {
                justWishlisted.value = false
            }, 2000)
        }

        router.post(
            '/wishlist/toggle',
            { product_id: productId },
            {
                preserveState: true,
                preserveScroll: true,
                onError: () => {
                    // Revert optimistic update jika gagal
                    isInWishlist.value = previousState
                },
                onFinish: () => {
                    isToggling.value = false
                },
            },
        )
    }

    return { isInWishlist, isToggling, justWishlisted, toggleWishlist }
}
