<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import CarouselHero from '@/components/home/CarouselHero.vue'
import CategoryGrid from '@/components/home/CategoryGrid.vue'
import FeaturedProducts from '@/components/home/FeaturedProducts.vue'
import ProductCTA from '@/components/home/ProductCTA.vue'
import BrandShowcase from '@/components/home/BrandShowcase.vue'
import AffiliateCTA from '@/components/home/AffiliateCTA.vue'
import TestimonialSection from '@/components/home/TestimonialSection.vue'
import FeatureHighlights from '@/components/home/FeatureHighlights.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { Product } from '@/components/product/ProductCard.vue'

defineOptions({ layout: AppLayout })

interface HeroBanner {
    id: number
    name: string
    description: string | null
    image: string | null
    slug: string | null
    type: string
    code: string
}

interface Brand {
    name: string
    slug: string
    productCount: number
}

defineProps<{
    heroBanners?: HeroBanner[]
    featuredProducts?: Product[]
    brands?: Brand[]
}>()

const page = usePage<{ categories: any[] }>()
const categories = computed(() => page.props.categories)
</script>

<template>
    <div>
        <!-- Hero Carousel -->
        <CarouselHero :banners="heroBanners ?? []" />

        <!-- Feature Highlights (trust strip) -->
        <FeatureHighlights />

        <!-- Categories + Featured Products: satu background menyatu -->
        <div class="relative overflow-hidden">
            <!-- Shared background -->
            <div class="pointer-events-none absolute inset-0 -z-10">
                <div
                    class="absolute inset-0 bg-linear-to-b from-indigo-50/80 via-white/90 to-violet-50/60 dark:from-indigo-950/50 dark:via-gray-950 dark:to-purple-950/40">
                </div>
                <!-- Blobs tersebar sepanjang kedua section -->
                <div
                    class="absolute -top-24 -right-20 h-96 w-96 rounded-full bg-violet-300/30 blur-3xl dark:bg-violet-700/20">
                </div>
                <div
                    class="absolute top-1/3 -left-20 h-80 w-80 rounded-full bg-blue-300/25 blur-3xl dark:bg-blue-700/15">
                </div>
                <div
                    class="absolute top-2/3 -right-16 h-72 w-72 rounded-full bg-indigo-300/25 blur-3xl dark:bg-indigo-700/15">
                </div>
                <div
                    class="absolute -bottom-20 left-1/4 h-80 w-80 rounded-full bg-purple-300/20 blur-3xl dark:bg-purple-700/12">
                </div>
                <!-- Dot pattern -->
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle,#6366f120_1px,transparent_1px)] bg-size-[28px_28px]">
                </div>
            </div>

            <CategoryGrid :categories="categories ?? []" :hide-background="true" />
            <FeaturedProducts :products="featuredProducts" :hide-background="true" />
        </div>

        <!-- Product Highlight CTA -->
        <ProductCTA />

        <!-- Brand Showcase -->
        <BrandShowcase :brands="brands" />

        <!-- Affiliate / Referral Business CTA -->
        <AffiliateCTA />

        <!-- Testimonials -->
        <TestimonialSection />
    </div>
</template>
