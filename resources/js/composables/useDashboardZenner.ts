import { computed, ref, type ComputedRef } from 'vue'
import type { DashboardZennerCategory, DashboardZennerContent } from '@/types/dashboard'

type UseDashboardZennerOptions = {
    categories: ComputedRef<DashboardZennerCategory[]>
    contents: ComputedRef<DashboardZennerContent[]>
}

export function useDashboardZenner(options: UseDashboardZennerOptions) {
    const selectedCategory = ref<string>('all')
    const searchQuery = ref('')

    const categoryItems = computed(() => {
        const base = [{ label: 'Semua kategori', value: 'all' }]
        const dynamic = options.categories.value.map((category) => ({
            label: category.name,
            value: category.slug,
        }))

        return [...base, ...dynamic]
    })

    const filteredContents = computed(() => {
        const keyword = searchQuery.value.trim().toLowerCase()

        return options.contents.value.filter((content) => {
            const matchesCategory = selectedCategory.value === 'all'
                || String(content.category_slug ?? '') === selectedCategory.value

            if (!matchesCategory) {
                return false
            }

            if (keyword === '') {
                return true
            }

            const haystack = [
                content.title,
                content.excerpt,
                content.category_name ?? '',
                content.slug,
            ]
                .join(' ')
                .toLowerCase()

            return haystack.includes(keyword)
        })
    })

    const totalContents = computed(() => options.contents.value.length)
    const totalCategories = computed(() => options.categories.value.length)

    function formatDate(value?: string | null): string {
        if (!value) {
            return '-'
        }

        const date = new Date(value)

        if (Number.isNaN(date.getTime())) {
            return value
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(date)
    }

    function normalizeFileUrl(file?: string | null): string | null {
        if (!file) {
            return null
        }

        if (file.startsWith('http://') || file.startsWith('https://') || file.startsWith('/media/public/')) {
            return file
        }

        if (file.startsWith('/')) {
            if (file.startsWith('/storage/')) {
                return `/media/public/${file.slice('/storage/'.length)}`
            }

            if (file.startsWith('/public/')) {
                return `/media/public/${file.slice('/public/'.length)}`
            }

            return file
        }

        const normalized = file.replace(/^\/+/, '')

        if (normalized.startsWith('media/public/')) {
            return `/${normalized}`
        }

        if (normalized.startsWith('public/storage/')) {
            return `/media/public/${normalized.slice('public/storage/'.length)}`
        }

        if (normalized.startsWith('storage/')) {
            return `/media/public/${normalized.slice('storage/'.length)}`
        }

        if (normalized.startsWith('public/')) {
            return `/media/public/${normalized.slice('public/'.length)}`
        }

        return `/media/public/${normalized}`
    }

    return {
        selectedCategory,
        searchQuery,
        categoryItems,
        filteredContents,
        totalContents,
        totalCategories,
        formatDate,
        normalizeFileUrl,
    }
}
