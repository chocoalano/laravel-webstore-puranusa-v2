import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import type {
    ArticleFilterOption,
    ArticleFilters,
    ArticleIndexPageProps,
    ArticleSortValue,
} from '@/types/article'

const SORT_ITEMS: ArticleFilterOption<ArticleSortValue>[] = [
    { label: 'Terbaru', value: 'newest' },
    { label: 'Terlama', value: 'oldest' },
    { label: 'A-Z', value: 'az' },
    { label: 'Z-A', value: 'za' },
]

export function useArticleCatalog(props: ArticleIndexPageProps) {
    const searchQuery = ref(props.filters.search ?? '')
    const selectedTag = ref(props.filters.tag ?? '')
    const sortValue = ref<ArticleSortValue>(props.filters.sort ?? 'newest')
    const isFilterDrawerOpen = ref(false)
    const isApplying = ref(false)

    const isSyncing = ref(false)
    let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null

    const articles = computed(() => props.articles.data)
    const pagination = computed(() => props.articles)
    const availableTags = computed(() => props.availableTags)
    const stats = computed(() => props.stats)

    const sortItems = computed(() => SORT_ITEMS)
    const tagItems = computed<ArticleFilterOption[]>(() => {
        return [
            { label: 'Semua tag', value: '' },
            ...availableTags.value.map((tag) => ({ label: tag, value: tag })),
        ]
    })

    const hasActiveFilters = computed(() => {
        return (
            searchQuery.value.trim() !== ''
            || selectedTag.value.trim() !== ''
            || sortValue.value !== 'newest'
        )
    })

    const selectedSortLabel = computed(() => {
        const item = sortItems.value.find((option) => option.value === sortValue.value)

        return item?.label ?? 'Terbaru'
    })

    const selectedTagLabel = computed(() => {
        if (selectedTag.value.trim() === '') {
            return 'Semua tag'
        }

        return selectedTag.value
    })

    function normalizeFilters(page: number): Record<string, string | number> {
        const normalized: Record<string, string | number> = {}
        const search = searchQuery.value.trim()
        const tag = selectedTag.value.trim()

        if (search !== '') {
            normalized.search = search
        }

        if (tag !== '') {
            normalized.tag = tag
        }

        if (sortValue.value !== 'newest') {
            normalized.sort = sortValue.value
        }

        if (page > 1) {
            normalized.page = page
        }

        return normalized
    }

    function applyFilters(page = 1): void {
        router.get('/articles', normalizeFilters(page), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['seo', 'articles', 'filters', 'availableTags', 'stats'],
            onStart: () => {
                isApplying.value = true
            },
            onFinish: () => {
                isApplying.value = false
            },
        })
    }

    function onSearchQueryChange(value: string): void {
        searchQuery.value = value
    }

    function onTagChange(value: string): void {
        selectedTag.value = value
        applyFilters(1)
    }

    function onSortChange(value: ArticleSortValue): void {
        sortValue.value = value
        applyFilters(1)
    }

    function onPageChange(page: number): void {
        applyFilters(page)
    }

    function applyCurrentFilters(): void {
        applyFilters(1)
    }

    function resetFilters(): void {
        searchQuery.value = ''
        selectedTag.value = ''
        sortValue.value = 'newest'
        applyFilters(1)
    }

    function openFilterDrawer(): void {
        isFilterDrawerOpen.value = true
    }

    function closeFilterDrawer(): void {
        isFilterDrawerOpen.value = false
    }

    function setFilterDrawerOpen(value: boolean): void {
        isFilterDrawerOpen.value = value
    }

    watch(
        () => props.filters,
        (filters: ArticleFilters) => {
            isSyncing.value = true
            searchQuery.value = filters.search ?? ''
            selectedTag.value = filters.tag ?? ''
            sortValue.value = filters.sort ?? 'newest'
            setTimeout(() => {
                isSyncing.value = false
            }, 0)
        },
        { deep: true }
    )

    watch(searchQuery, () => {
        if (isSyncing.value) {
            return
        }

        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer)
        }

        searchDebounceTimer = setTimeout(() => {
            applyFilters(1)
        }, 400)
    })

    onBeforeUnmount(() => {
        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer)
            searchDebounceTimer = null
        }
    })

    return {
        searchQuery,
        selectedTag,
        sortValue,
        isFilterDrawerOpen,
        isApplying,
        articles,
        pagination,
        availableTags,
        stats,
        sortItems,
        tagItems,
        hasActiveFilters,
        selectedSortLabel,
        selectedTagLabel,
        applyCurrentFilters,
        onSearchQueryChange,
        onTagChange,
        onSortChange,
        onPageChange,
        resetFilters,
        openFilterDrawer,
        closeFilterDrawer,
        setFilterDrawerOpen,
    }
}
