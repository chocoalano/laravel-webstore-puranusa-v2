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
    <UCard class="rounded-2xl">
        <article class="prose prose-gray max-w-none dark:prose-invert">
            <template v-for="(block, index) in props.blocks" :key="`block-${index}`">
                <component
                    :is="headingTag(block.level)"
                    v-if="block.type === 'heading'"
                    class="font-bold tracking-tight"
                >
                    {{ block.text }}
                </component>

                <div
                    v-else-if="block.type === 'rich_text'"
                    class="leading-relaxed"
                    v-html="block.html"
                />

                <figure v-else-if="block.type === 'image'" class="my-8">
                    <div class="overflow-hidden rounded-2xl bg-elevated">
                        <img
                            v-if="block.url"
                            :src="block.url"
                            :alt="block.alt || 'Gambar artikel'"
                            class="w-full object-cover"
                            loading="lazy"
                        >
                        <div v-else class="grid h-56 w-full place-items-center text-muted">
                            <UIcon name="i-lucide-image-off" class="size-7" />
                        </div>
                    </div>
                    <figcaption v-if="block.caption" class="mt-2 text-sm text-muted">
                        {{ block.caption }}
                    </figcaption>
                </figure>

                <component
                    :is="block.ordered ? 'ol' : 'ul'"
                    v-else-if="block.type === 'list'"
                    class="space-y-2"
                >
                    <li v-for="(item, itemIndex) in block.items" :key="`item-${index}-${itemIndex}`" v-html="item" />
                </component>

                <blockquote v-else-if="block.type === 'quote'" class="border-primary pl-4">
                    <p>{{ block.quote }}</p>
                    <cite v-if="block.cite" class="not-italic text-muted">— {{ block.cite }}</cite>
                </blockquote>

                <hr v-else-if="block.type === 'divider'" class="my-8 border-default">

                <details v-else class="rounded-xl border border-default p-3 text-sm">
                    <summary class="cursor-pointer font-medium text-highlighted">Blok tidak dikenal: {{ block.block_type }}</summary>
                    <pre class="mt-2 overflow-x-auto text-xs text-muted">{{ JSON.stringify(block.data, null, 2) }}</pre>
                </details>
            </template>

            <div v-if="props.blocks.length === 0" class="rounded-2xl border border-dashed border-default p-8 text-center text-muted">
                Konten artikel belum tersedia.
            </div>
        </article>
    </UCard>
</template>
