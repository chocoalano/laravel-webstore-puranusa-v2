import { computed, ref, watch } from 'vue'

export type Media = { url: string; alt?: string }
export type VariantOption = { name: string; value: string; badge?: string }
export type Variant = {
    id: number | string
    sku?: string
    name: string
    price: number
    compareAtPrice?: number
    inStock: boolean
    stock?: number
    media?: Media[]
    options: VariantOption[]
}

export type ProductSpec = { label: string; value: string }
export type Review = {
    id: number | string
    name: string
    rating: number
    title?: string
    body: string
    date: string
    verified?: boolean
}

export type Recommendation = {
    id: number | string
    slug: string
    name: string
    price: number
    image?: string | null
    rating?: number
    reviewsCount?: number
    badge?: string | null
}

export type ProductData = {
    id: number | string
    slug: string
    name: string
    brand?: string
    shortDescription?: string
    description?: string
    priceFrom?: number
    rating?: number
    reviewsCount?: number
    highlights?: string[]
    specs?: ProductSpec[]
    media?: Media[]
    variants?: Variant[]
}

export type GalleryItem = { src: string; alt: string }

export function resolveMediaUrl(url: string | null | undefined): string | null {
    if (!url) return null
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('//') || url.startsWith('/')) return url
    return `/storage/${url}`
}

export function formatCurrency(val: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val)
}

export function starsArray(rating: number): boolean[] {
    const full = Math.floor(rating)
    return Array.from({ length: 5 }, (_, i) => i < full)
}

export function useProductDetail(product: () => ProductData) {
    const variants = computed<Variant[]>(() => product().variants ?? [])
    const selectedVariantId = ref<Variant['id'] | null>(variants.value[0]?.id ?? null)

    watch(
        () => variants.value,
        (v) => {
            if (!selectedVariantId.value && v?.length) selectedVariantId.value = v[0].id
        },
        { immediate: true },
    )

    const selectedVariant = computed<Variant | null>(() => {
        if (!variants.value.length) return null
        return variants.value.find((v) => v.id === selectedVariantId.value) ?? variants.value[0]
    })

    const price = computed(() => Number(selectedVariant.value?.price ?? product().priceFrom ?? 0))
    const compareAtPrice = computed(() => {
        const v = selectedVariant.value?.compareAtPrice
        return v != null ? Number(v) : null
    })
    const inStock = computed(() => selectedVariant.value?.inStock ?? true)

    const discountPercent = computed(() => {
        if (!compareAtPrice.value || compareAtPrice.value <= price.value) return null
        return Math.round(((compareAtPrice.value - price.value) / compareAtPrice.value) * 100)
    })

    const galleryItems = computed<GalleryItem[]>(() => {
        const p = product()

        const base = (p.media ?? []).map((m) => ({
            src: resolveMediaUrl(m.url) ?? '',
            alt: m.alt ?? p.name,
        }))

        const variantMedia = (selectedVariant.value?.media ?? []).map((m) => ({
            src: resolveMediaUrl(m.url) ?? '',
            alt: m.alt ?? selectedVariant.value?.name ?? p.name,
        }))

        const merged = [...variantMedia, ...base]
        const seen = new Set<string>()
        return merged.filter((x) => x.src && (seen.has(x.src) ? false : (seen.add(x.src), true)))
    })

    const activeImage = ref(0)
    watch(galleryItems, () => (activeImage.value = 0))

    const avgRating = computed(() => product().rating ?? 0)
    const reviewCount = computed(() => product().reviewsCount ?? 0)

    return {
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
    }
}
