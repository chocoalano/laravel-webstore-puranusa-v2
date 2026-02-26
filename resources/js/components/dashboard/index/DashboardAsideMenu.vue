<script setup lang="ts">
import { useDashboard } from '@/composables/useDashboard'
import { useDashboardAsideMenuState } from '@/composables/useDashboardAsideMenuState'
import type { DashboardAsideLink, DashboardSectionKey } from '@/types/dashboard'
import DashboardAsideMenuNav from '@/components/dashboard/index/aside/DashboardAsideMenuNav.vue'
import DashboardAsideMenuTrigger from '@/components/dashboard/index/aside/DashboardAsideMenuTrigger.vue'
import DashboardAsideTipsCard from '@/components/dashboard/index/aside/DashboardAsideTipsCard.vue'

const props = withDefaults(
    defineProps<{
        active: DashboardSectionKey
        links: DashboardAsideLink[]
        walletBalance?: number | null
    }>(),
    {
        walletBalance: 0,
    }
)

const emit = defineEmits<{
    (e: 'update:active', value: DashboardSectionKey): void
}>()

const { formatIDR } = useDashboard()
const { mobileMenuOpen, openMobileMenu, selectSection } = useDashboardAsideMenuState({
    onSelect: (section) => emit('update:active', section),
})

function selectDesktopSection(section: DashboardSectionKey): void {
    selectSection(section)
}

function selectMobileSection(section: DashboardSectionKey): void {
    selectSection(section, true)
}
</script>

<template>
    <div class="lg:hidden">
        <DashboardAsideMenuTrigger :wallet-balance="props.walletBalance" @open="openMobileMenu" />

        <USlideover
            v-model:open="mobileMenuOpen"
            :portal="true"
            side="left"
            title="Menu Akun"
            description="Kelola akun & aktivitasmu"
            :ui="{ overlay: 'z-[90]', content: 'z-[100] w-full sm:max-w-sm' }"
        >
            <template #body>
                <DashboardAsideMenuNav
                    :active="props.active"
                    :links="props.links"
                    key-prefix="mobile-aside"
                    @select="selectMobileSection"
                />

                <div class="mt-5">
                    <DashboardAsideTipsCard />
                </div>
            </template>
        </USlideover>
    </div>

    <UPageAside class="hidden lg:block lg:col-span-3">
        <UCard class="rounded-2xl">
            <template #header>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Menu Akun</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kelola akun & aktivitasmu</p>
                    </div>
                    <UBadge :label="formatIDR(props.walletBalance ?? 0)" color="primary" variant="soft" class="rounded-full" />
                </div>
            </template>

            <DashboardAsideMenuNav
                :active="props.active"
                :links="props.links"
                key-prefix="desktop-aside"
                @select="selectDesktopSection"
            />

            <template #footer>
                <DashboardAsideTipsCard />
            </template>
        </UCard>
    </UPageAside>
</template>
