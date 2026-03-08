<script setup lang="ts">
import type { ArticleBlock } from '@/types/article'

const props = defineProps<{
    blocks: ArticleBlock[]
}>()

function headingTag(level: number): 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' {
    if (level <= 1) {
        return 'h1'
    }

    if (level >= 6) {
        return 'h6'
    }

    return `h${level}` as 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6'
}
</script>

<template>
    <article class="article-content">
        <template v-for="(block, index) in props.blocks" :key="`block-${index}`">
            <component
                :is="headingTag((block as { level: number }).level)"
                v-if="block.type === 'heading'"
                class="article-heading"
                :class="`article-heading--h${(block as { level: number }).level}`"
            >
                {{ (block as { text: string }).text }}
            </component>

            <div
                v-else-if="block.type === 'rich_text'"
                class="article-rich-text"
                v-html="(block as { html: string }).html"
            />

            <figure v-else-if="block.type === 'image'" class="article-figure">
                <div class="article-figure__media">
                    <img
                        v-if="(block as { url: string | null }).url"
                        :src="(block as { url: string }).url"
                        :alt="(block as { alt: string }).alt || 'Gambar artikel'"
                        class="article-figure__img"
                        loading="lazy"
                    >
                    <div v-else class="article-figure__placeholder">
                        <UIcon name="i-lucide-image-off" class="size-7" />
                    </div>
                </div>
                <figcaption v-if="(block as { caption: string }).caption" class="article-figure__caption">
                    {{ (block as { caption: string }).caption }}
                </figcaption>
            </figure>

            <component
                :is="(block as { ordered: boolean }).ordered ? 'ol' : 'ul'"
                v-else-if="block.type === 'list'"
                :class="(block as { ordered: boolean }).ordered ? 'article-list article-list--ordered' : 'article-list article-list--unordered'"
            >
                <li
                    v-for="(item, itemIndex) in (block as { items: string[] }).items"
                    :key="`item-${index}-${itemIndex}`"
                    class="article-list__item"
                    v-html="item"
                />
            </component>

            <blockquote v-else-if="block.type === 'quote'" class="article-blockquote">
                <p>{{ (block as { quote: string }).quote }}</p>
                <cite v-if="(block as { cite: string }).cite" class="article-blockquote__cite">
                    — {{ (block as { cite: string }).cite }}
                </cite>
            </blockquote>

            <hr v-else-if="block.type === 'divider'" class="article-divider">

            <details v-else class="article-unknown">
                <summary class="article-unknown__summary">
                    Blok tidak dikenal: {{ (block as { block_type: string }).block_type }}
                </summary>
                <pre class="article-unknown__pre">{{ JSON.stringify((block as { data: unknown }).data, null, 2) }}</pre>
            </details>
        </template>

        <div v-if="props.blocks.length === 0" class="article-empty">
            Konten artikel belum tersedia.
        </div>
    </article>
</template>

<style scoped>
/* ── Base container ── */
.article-content {
    line-height: 1.75;
    color: var(--ui-text);
    font-size: 1rem;
}

.article-content > * + * {
    margin-top: 1.5rem;
}

/* ── Headings ── */
.article-heading {
    font-weight: 700;
    line-height: 1.25;
    color: var(--ui-text-highlighted);
    margin-top: 2.25rem;
    margin-bottom: 0.75rem;
}

.article-heading--h1 { font-size: 2rem; }
.article-heading--h2 { font-size: 1.5rem; }
.article-heading--h3 { font-size: 1.25rem; }
.article-heading--h4 { font-size: 1.125rem; }
.article-heading--h5 { font-size: 1rem; }
.article-heading--h6 { font-size: 0.875rem; }

/* ── Rich text (TipTap HTML output) ── */
.article-rich-text {
    color: var(--ui-text);
}

.article-rich-text :deep(p) {
    margin-top: 0;
    margin-bottom: 1.25rem;
    line-height: 1.75;
}

.article-rich-text :deep(p:last-child) {
    margin-bottom: 0;
}

.article-rich-text :deep(h1),
.article-rich-text :deep(h2),
.article-rich-text :deep(h3),
.article-rich-text :deep(h4),
.article-rich-text :deep(h5),
.article-rich-text :deep(h6) {
    font-weight: 700;
    line-height: 1.25;
    color: var(--ui-text-highlighted);
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}

.article-rich-text :deep(h1) { font-size: 1.875rem; }
.article-rich-text :deep(h2) { font-size: 1.5rem; }
.article-rich-text :deep(h3) { font-size: 1.25rem; }
.article-rich-text :deep(h4) { font-size: 1.125rem; }
.article-rich-text :deep(h5) { font-size: 1rem; }
.article-rich-text :deep(h6) { font-size: 0.875rem; }

.article-rich-text :deep(strong) {
    font-weight: 700;
    color: var(--ui-text-highlighted);
}

.article-rich-text :deep(em) {
    font-style: italic;
}

.article-rich-text :deep(u) {
    text-decoration: underline;
    text-underline-offset: 3px;
}

.article-rich-text :deep(s) {
    text-decoration: line-through;
    color: var(--ui-text-muted);
}

.article-rich-text :deep(a) {
    color: var(--ui-primary);
    text-decoration: underline;
    text-underline-offset: 3px;
    font-weight: 500;
    transition: opacity 0.15s;
}

.article-rich-text :deep(a:hover) {
    opacity: 0.75;
}

.article-rich-text :deep(code) {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.875em;
    background: var(--ui-bg-elevated);
    border: 1px solid var(--ui-border);
    border-radius: 0.25rem;
    padding: 0.1em 0.35em;
    color: var(--ui-error);
}

.article-rich-text :deep(pre) {
    background: var(--ui-bg-elevated);
    border: 1px solid var(--ui-border);
    border-radius: 0.75rem;
    padding: 1.25rem;
    overflow-x: auto;
    margin-top: 1.25rem;
    margin-bottom: 1.25rem;
}

.article-rich-text :deep(pre code) {
    background: transparent;
    border: none;
    padding: 0;
    font-size: 0.875rem;
    color: var(--ui-text);
}

.article-rich-text :deep(ul) {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-top: 0.75rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.article-rich-text :deep(ol) {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-top: 0.75rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.article-rich-text :deep(li) {
    line-height: 1.7;
}

.article-rich-text :deep(blockquote) {
    border-left: 4px solid var(--ui-primary);
    padding-left: 1.25rem;
    margin: 1.5rem 0;
    color: var(--ui-text-toned);
    font-style: italic;
}

.article-rich-text :deep(hr) {
    border: none;
    border-top: 1px solid var(--ui-border);
    margin: 2rem 0;
}

.article-rich-text :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.75rem;
    margin: 1rem 0;
}

.article-rich-text :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
    font-size: 0.9rem;
}

.article-rich-text :deep(th) {
    background: var(--ui-bg-elevated);
    font-weight: 600;
    text-align: left;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--ui-border);
    color: var(--ui-text-highlighted);
}

.article-rich-text :deep(td) {
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--ui-border);
    vertical-align: top;
}

.article-rich-text :deep(tr:nth-child(even) td) {
    background: var(--ui-bg-elevated);
}

/* ── Figure / Image block ── */
.article-figure {
    margin: 2rem 0;
}

.article-figure__media {
    overflow: hidden;
    border-radius: 1rem;
    background: var(--ui-bg-elevated);
}

.article-figure__img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
}

.article-figure__placeholder {
    display: grid;
    place-items: center;
    height: 14rem;
    color: var(--ui-text-muted);
}

.article-figure__caption {
    margin-top: 0.625rem;
    font-size: 0.875rem;
    color: var(--ui-text-muted);
    text-align: center;
}

/* ── List block ── */
.article-list {
    padding-left: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.article-list--unordered {
    list-style-type: disc;
}

.article-list--ordered {
    list-style-type: decimal;
}

.article-list__item {
    line-height: 1.7;
    color: var(--ui-text);
}

/* ── Blockquote block ── */
.article-blockquote {
    border-left: 4px solid var(--ui-primary);
    background: var(--ui-bg-elevated);
    border-radius: 0 0.75rem 0.75rem 0;
    padding: 1rem 1.25rem;
    color: var(--ui-text-toned);
    font-style: italic;
}

.article-blockquote p {
    margin: 0;
    line-height: 1.7;
}

.article-blockquote__cite {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    font-style: normal;
    font-weight: 600;
    color: var(--ui-text-muted);
}

/* ── Divider block ── */
.article-divider {
    border: none;
    border-top: 1px solid var(--ui-border);
    margin: 2rem 0;
}

/* ── Unknown block (dev only) ── */
.article-unknown {
    border-radius: 0.75rem;
    border: 1px solid var(--ui-border);
    padding: 0.75rem;
    font-size: 0.875rem;
}

.article-unknown__summary {
    cursor: pointer;
    font-weight: 500;
    color: var(--ui-text-highlighted);
}

.article-unknown__pre {
    margin-top: 0.5rem;
    overflow-x: auto;
    font-size: 0.75rem;
    color: var(--ui-text-muted);
}

/* ── Empty state ── */
.article-empty {
    border-radius: 1rem;
    border: 2px dashed var(--ui-border);
    padding: 2rem;
    text-align: center;
    color: var(--ui-text-muted);
}
</style>
