import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

export function useCart() {
    const isAddingToCart = ref(false)
    const addedToCart = ref(false)

    function addToCart(productId: number | string, qty: number): void {
        if (isAddingToCart.value) return

        isAddingToCart.value = true

        router.post(
            '/cart/add',
            { product_id: productId, qty },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    addedToCart.value = true
                    setTimeout(() => {
                        addedToCart.value = false
                    }, 2500)
                },
                onFinish: () => {
                    isAddingToCart.value = false
                },
            },
        )
    }

    return { isAddingToCart, addedToCart, addToCart }
}
