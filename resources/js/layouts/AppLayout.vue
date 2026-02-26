<script setup lang="ts">
import AppBackground from '@/components/layouts/AppBackground.vue'
import AppFooter from '@/components/layouts/AppFooter.vue'
import Header from '@/components/layouts/Header.vue'
import Topbar from '@/components/layouts/Topbar.vue'
import BottomNavigation from '@/components/layouts/BottomNavigation.vue'
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

type ImpersonationPayload = {
    active?: boolean
    admin_name?: string | null
    customer_name?: string | null
    stop_url?: string | null
}

const page = usePage<{ appName?: string; categories?: any[]; wishlistCount?: number; cartCount?: number; impersonation?: ImpersonationPayload }>()

const appName = computed(() => page.props.appName ?? 'Store')
const categories = computed(() => page.props.categories ?? [])
const wishlistCount = computed(() => page.props.wishlistCount ?? 0)
const cartCount = computed(() => page.props.cartCount ?? 0)
const impersonation = computed<ImpersonationPayload>(() => page.props.impersonation ?? { active: false })
const isImpersonating = computed(() => !!impersonation.value.active)
const impersonationStopUrl = computed(() => impersonation.value.stop_url ?? '/impersonation/stop')
const isStoppingImpersonation = ref(false)

function stopImpersonation(): void {
    if (!isImpersonating.value || isStoppingImpersonation.value) {
        return
    }

    isStoppingImpersonation.value = true

    router.post(impersonationStopUrl.value, {}, {
        preserveScroll: true,
        onFinish: () => {
            isStoppingImpersonation.value = false
        }
    })
}
</script>

<template>
    <UApp :locale="{ messages: { header: { title: 'Menu', description: 'Navigasi situs' } } }">
        <div class="relative flex min-h-dvh flex-col">
            <AppBackground />

            <Topbar />

            <div v-if="isImpersonating" class="relative z-20 border-y border-amber-300/60 bg-amber-50/95 dark:border-amber-700/50 dark:bg-amber-950/60">
                <div class="mx-auto flex max-w-screen-2xl flex-col gap-2 px-4 py-2 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p class="text-xs sm:text-sm font-medium text-amber-900 dark:text-amber-100">
                        Mode impersonasi aktif:
                        <span class="font-semibold">{{ impersonation.admin_name || 'Admin' }}</span>
                        sedang masuk sebagai
                        <span class="font-semibold">{{ impersonation.customer_name || 'customer' }}</span>.
                    </p>
                    <UButton
                        size="xs"
                        color="error"
                        variant="solid"
                        class="rounded-lg"
                        :loading="isStoppingImpersonation"
                        @click="stopImpersonation"
                    >
                        Akhiri Impersonasi
                    </UButton>
                </div>
            </div>

            <Header :appName="appName" />

            <UMain class="flex-1 pb-20 lg:pb-0">
                <slot />
            </UMain>

            <AppFooter :appName="appName" />

            <BottomNavigation />
        </div>
    </UApp>
</template>
