export type PageBlockType
    = 'hero'
    | 'section_rich'
    | 'features'
    | 'cta'
    | 'faq'
    | 'testimonials'
    | 'divider'
    | 'spacer'
    | 'custom_html'
    | string

export interface PageBlock {
    type: PageBlockType
    data: Record<string, unknown>
}

export interface PageSeo {
    title: string
    description: string
    canonical: string
    robots?: string
    image?: string | null
    structured_data?: Array<Record<string, unknown>>
}

export interface PageDetail {
    id: number
    title: string
    slug: string
    template: string
    seo_title: string
    seo_description: string
    excerpt: string
    cover_image: string | null
    content_html: string
    blocks: PageBlock[]
    published_label: string | null
    url: string
}

export interface PageShowProps {
    seo: PageSeo
    page: PageDetail
}

