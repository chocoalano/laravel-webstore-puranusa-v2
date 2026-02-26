<script setup lang="ts">
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'

const props = defineProps<{
    title: string
    description?: string
    canonical?: string
    robots?: string
    image?: string
}>()

const page = usePage<{ appName?: string }>()

const siteName = computed(() => page.props.appName ?? 'Puranusa')
const ogTitle = computed(() => `${props.title} | ${siteName.value}`)
</script>

<template>
    <Head>
        <title>{{ title }}</title>

        <!-- Primary meta -->
        <meta v-if="description" name="description" :content="description" />
        <meta name="robots" :content="robots ?? 'index, follow'" />
        <link v-if="canonical" rel="canonical" :href="canonical" />

        <!-- Open Graph (Facebook, LinkedIn, WhatsApp, Google) -->
        <meta property="og:type" content="website" />
        <meta property="og:site_name" :content="siteName" />
        <meta property="og:title" :content="ogTitle" />
        <meta v-if="description" property="og:description" :content="description" />
        <meta v-if="canonical" property="og:url" :content="canonical" />
        <meta property="og:locale" content="id_ID" />
        <meta v-if="image" property="og:image" :content="image" />
        <meta v-if="image" property="og:image:width" content="1200" />
        <meta v-if="image" property="og:image:height" content="630" />

        <!-- Twitter / X Card -->
        <meta name="twitter:card" :content="image ? 'summary_large_image' : 'summary'" />
        <meta name="twitter:title" :content="ogTitle" />
        <meta v-if="description" name="twitter:description" :content="description" />
        <meta v-if="image" name="twitter:image" :content="image" />
    </Head>
</template>
