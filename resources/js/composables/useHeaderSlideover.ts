import { ref } from 'vue'

const cartSlideoverOpen = ref(false)
const wishlistSlideoverOpen = ref(false)

function openCartSlideover(): void {
    wishlistSlideoverOpen.value = false
    cartSlideoverOpen.value = true
}

function openWishlistSlideover(): void {
    cartSlideoverOpen.value = false
    wishlistSlideoverOpen.value = true
}

export function useHeaderSlideover() {
    return {
        cartSlideoverOpen,
        wishlistSlideoverOpen,
        openCartSlideover,
        openWishlistSlideover,
    }
}
