<script setup lang="ts">
import type { PageBlock } from '@/types/page'

interface HeroData {
    headline: string
    subheadline: string
    primary_cta_label: string
    primary_cta_url: string
    secondary_cta_label: string
    secondary_cta_url: string
    align: 'left' | 'center'
    variant: 'image-right' | 'image-left' | 'image-bg' | 'text-only'
    image: string | null
}

interface HeadingData {
    level: 1 | 2 | 3 | 4 | 5 | 6
    text: string
}

interface RichTextData {
    content: string
}

interface ImageData {
    url: string | null
    alt: string
    caption: string
}

interface ListData {
    ordered: boolean
    items: string[]
}

interface QuoteData {
    quote: string
    cite: string
}

interface SectionRichData {
    title: string
    content: string
    container: 'sm' | 'md' | 'lg' | 'xl'
    with_divider: boolean
}

interface FeatureItem {
    title: string
    icon: string
    description: string
}

interface FeaturesData {
    title: string
    subtitle: string
    columns: number
    iconed: boolean
    carded: boolean
    items: FeatureItem[]
}

interface CtaData {
    title: string
    description: string
    button_label: string
    button_url: string
    style: 'primary' | 'secondary' | 'outline'
    accent: string
}

interface FaqItem {
    q: string
    a: string
}

interface FaqData {
    title: string
    items: FaqItem[]
}

interface FaqAccordionItem {
    label: string
    content: string
    value: string
}

interface TestimonialItem {
    name: string
    role: string
    quote: string
    avatar: string | null
}

interface TestimonialsData {
    title: string
    items: TestimonialItem[]
}

interface SpacerData {
    size: 'sm' | 'md' | 'lg' | 'xl'
}

interface CustomHtmlData {
    html: string
    meta: Record<string, unknown>
}

const props = defineProps<{
    blocks: PageBlock[]
}>()

const spacerClassMap: Record<SpacerData['size'], string> = {
    sm: 'h-4',
    md: 'h-8',
    lg: 'h-14',
    xl: 'h-20'
}

function heroData(data: Record<string, unknown>): HeroData {
    const align = `${data.align ?? 'left'}` === 'center' ? 'center' : 'left'
    const variant = `${data.variant ?? 'image-right'}`
    const normalizedVariant: HeroData['variant']
        = variant === 'image-left' || variant === 'image-bg' || variant === 'text-only'
            ? variant
            : 'image-right'

    return {
        headline: `${data.headline ?? ''}`,
        subheadline: `${data.subheadline ?? ''}`,
        primary_cta_label: `${data.primary_cta_label ?? ''}`,
        primary_cta_url: `${data.primary_cta_url ?? ''}`,
        secondary_cta_label: `${data.secondary_cta_label ?? ''}`,
        secondary_cta_url: `${data.secondary_cta_url ?? ''}`,
        align,
        variant: normalizedVariant,
        image: typeof data.image === 'string' && data.image.trim() !== '' ? data.image : null
    }
}

function sectionRichData(data: Record<string, unknown>): SectionRichData {
    const rawContainer = `${data.container ?? 'lg'}`
    const container: SectionRichData['container']
        = rawContainer === 'sm' || rawContainer === 'md' || rawContainer === 'xl'
            ? rawContainer
            : 'lg'

    return {
        title: `${data.title ?? ''}`,
        content: `${data.content ?? ''}`,
        container,
        with_divider: Boolean(data.with_divider)
    }
}

function featuresData(data: Record<string, unknown>): FeaturesData {
    const rawColumns = Number(data.columns ?? 3)
    const columns = Number.isFinite(rawColumns) ? Math.min(4, Math.max(2, rawColumns)) : 3
    const items = Array.isArray(data.items) ? data.items : []

    return {
        title: `${data.title ?? ''}`,
        subtitle: `${data.subtitle ?? ''}`,
        columns,
        iconed: Boolean(data.iconed),
        carded: Boolean(data.carded),
        items: items
            .map((item) => {
                const row = (item ?? {}) as Record<string, unknown>
                return {
                    title: `${row.title ?? ''}`,
                    icon: `${row.icon ?? ''}`,
                    description: `${row.description ?? ''}`
                }
            })
            .filter((item) => item.title !== '' || item.description !== '')
    }
}

function ctaData(data: Record<string, unknown>): CtaData {
    const rawStyle = `${data.style ?? 'primary'}`
    const style: CtaData['style']
        = rawStyle === 'secondary' || rawStyle === 'outline'
            ? rawStyle
            : 'primary'

    return {
        title: `${data.title ?? ''}`,
        description: `${data.description ?? ''}`,
        button_label: `${data.button_label ?? ''}`,
        button_url: `${data.button_url ?? ''}`,
        style,
        accent: `${data.accent ?? ''}`
    }
}

function faqData(data: Record<string, unknown>): FaqData {
    const items = Array.isArray(data.items) ? data.items : []

    return {
        title: `${data.title ?? ''}`,
        items: items
            .map((item) => {
                const row = (item ?? {}) as Record<string, unknown>
                return {
                    q: `${row.q ?? ''}`,
                    a: `${row.a ?? ''}`
                }
            })
            .filter((item) => item.q !== '' || item.a !== '')
    }
}

function faqAccordionItems(data: Record<string, unknown>): FaqAccordionItem[] {
    return faqData(data).items.map((item, index) => ({
        label: item.q || `Pertanyaan ${index + 1}`,
        content: item.a,
        value: `faq-${index}`
    }))
}

function testimonialsData(data: Record<string, unknown>): TestimonialsData {
    const items = Array.isArray(data.items) ? data.items : []

    return {
        title: `${data.title ?? ''}`,
        items: items
            .map((item) => {
                const row = (item ?? {}) as Record<string, unknown>
                return {
                    name: `${row.name ?? ''}`,
                    role: `${row.role ?? ''}`,
                    quote: `${row.quote ?? ''}`,
                    avatar: typeof row.avatar === 'string' && row.avatar.trim() !== '' ? row.avatar : null
                }
            })
            .filter((item) => item.name !== '' || item.quote !== '')
    }
}

function testimonialInitial(name: string): string | undefined {
    const normalizedName = name.trim()

    if (normalizedName === '') {
        return undefined
    }

    return normalizedName.charAt(0).toUpperCase()
}

function spacerData(data: Record<string, unknown>): SpacerData {
    const rawSize = `${data.size ?? 'md'}`
    const size: SpacerData['size']
        = rawSize === 'sm' || rawSize === 'lg' || rawSize === 'xl'
            ? rawSize
            : 'md'

    return { size }
}

function customHtmlData(data: Record<string, unknown>): CustomHtmlData {
    return {
        html: `${data.html ?? ''}`,
        meta: typeof data.meta === 'object' && data.meta !== null ? (data.meta as Record<string, unknown>) : {}
    }
}

function headingData(data: Record<string, unknown>): HeadingData {
    const rawLevel = Number(data.level ?? 2)
    const normalizedLevel = Number.isFinite(rawLevel)
        ? Math.min(6, Math.max(1, Math.round(rawLevel)))
        : 2

    return {
        level: normalizedLevel as HeadingData['level'],
        text: `${data.text ?? ''}` || `${data.content ?? ''}`
    }
}

function headingTag(level: HeadingData['level']): 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' {
    return `h${level}` as 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6'
}

function richTextData(data: Record<string, unknown>): RichTextData {
    return {
        content: `${data.content ?? ''}` || `${data.text ?? ''}`
    }
}

function imageData(data: Record<string, unknown>): ImageData {
    return {
        url: typeof data.url === 'string' && data.url.trim() !== '' ? data.url : null,
        alt: `${data.alt ?? ''}`,
        caption: `${data.caption ?? ''}`
    }
}

function listData(data: Record<string, unknown>): ListData {
    const items = Array.isArray(data.items) ? data.items : []

    return {
        ordered: Boolean(data.ordered),
        items: items
            .map((item) => `${item ?? ''}`.trim())
            .filter((item) => item !== '')
    }
}

function quoteData(data: Record<string, unknown>): QuoteData {
    return {
        quote: `${data.quote ?? ''}` || `${data.text ?? ''}`,
        cite: `${data.cite ?? ''}`
    }
}

function featureGridClass(columns: number): string {
    if (columns === 2) {
        return 'md:grid-cols-2'
    }

    if (columns === 4) {
        return 'md:grid-cols-2 xl:grid-cols-4'
    }

    return 'md:grid-cols-2 xl:grid-cols-3'
}

function heroTextAlignClass(align: HeroData['align']): string {
    return align === 'center' ? 'text-center items-center' : 'text-left items-start'
}

function heroLayoutClass(variant: HeroData['variant']): string {
    if (variant === 'image-left') {
        return 'lg:flex-row-reverse'
    }

    if (variant === 'image-right') {
        return 'lg:flex-row'
    }

    return ''
}

function ctaColor(style: CtaData['style']): 'primary' | 'secondary' | 'neutral' {
    if (style === 'secondary') {
        return 'secondary'
    }

    if (style === 'outline') {
        return 'neutral'
    }

    return 'primary'
}

function ctaVariant(style: CtaData['style']): 'solid' | 'outline' {
    return style === 'outline' ? 'outline' : 'solid'
}
</script>

<template>
    <div class="space-y-6">
        <template v-for="(block, index) in props.blocks" :key="`page-block-${index}`">
            <section v-if="block.type === 'hero'" class="relative overflow-hidden rounded-3xl border border-default/80 bg-linear-to-br from-primary-50/70 via-white to-cyan-50/40 p-6 dark:from-primary-950/50 dark:via-gray-950 dark:to-cyan-950/30 sm:p-8">
                <template v-if="heroData(block.data).variant === 'image-bg' && heroData(block.data).image">
                    <div class="absolute inset-0">
                        <img
                            :src="heroData(block.data).image ?? ''"
                            :alt="heroData(block.data).headline || 'Hero image'"
                            class="h-full w-full object-cover opacity-20"
                        >
                    </div>
                </template>

                <div class="relative flex flex-col gap-6" :class="heroLayoutClass(heroData(block.data).variant)">
                    <div class="flex-1 space-y-4" :class="heroTextAlignClass(heroData(block.data).align)">
                        <h2 class="text-2xl font-bold leading-tight text-highlighted sm:text-4xl">
                            {{ heroData(block.data).headline || 'Hero' }}
                        </h2>
                        <p v-if="heroData(block.data).subheadline" class="max-w-2xl text-sm leading-relaxed text-muted sm:text-base">
                            {{ heroData(block.data).subheadline }}
                        </p>

                        <div class="flex flex-wrap gap-2" :class="heroData(block.data).align === 'center' ? 'justify-center' : ''">
                            <UButton
                                v-if="heroData(block.data).primary_cta_label"
                                :to="heroData(block.data).primary_cta_url || undefined"
                                color="primary"
                                variant="solid"
                                trailing-icon="i-lucide-arrow-right"
                            >
                                {{ heroData(block.data).primary_cta_label }}
                            </UButton>

                            <UButton
                                v-if="heroData(block.data).secondary_cta_label"
                                :to="heroData(block.data).secondary_cta_url || undefined"
                                color="neutral"
                                variant="outline"
                            >
                                {{ heroData(block.data).secondary_cta_label }}
                            </UButton>
                        </div>
                    </div>

                    <div
                        v-if="heroData(block.data).image && heroData(block.data).variant !== 'text-only' && heroData(block.data).variant !== 'image-bg'"
                        class="w-full lg:w-2/5"
                    >
                        <div class="overflow-hidden rounded-2xl border border-default/80 bg-elevated">
                            <img
                                :src="heroData(block.data).image ?? ''"
                                :alt="heroData(block.data).headline || 'Hero image'"
                                class="h-full w-full object-cover"
                            >
                        </div>
                    </div>
                </div>
            </section>

            <section v-else-if="block.type === 'section_rich'" class="space-y-4">
                <h2 v-if="sectionRichData(block.data).title" class="text-xl font-semibold text-highlighted sm:text-2xl">
                    {{ sectionRichData(block.data).title }}
                </h2>

                <UCard class="rounded-3xl border border-default/80" :ui="{ body: 'p-5 sm:p-8' }">
                    <article class="prose prose-gray max-w-none dark:prose-invert" v-html="sectionRichData(block.data).content" />
                </UCard>

                <USeparator v-if="sectionRichData(block.data).with_divider" />
            </section>

            <section v-else-if="block.type === 'heading'">
                <component
                    :is="headingTag(headingData(block.data).level)"
                    class="text-xl font-semibold leading-tight text-highlighted sm:text-2xl"
                >
                    {{ headingData(block.data).text }}
                </component>
            </section>

            <section v-else-if="block.type === 'rich_text' || block.type === 'paragraph' || block.type === 'richtext'">
                <UCard class="rounded-3xl border border-default/80" :ui="{ body: 'p-5 sm:p-8' }">
                    <article class="prose prose-gray max-w-none dark:prose-invert" v-html="richTextData(block.data).content" />
                </UCard>
            </section>

            <figure v-else-if="block.type === 'image'" class="space-y-2">
                <UCard class="overflow-hidden rounded-2xl border border-default/80" :ui="{ body: 'p-0' }">
                    <img
                        v-if="imageData(block.data).url"
                        :src="imageData(block.data).url ?? ''"
                        :alt="imageData(block.data).alt || 'Image'"
                        class="h-full w-full object-cover"
                    >
                    <div v-else class="grid h-48 place-items-center bg-elevated">
                        <UIcon name="i-lucide-image-off" class="size-7 text-muted" />
                    </div>
                </UCard>
                <figcaption v-if="imageData(block.data).caption" class="text-sm text-muted">
                    {{ imageData(block.data).caption }}
                </figcaption>
            </figure>

            <section v-else-if="block.type === 'list'">
                <UCard class="rounded-2xl border border-default/80">
                    <component :is="listData(block.data).ordered ? 'ol' : 'ul'" class="space-y-2 pl-5 text-sm text-muted">
                        <li
                            v-for="(item, itemIndex) in listData(block.data).items"
                            :key="`legacy-list-${index}-${itemIndex}`"
                            v-html="item"
                        />
                    </component>
                </UCard>
            </section>

            <section v-else-if="block.type === 'quote'">
                <UCard class="rounded-2xl border border-default/80">
                    <blockquote class="border-l-4 border-primary pl-4">
                        <p class="text-sm text-highlighted">{{ quoteData(block.data).quote }}</p>
                        <cite v-if="quoteData(block.data).cite" class="mt-2 block text-xs not-italic text-muted">
                            — {{ quoteData(block.data).cite }}
                        </cite>
                    </blockquote>
                </UCard>
            </section>

            <section v-else-if="block.type === 'features' || block.type === 'features_grid'" class="space-y-4">
                <div class="space-y-2">
                    <h2 v-if="featuresData(block.data).title" class="text-xl font-semibold text-highlighted sm:text-2xl">
                        {{ featuresData(block.data).title }}
                    </h2>
                    <p v-if="featuresData(block.data).subtitle" class="text-sm text-muted">
                        {{ featuresData(block.data).subtitle }}
                    </p>
                </div>

                <div class="grid gap-3" :class="featureGridClass(featuresData(block.data).columns)">
                    <UCard
                        v-for="(item, itemIndex) in featuresData(block.data).items"
                        :key="`feature-item-${index}-${itemIndex}`"
                        class="rounded-2xl border border-default/80"
                    >
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <UIcon
                                    v-if="featuresData(block.data).iconed"
                                    :name="item.icon || 'i-lucide-star'"
                                    class="size-4 text-primary"
                                />
                                <h3 class="text-sm font-semibold text-highlighted">
                                    {{ item.title }}
                                </h3>
                            </div>
                            <p class="text-sm text-muted">
                                {{ item.description }}
                            </p>
                        </div>
                    </UCard>
                </div>
            </section>

            <section v-else-if="block.type === 'cta'">
                <UCard class="rounded-3xl border border-default/80 bg-linear-to-r from-primary-50/60 to-cyan-50/40 dark:from-primary-950/30 dark:to-cyan-950/30">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-y-1">
                            <h2 class="text-xl font-semibold text-highlighted sm:text-2xl">
                                {{ ctaData(block.data).title || 'Call to Action' }}
                            </h2>
                            <p v-if="ctaData(block.data).description" class="text-sm text-muted">
                                {{ ctaData(block.data).description }}
                            </p>
                        </div>

                        <UButton
                            v-if="ctaData(block.data).button_label"
                            :to="ctaData(block.data).button_url || undefined"
                            :color="ctaColor(ctaData(block.data).style)"
                            :variant="ctaVariant(ctaData(block.data).style)"
                            trailing-icon="i-lucide-arrow-right"
                        >
                            {{ ctaData(block.data).button_label }}
                        </UButton>
                    </div>
                </UCard>
            </section>

            <section v-else-if="block.type === 'faq'" class="space-y-3">
                <h2 v-if="faqData(block.data).title" class="text-xl font-semibold text-highlighted sm:text-2xl">
                    {{ faqData(block.data).title }}
                </h2>

                <UAccordion
                    :items="faqAccordionItems(block.data)"
                    type="multiple"
                    collapsible
                    :ui="{
                        item: 'rounded-2xl border border-default/80 px-3 py-1',
                        trigger: 'text-sm font-semibold text-highlighted',
                        content: 'pt-1'
                    }"
                >
                    <template #body="{ item }">
                        <p class="whitespace-pre-line text-sm leading-relaxed text-muted">
                            {{ item.content }}
                        </p>
                    </template>
                </UAccordion>
            </section>

            <section v-else-if="block.type === 'testimonials' || block.type === 'testimonial'" class="space-y-4">
                <h2 v-if="testimonialsData(block.data).title" class="text-xl font-semibold text-highlighted sm:text-2xl">
                    {{ testimonialsData(block.data).title }}
                </h2>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <UCard
                        v-for="(item, itemIndex) in testimonialsData(block.data).items"
                        :key="`testimonial-item-${index}-${itemIndex}`"
                        class="rounded-2xl border border-default/80"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <UAvatar
                                    :src="item.avatar ?? undefined"
                                    :alt="item.name || 'Avatar'"
                                    :text="testimonialInitial(item.name)"
                                    icon="i-lucide-user"
                                    size="lg"
                                />
                                <div>
                                    <p class="text-sm font-semibold text-highlighted">{{ item.name || 'Anonymous' }}</p>
                                    <p v-if="item.role" class="text-xs text-muted">{{ item.role }}</p>
                                </div>
                            </div>
                            <p class="text-sm leading-relaxed text-muted">"{{ item.quote }}"</p>
                        </div>
                    </UCard>
                </div>
            </section>

            <USeparator v-else-if="block.type === 'divider'" />

            <div v-else-if="block.type === 'spacer'" :class="spacerClassMap[spacerData(block.data).size]" />

            <section v-else-if="block.type === 'custom_html'" class="space-y-3">
                <UCard class="rounded-2xl border border-warning/40 bg-warning/5">
                    <div class="flex items-center gap-2 text-sm text-warning">
                        <UIcon name="i-lucide-triangle-alert" class="size-4" />
                        <span>Custom HTML</span>
                    </div>
                </UCard>
                <div class="prose prose-gray max-w-none dark:prose-invert" v-html="customHtmlData(block.data).html" />
            </section>

            <section v-else>
                <UCard class="rounded-2xl border border-default/80">
                    <p class="text-sm text-muted">
                        Blok <span class="font-semibold text-highlighted">{{ block.type }}</span> belum didukung di storefront.
                    </p>
                </UCard>
            </section>
        </template>
    </div>
</template>
