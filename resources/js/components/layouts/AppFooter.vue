<script setup lang="ts">
import FooterTrustBar from '@/components/layouts/footer/FooterTrustBar.vue'
import FooterBrandSection from '@/components/layouts/footer/FooterBrandSection.vue'
import FooterLinkGroups from '@/components/layouts/footer/FooterLinkGroups.vue'
import FooterBottom from '@/components/layouts/footer/FooterBottom.vue'

defineProps<{
    appName: string
}>()
</script>

<template>
    <footer class="relative overflow-hidden">
        <!-- Trust bar -->
        <FooterTrustBar />

        <!-- Main footer content -->
        <div class="bg-white text-gray-700 dark:bg-gray-950 dark:text-gray-300">
            <div class="mx-auto max-w-screen-2xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-12">
                    <!-- Brand & Newsletter section -->
                    <FooterBrandSection />

                    <!-- Link columns (Belanja, Layanan, Perusahaan) -->
                    <FooterLinkGroups />
                </div>
            </div>

            <!-- Payment & Copy section -->
            <FooterBottom />
        </div>
    </footer>
</template>