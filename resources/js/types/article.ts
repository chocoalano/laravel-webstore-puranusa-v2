export type ArticleSortValue = 'newest' | 'oldest' | 'az' | 'za'

export interface ArticleSeo {
    title: string
    description: string
    canonical: string
    robots?: string
    image?: string | null
    structured_data?: Array<Record<string, unknown>>
}

export interface ArticleFilters {
    search: string | null
    tag: string | null
    sort: ArticleSortValue
    page: number
}

export interface ArticleStats {
    total_articles: number
    result_count: number
    current_page_count: number
    tag_count: number
}

export interface ArticleCard {
    id: number
    title: string
    slug: string
    seo_title: string | null
    seo_description: string | null
    excerpt: string
    cover_image: string | null
    published_at: string | null
    published_label: string | null
    read_time_minutes: number
    tags: string[]
    url: string
}

export interface ArticlePaginationLink {
    url: string | null
    label: string
    active: boolean
}

export interface ArticlePaginator {
    data: ArticleCard[]
    current_page: number
    per_page: number
    total: number
    last_page: number
    from: number | null
    to: number | null
    links: ArticlePaginationLink[]
    next_page_url: string | null
    prev_page_url: string | null
}

export type ArticleBlock =
    | {
          type: 'heading'
          level: number
          text: string
      }
    | {
          type: 'rich_text'
          html: string
      }
    | {
          type: 'image'
          url: string | null
          alt: string
          caption: string
      }
    | {
          type: 'list'
          ordered: boolean
          items: string[]
      }
    | {
          type: 'quote'
          quote: string
          cite: string
      }
    | {
          type: 'divider'
      }
    | {
          type: 'unknown'
          block_type: string
          data: Record<string, unknown>
      }

export interface ArticleDetail {
    id: number
    title: string
    slug: string
    seo_title: string | null
    seo_description: string | null
    excerpt: string
    cover_image: string | null
    tags: string[]
    blocks: ArticleBlock[]
    read_time_minutes: number
    published_at: string | null
    published_label: string | null
    updated_at: string | null
    updated_label: string | null
    url: string
}

export interface ArticleIndexPageProps {
    seo: ArticleSeo
    articles: ArticlePaginator
    filters: ArticleFilters
    availableTags: string[]
    stats: ArticleStats
}

export interface ArticleShowPageProps {
    seo: ArticleSeo
    article: ArticleDetail
    relatedArticles: ArticleCard[]
}

export interface ArticleFilterOption<TValue extends string = string> {
    label: string
    value: TValue
}
