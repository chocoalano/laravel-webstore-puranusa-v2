import { ref, computed, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import { formatCurrency } from '@/composables/useProductDetail'

export interface ShopCategory {
    id: number
    name: string
    slug: string
    products_count?: number
}

export interface ShopBrand {
    id: number
    name: string
    slug: string
    products_count?: number
}

export interface ShopProduct {
    id: number
    slug: string
    name: string
    base_price: number
    primary_media?: { url: string }[]
    avg_rating?: number
    reviews_count?: number
}

export interface ShopFilters {
    category?: string
    brand?: string
    products?: string
    search?: string
    sort?: string
    min_price?: number
    max_price?: number
    rating?: number
    in_stock?: boolean
}

export interface FilterStats {
    min_price: number
    max_price: number
    ratings: number[]
}

export interface ShopProps {
    products: { data: ShopProduct[]; next_page_url: string | null; current_page: number; total: number }
    categories: ShopCategory[]
    brands?: ShopBrand[]
    filterStats: FilterStats
    filters: ShopFilters
}

function resolveProductImage(url: string | undefined | null): string | null {
    if (!url) return null
    return url.startsWith('http') ? url : `/storage/${url}`
}

export function transformProduct(p: ShopProduct) {
    const rawImage = p.primary_media?.[0]?.url
    return {
        ...p,
        price: Number(p.base_price),
        image: resolveProductImage(rawImage),
        rating: Number(p.avg_rating) || 0,
        reviewCount: p.reviews_count || 0,
        salesCount: Math.floor(Math.random() * 500) + 50,
        badge: p.id % 3 === 0 ? 'Terlaris' : p.id % 5 === 0 ? 'Baru' : null,
    }
}

export function useShopCatalog(props: ShopProps) {
    // ── Sync guard (prevents watcher → handleFilter → onSuccess → watcher loop) ──
    let _syncing = false

    // ── Product state ──
    const allProducts = ref<ShopProduct[]>([...props.products.data])
    const nextPageUrl = ref<string | null>(props.products.next_page_url)
    const totalProducts = ref<number>(props.products.total)
    const isLoading = ref(false)

    // ── Filter state ──
    const currentFilters = ref<ShopFilters>({ ...props.filters })
    const search = ref(currentFilters.value.search || '')
    const viewMode = ref<'grid' | 'list'>('grid')
    const inStockOnly = ref(!!currentFilters.value.in_stock)
    const isFilterDrawerOpen = ref(false)

    const filterStats = props.filterStats
    const minPrice = computed(() => Number(filterStats.min_price))
    const maxPrice = computed(() => Number(filterStats.max_price))

    const priceRange = ref<[number, number]>([
        Number(currentFilters.value.min_price ?? filterStats.min_price),
        Number(currentFilters.value.max_price ?? filterStats.max_price),
    ])

    // ── Derived ──
    const transformedProducts = computed(() => allProducts.value.map(transformProduct))

    const currentSortLabel = computed(() => {
        const labels: Record<string, string> = {
            newest: 'Terbaru',
            price_low: 'Harga Terendah',
            price_high: 'Harga Tertinggi',
            popular: 'Terpopuler',
            rating: 'Rating Tertinggi',
        }
        return labels[currentFilters.value.sort || 'newest']
    })

    const activeCategoryLabel = computed(() => {
        if (!currentFilters.value.category) return 'Semua Kategori'
        return props.categories.find((c) => c.slug === currentFilters.value.category)?.name || 'Semua Kategori'
    })

    const activeBrandLabel = computed(() => {
        if (!currentFilters.value.brand) return null
        return props.brands?.find((b) => b.slug === currentFilters.value.brand)?.name || null
    })

    const hasActiveFilters = computed(() => {
        const f = currentFilters.value
        return (
            !!f.search ||
            !!f.category ||
            !!f.brand ||
            !!f.rating ||
            !!f.in_stock ||
            Number(f.min_price) > minPrice.value ||
            Number(f.max_price) < maxPrice.value
        )
    })

    const activeFilterCount = computed(() => {
        let count = 0
        const f = currentFilters.value
        if (f.search) count++
        if (f.category) count++
        if (f.brand) count++
        if (f.rating) count++
        if (f.in_stock) count++
        if (Number(f.min_price) > minPrice.value) count++
        if (Number(f.max_price) < maxPrice.value) count++
        return count
    })

    const ratingItems = computed(() => {
        const base = filterStats.ratings?.length ? filterStats.ratings : [5, 4, 3, 2, 1]
        return [
            { label: 'Semua rating', value: undefined },
            ...base.map((r) => ({ label: `${r}+ bintang`, value: r })),
        ]
    })

    const isActiveCat = (slug: string | undefined): boolean =>
        (!currentFilters.value.category && slug === undefined) || currentFilters.value.category === slug

    const isActiveBrand = (slug: string): boolean => currentFilters.value.brand === slug

    // ── Filter logic ──
    function normalizeFilters(f: Record<string, any>) {
        const out = { ...f }
        if (!out.search) delete out.search
        if (!out.category) delete out.category
        if (!out.brand) delete out.brand
        if (!out.sort) delete out.sort
        if (out.rating === undefined || out.rating === null) delete out.rating
        if (!out.in_stock) delete out.in_stock
        if (Number(out.min_price) === minPrice.value) delete out.min_price
        if (Number(out.max_price) === maxPrice.value) delete out.max_price
        return out
    }

    function handleFilter(newFilters: Partial<ShopFilters>): void {
        const merged = normalizeFilters({ ...currentFilters.value, ...newFilters })

        router.get('/shop', merged, {
            preserveState: true,
            preserveScroll: true,
            only: ['products', 'filters'],
            onSuccess: (page: any) => {
                const p = page.props.products
                const f = page.props.filters

                _syncing = true

                allProducts.value = [...p.data]
                nextPageUrl.value = p.next_page_url
                totalProducts.value = p.total
                currentFilters.value = { ...f }
                search.value = f.search ?? ''
                inStockOnly.value = !!f.in_stock
                priceRange.value = [
                    Number(f.min_price ?? filterStats.min_price),
                    Number(f.max_price ?? filterStats.max_price),
                ]
                isLoading.value = false

                nextTick(() => {
                    _syncing = false
                })
            },
        })
    }

    function resetFilters() {
        handleFilter({
            search: '',
            category: undefined,
            brand: undefined,
            min_price: filterStats.min_price,
            max_price: filterStats.max_price,
            rating: undefined,
            in_stock: false,
        })
    }

    // ── Watchers (guarded by _syncing flag) ──
    const debouncedSearch = debounce((val: string) => handleFilter({ search: val }), 450)
    const debouncedPrice = debounce(() => {
        handleFilter({ min_price: priceRange.value[0], max_price: priceRange.value[1] })
    }, 650)

    watch(search, (val) => {
        if (_syncing) return
        debouncedSearch(val)
    })

    watch(priceRange, () => {
        if (_syncing) return
        debouncedPrice()
    }, { deep: true })

    watch(inStockOnly, (v) => {
        if (_syncing) return
        handleFilter({ in_stock: !!v })
    })

    // ── Sort ──
    const sortOptions = [
        [
            { label: 'Terbaru', onSelect: () => handleFilter({ sort: 'newest' }) },
            { label: 'Harga: Terendah', onSelect: () => handleFilter({ sort: 'price_low' }) },
            { label: 'Harga: Tertinggi', onSelect: () => handleFilter({ sort: 'price_high' }) },
            { label: 'Terpopuler', onSelect: () => handleFilter({ sort: 'popular' }) },
            { label: 'Rating Tertinggi', onSelect: () => handleFilter({ sort: 'rating' }) },
        ],
    ]

    // ── Infinite scroll ──
    function loadMore() {
        if (!nextPageUrl.value || isLoading.value) return
        isLoading.value = true

        router.visit(nextPageUrl.value, {
            method: 'get',
            preserveScroll: true,
            preserveState: true,
            only: ['products'],
            onSuccess: (page: any) => {
                const p = page.props.products
                allProducts.value = [...allProducts.value, ...p.data]
                nextPageUrl.value = p.next_page_url
                totalProducts.value = p.total
            },
            onFinish: () => {
                isLoading.value = false
            },
        })
    }

    return {
        // Product state
        transformedProducts,
        allProducts,
        nextPageUrl,
        totalProducts,
        isLoading,
        loadMore,

        // Filter state
        currentFilters,
        search,
        viewMode,
        inStockOnly,
        isFilterDrawerOpen,
        priceRange,
        minPrice,
        maxPrice,

        // Derived
        currentSortLabel,
        activeCategoryLabel,
        activeBrandLabel,
        hasActiveFilters,
        activeFilterCount,
        ratingItems,
        sortOptions,

        // Methods
        handleFilter,
        resetFilters,
        isActiveCat,
        isActiveBrand,
        formatCurrency,
    }
}
