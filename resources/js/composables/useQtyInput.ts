import { ref, watch, type ComputedRef, type Ref } from 'vue'

/**
 * Composable untuk input jumlah (qty) dengan validasi min/max.
 * Otomatis mereset ke 1 ketika stok maksimum berubah (misal: ganti varian).
 */
export function useQtyInput(stockMax: ComputedRef<number> | Ref<number>) {
    const qty = ref(1)

    watch(stockMax, (newMax) => {
        if (qty.value > newMax) {
            qty.value = Math.max(1, newMax)
        }
    })

    function increaseQty(): void {
        if (qty.value < stockMax.value) qty.value++
    }

    function decreaseQty(): void {
        if (qty.value > 1) qty.value--
    }

    function onQtyInput(event: Event): void {
        const raw = parseInt((event.target as HTMLInputElement).value, 10)

        if (isNaN(raw) || raw < 1) {
            qty.value = 1
        } else if (raw > stockMax.value) {
            qty.value = stockMax.value
        } else {
            qty.value = raw
        }
    }

    return { qty, increaseQty, decreaseQty, onQtyInput }
}
