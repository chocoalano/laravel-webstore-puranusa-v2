import { computed, provide, type ComputedRef, type InjectionKey, type Ref } from 'vue'
import {
    useProductDetail,
    type GalleryItem,
    type ProductData,
    type Recommendation,
    type Review,
    type Variant,
} from '@/composables/useProductDetail'
import { useCart } from '@/composables/useCart'
import { useQtyInput } from '@/composables/useQtyInput'
import { useShare } from '@/composables/useShare'
import { useWishlist } from '@/composables/useWishlist'

// ─── Tipe context yang di-provide ke seluruh komponen halaman produk ───────────

export type ProductPageContext = {
    // Data
    product: ComputedRef<ProductData>
    reviews: ComputedRef<Review[]>
    recommendations: ComputedRef<Recommendation[]>

    // Varian & galeri
    variants: ComputedRef<Variant[]>
    selectedVariantId: Ref<Variant['id'] | null>
    selectedVariant: ComputedRef<Variant | null>
    hasRealVariants: ComputedRef<boolean>

    // Harga & stok
    price: ComputedRef<number>
    compareAtPrice: ComputedRef<number | null>
    inStock: ComputedRef<boolean>
    discountPercent: ComputedRef<number | null>
    stockMax: ComputedRef<number>

    // Galeri
    galleryItems: ComputedRef<GalleryItem[]>
    activeImage: Ref<number>

    // Rating
    avgRating: ComputedRef<number>
    reviewCount: ComputedRef<number>

    // Jumlah pembelian
    qty: Ref<number>
    increaseQty: () => void
    decreaseQty: () => void
    onQtyInput: (event: Event) => void

    // Keranjang
    isAddingToCart: Ref<boolean>
    addedToCart: Ref<boolean>
    handleAddToCart: () => void

    // Wishlist
    isInWishlist: Ref<boolean>
    isToggling: Ref<boolean>
    justWishlisted: Ref<boolean>
    handleToggleWishlist: () => void

    // Share
    isSharing: Ref<boolean>
    handleShare: () => Promise<void>
}

export const PRODUCT_PAGE_KEY = Symbol('productPage') as InjectionKey<ProductPageContext>

// ─── Tipe props dari halaman Show.vue ─────────────────────────────────────────

export type ShowPageProps = {
    slug: string
    product?: ProductData | null
    reviews?: Review[]
    recommendations?: Recommendation[]
    isInWishlist?: boolean
}

// ─── Fallback data produk untuk development ───────────────────────────────────

function makeFallbackProduct(slug: string): ProductData {
    return {
        id: 1,
        slug,
        name: 'Produk Premium Puranusa',
        brand: 'Puranusa',
        shortDescription: 'Produk premium dengan kualitas terverifikasi, nyaman dipakai sehari-hari.',
        description: 'Deskripsi lengkap produk. Jelaskan manfaat, bahan, cara pakai, dan informasi penting lainnya.',
        rating: 4.8,
        reviewsCount: 128,
        highlights: ['Kualitas premium', 'Garansi resmi', 'Pengiriman cepat', 'Support ramah'],
        specs: [
            { label: 'Material', value: 'Premium Grade' },
            { label: 'Berat', value: '500g' },
            { label: 'Asal', value: 'Indonesia' },
            { label: 'Garansi', value: '7 hari' },
        ],
        media: [
            { url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=1400&auto=format&fit=crop', alt: 'Foto 1' },
            { url: 'https://images.unsplash.com/photo-1503602642458-232111445657?w=1400&auto=format&fit=crop', alt: 'Foto 2' },
            { url: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1400&auto=format&fit=crop', alt: 'Foto 3' },
            { url: 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=1400&auto=format&fit=crop', alt: 'Foto 4' },
        ],
        variants: [
            { id: 1, sku: 'PRN-DEFAULT', name: 'Default', price: 159000, inStock: true, stock: 24, options: [] },
        ],
        priceFrom: 159000,
    }
}

const FALLBACK_REVIEWS: Review[] = [
    { id: 1, name: 'Rani', rating: 5, title: 'Kualitasnya kerasa premium', body: 'Packing rapi, kualitas sesuai ekspektasi. Repeat order.', date: '2026-02-10', verified: true },
    { id: 2, name: 'Dimas', rating: 4, title: 'Bagus, pengiriman cepat', body: 'Sesuai deskripsi, semoga stok varian favorit cepat tersedia lagi.', date: '2026-02-03', verified: true },
    { id: 3, name: 'Sinta', rating: 5, title: 'Worth it', body: 'Harga sepadan, kualitas oke, dan admin responsif.', date: '2026-01-28' },
]

const FALLBACK_RECOMMENDATIONS: Recommendation[] = [
    { id: 1, slug: 'produk-1', name: 'Produk Rekomendasi 1', price: 129000, image: 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=1200&auto=format&fit=crop', rating: 4.7, reviewsCount: 81, badge: 'Diskon' },
    { id: 2, slug: 'produk-2', name: 'Produk Rekomendasi 2', price: 219000, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=1200&auto=format&fit=crop', rating: 4.8, reviewsCount: 112 },
    { id: 3, slug: 'produk-3', name: 'Produk Rekomendasi 3', price: 99000, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1200&auto=format&fit=crop', rating: 4.6, reviewsCount: 45, badge: 'Terlaris' },
    { id: 4, slug: 'produk-4', name: 'Produk Rekomendasi 4', price: 179000, image: 'https://images.unsplash.com/photo-1503602642458-232111445657?w=1200&auto=format&fit=crop', rating: 4.5, reviewsCount: 39 },
]

// ─── Composable utama halaman produk ──────────────────────────────────────────

/**
 * Orchestrates all state for the product Show page.
 * Wires together product data, variant selection, cart, wishlist, share, and qty.
 * Provides the full context via `provide()` so child components can inject what they need.
 */
export function useProductPage(props: ShowPageProps): ProductPageContext {
    // ── Data resolution ────────────────────────────────────────────────────────
    const product = computed<ProductData>(() => props.product ?? makeFallbackProduct(props.slug))

    const reviews = computed<Review[]>(() =>
        props.reviews?.length ? props.reviews : FALLBACK_REVIEWS,
    )

    const recommendations = computed<Recommendation[]>(() =>
        props.recommendations?.length ? props.recommendations : FALLBACK_RECOMMENDATIONS,
    )

    // ── Varian & galeri ───────────────────────────────────────────────────────
    const {
        variants,
        selectedVariantId,
        selectedVariant,
        price,
        compareAtPrice,
        inStock,
        discountPercent,
        galleryItems,
        activeImage,
        avgRating,
        reviewCount,
    } = useProductDetail(() => product.value)

    /** Hanya ada varian nyata jika > 1 varian, atau 1 varian dengan options terisi */
    const hasRealVariants = computed(
        () =>
            variants.value.length > 1 ||
            (variants.value.length === 1 && (variants.value[0]?.options?.length ?? 0) > 0),
    )

    // ── Jumlah stok maksimum ──────────────────────────────────────────────────
    const stockMax = computed(() => selectedVariant.value?.stock ?? 99)

    // ── Qty stepper ───────────────────────────────────────────────────────────
    const { qty, increaseQty, decreaseQty, onQtyInput } = useQtyInput(stockMax)

    // ── Keranjang ─────────────────────────────────────────────────────────────
    const { isAddingToCart, addedToCart, addToCart } = useCart()

    function handleAddToCart(): void {
        addToCart(product.value.id, qty.value)
    }

    // ── Wishlist ──────────────────────────────────────────────────────────────
    const { isInWishlist, isToggling, justWishlisted, toggleWishlist } = useWishlist(
        props.isInWishlist ?? false,
    )

    function handleToggleWishlist(): void {
        toggleWishlist(product.value.id)
    }

    // ── Share ─────────────────────────────────────────────────────────────────
    const { isSharing, share } = useShare()

    async function handleShare(): Promise<void> {
        await share(product.value.name)
    }

    // ── Context yang di-provide ───────────────────────────────────────────────
    const ctx: ProductPageContext = {
        product,
        reviews,
        recommendations,
        variants,
        selectedVariantId,
        selectedVariant,
        hasRealVariants,
        price,
        compareAtPrice,
        inStock,
        discountPercent,
        stockMax,
        galleryItems,
        activeImage,
        avgRating,
        reviewCount,
        qty,
        increaseQty,
        decreaseQty,
        onQtyInput,
        isAddingToCart,
        addedToCart,
        handleAddToCart,
        isInWishlist,
        isToggling,
        justWishlisted,
        handleToggleWishlist,
        isSharing,
        handleShare,
    }

    provide(PRODUCT_PAGE_KEY, ctx)

    return ctx
}
