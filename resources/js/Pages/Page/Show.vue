<script setup lang="ts">
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SeoHead from '@/components/SeoHead.vue'
import PageBlockRenderer from '@/components/page/PageBlockRenderer.vue'
import type { PageShowProps } from '@/types/page'

defineOptions({ layout: AppLayout })

const props = defineProps<PageShowProps>()

const structuredDataScripts = computed(() => {
    const payload = props.seo.structured_data ?? []

    return payload.map((item) => JSON.stringify(item))
})

const hasBlocks = computed(() => props.page.blocks.length > 0)
const hasFallbackContent = computed(() => props.page.content_html.trim() !== '')
</script>

<template>
    <SeoHead
        :title="props.seo.title"
        :description="props.seo.description"
        :canonical="props.seo.canonical"
        :robots="props.seo.robots"
        :image="props.seo.image ?? undefined"
    />

    <Head>
        <component
            v-for="(script, index) in structuredDataScripts"
            :key="`page-show-ld-${index}`"
            :is="'script'"
            type="application/ld+json"
            v-html="script"
        />
    </Head>

    <div class="min-h-screen bg-gray-50/60 py-8 transition-colors duration-300 dark:bg-gray-950">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            <UButton
                to="/"
                color="neutral"
                variant="outline"
                icon="i-lucide-arrow-left"
                size="sm"
                class="w-fit"
            >
                Kembali ke Beranda
            </UButton>

            <UCard class="rounded-3xl border border-default/80 bg-linear-to-br from-primary-50/60 via-white to-cyan-50/40 dark:from-primary-950/40 dark:via-gray-950 dark:to-cyan-950/20">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <UBadge color="primary" variant="soft" class="rounded-full">
                            Halaman
                        </UBadge>
                        <UBadge color="neutral" variant="subtle" class="rounded-full">
                            Template: {{ props.page.template }}
                        </UBadge>
                        <UBadge v-if="props.page.published_label" color="neutral" variant="subtle" class="rounded-full">
                            Update {{ props.page.published_label }}
                        </UBadge>
                    </div>
                    <h1 class="text-2xl font-bold leading-tight text-highlighted sm:text-4xl">
                        {{ props.page.title }}
                    </h1>
                    <p class="max-w-3xl text-sm leading-relaxed text-muted sm:text-base">
                        {{ props.page.excerpt }}
                    </p>
                </div>
            </UCard>

            <PageBlockRenderer v-if="hasBlocks" :blocks="props.page.blocks" />

            <UCard
                v-if="!hasBlocks && hasFallbackContent"
                class="rounded-3xl border border-default/80"
                :ui="{ body: 'p-5 sm:p-8' }"
            >
                <article class="prose prose-gray max-w-none dark:prose-invert" v-html="props.page.content_html" />
            </UCard>

            <UCard v-if="!hasBlocks && !hasFallbackContent" class="rounded-2xl border border-dashed border-default text-center">
                <div class="py-8">
                    <UIcon name="i-lucide-file-text" class="mx-auto mb-3 size-8 text-muted" />
                    <p class="text-sm text-muted">Konten halaman belum tersedia.</p>
                </div>
            </UCard>
        </div>
    </div>
</template>

