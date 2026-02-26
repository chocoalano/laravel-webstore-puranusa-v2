import { computed } from 'vue'
import type { ArticleDetail, ArticleSeo } from '@/types/article'

export function useArticleDetail(article: ArticleDetail, seo: ArticleSeo) {
    const publishedDate = computed(() => {
        if (!article.published_at) {
            return null
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(new Date(article.published_at))
    })

    const updatedDate = computed(() => {
        if (!article.updated_at) {
            return null
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(article.updated_at))
    })

    const readTimeLabel = computed(() => `${article.read_time_minutes} menit baca`)

    const jsonLdScripts = computed(() => {
        const list = seo.structured_data ?? []

        return list.map((item) => JSON.stringify(item))
    })

    const breadcrumbItems = computed(() => [
        { label: 'Home', icon: 'i-lucide-home', to: '/' },
        { label: 'Artikel', to: '/articles' },
        { label: article.title },
    ])

    function headingTag(level: number): 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' {
        if (level <= 1) {
            return 'h1'
        }

        if (level >= 6) {
            return 'h6'
        }

        return `h${level}` as 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6'
    }

    return {
        publishedDate,
        updatedDate,
        readTimeLabel,
        jsonLdScripts,
        breadcrumbItems,
        headingTag,
    }
}
