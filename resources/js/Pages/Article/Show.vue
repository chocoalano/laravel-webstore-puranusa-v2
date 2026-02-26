<script setup lang="ts">
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SeoHead from '@/components/SeoHead.vue'
import type { ArticleShowPageProps } from '@/types/article'
import { useArticleDetail } from '@/composables/useArticleDetail'
import ArticleDetailHeader from '@/components/article/ArticleDetailHeader.vue'
import ArticleContentRenderer from '@/components/article/ArticleContentRenderer.vue'
import ArticleRelatedPosts from '@/components/article/ArticleRelatedPosts.vue'

defineOptions({ layout: AppLayout })

const props = defineProps<ArticleShowPageProps>()

const { publishedDate, updatedDate, readTimeLabel, jsonLdScripts, breadcrumbItems } = useArticleDetail(
    props.article,
    props.seo,
)

const hasCoverImage = computed(() => !!props.article.cover_image)
const coverSrc = computed(() => props.article.cover_image || '')
</script>

<template>
    <SeoHead :title="props.seo.title" :description="props.seo.description" :canonical="props.seo.canonical"
        :robots="props.seo.robots" :image="props.seo.image ?? undefined" />

    <Head>
        <component v-for="(script, index) in jsonLdScripts" :key="`article-show-ld-${index}`" :is="'script'"
            type="application/ld+json" v-html="script" />
    </Head>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- HERO / COVER -->
        <div class="relative isolate">
            <div v-if="hasCoverImage" class="absolute inset-0 -z-10">
                <img :src="coverSrc" :alt="props.article.title" class="h-full w-full object-cover" />
                <div class="absolute inset-0 bg-linear-to-b from-black/55 via-black/35 to-gray-50 dark:to-gray-950" />
            </div>

            <div v-else class="absolute inset-0 -z-10">
                <div
                    class="h-full w-full bg-linear-to-b from-gray-900/10 via-gray-50 to-gray-50 dark:from-white/5 dark:via-gray-950 dark:to-gray-950" />
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-6">
                    <UButton to="/articles" color="neutral" variant="outline" icon="i-lucide-arrow-left" size="sm"
                        class="rounded-xl bg-white/80 backdrop-blur dark:bg-gray-950/60">
                        Kembali ke Artikel
                    </UButton>
                </div>

                <div class="pb-10 pt-4 sm:pb-14 sm:pt-6">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur dark:border-white/10">
                                {{ publishedDate }}
                            </span>
                            <span
                                class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur dark:border-white/10">
                                {{ readTimeLabel }}
                            </span>
                            <span v-if="updatedDate"
                                class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur dark:border-white/10">
                                Updated: {{ updatedDate }}
                            </span>
                        </div>

                        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">
                            {{ props.article.title }}
                        </h1>

                        <p v-if="props.seo?.description"
                            class="mt-4 max-w-2xl text-sm leading-6 text-white/85 sm:text-base">
                            {{ props.seo.description }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="pointer-events-none h-8 bg-linear-to-b from-transparent to-gray-50 dark:to-gray-950" />
        </div>

        <!-- BODY -->
        <div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
            <!-- ✅ SINGLE CARD (no double) -->
            <UCard
                class="-mt-10 rounded-3xl backdrop-blur dark:bg-gray-950/65"
                :ui="{ body: 'p-5 sm:p-8' }">
                <ArticleDetailHeader :article="props.article" :breadcrumb-items="breadcrumbItems"
                    :published-date="publishedDate" :updated-date="updatedDate" :read-time-label="readTimeLabel" />

                <USeparator class="my-6 dark:border-white/10" />

                <ArticleContentRenderer :blocks="props.article.blocks" />
            </UCard>

            <!-- Related (tanpa card supaya tidak terasa bertumpuk) -->
            <div class="mt-10">
                <ArticleRelatedPosts :related-articles="props.relatedArticles" />
            </div>
        </div>
    </div>
</template>
