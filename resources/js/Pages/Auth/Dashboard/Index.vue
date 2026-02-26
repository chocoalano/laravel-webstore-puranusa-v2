<script setup lang="ts">
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import SeoHead from '@/components/SeoHead.vue'
import DashboardAsideMenu from '@/components/dashboard/index/DashboardAsideMenu.vue'
import DashboardPageHeader from '@/components/dashboard/index/DashboardPageHeader.vue'
import { useDashboardSections } from '@/composables/useDashboardSections'
import AppLayout from '@/layouts/AppLayout.vue'
import type { DashboardPageProps } from '@/types/dashboard'

defineOptions({ layout: AppLayout })

const page = usePage<DashboardPageProps>()
const props = computed(() => page.props)

const seo = computed(() => props.value.seo)
const customer = computed(() => props.value.customer ?? null)

const {
    active,
    currentComponent,
    currentComponentProps,
    currentComponentListeners,
    asideLinks,
    setActive,
} = useDashboardSections(props, page.url)

function logout(): void {
    router.post('/logout')
}
</script>

<template>
    <SeoHead :title="seo.title" :description="seo.description" :canonical="seo.canonical" />

    <UPage class="min-h-screen bg-gray-50/60 dark:bg-gray-950">
        <DashboardPageHeader
            :customer="customer"
            :promo-active="props.stats?.promo_active"
            @logout="logout"
        />

        <UPageBody class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 pb-10">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <DashboardAsideMenu
                    :active="active"
                    :links="asideLinks"
                    :wallet-balance="props.stats?.wallet_balance"
                    @update:active="setActive"
                />

                <div class="lg:col-span-9">
                    <component
                        :is="currentComponent"
                        :key="active"
                        v-bind="currentComponentProps"
                        v-on="currentComponentListeners"
                    />
                </div>
            </div>
        </UPageBody>
    </UPage>
</template>
