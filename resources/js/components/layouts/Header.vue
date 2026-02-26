<script setup lang="ts">
import { computed, ref } from 'vue'
import HeaderDesktopNav from '@/components/layouts/header/HeaderDesktopNav.vue'
import HeaderSearch from '@/components/layouts/header/HeaderSearch.vue'
import HeaderActions from '@/components/layouts/header/HeaderActions.vue'
import HeaderMobileMenu from '@/components/layouts/header/HeaderMobileMenu.vue'
import { useStoreData } from '@/composables/useStoreData'

defineProps<{
    appName?: string
}>()

const mobileMenuOpen = ref(false)
const { headerBottomBarPages } = useStoreData()

const rightUtilityLinks = computed(() => {
    if (headerBottomBarPages.value.length > 0) {
        return headerBottomBarPages.value
    }

    return [{ label: 'Bantuan', to: '/help' }]
})
</script>

<template>
    <header class="sticky top-9 z-40 bg-white backdrop-blur-xl dark:bg-primary-950/80">
        <!-- ================================================================ -->
        <!--  ROW 1: Brand (left) | Search (center) | Actions (right)         -->
        <!-- ================================================================ -->
        <div class="border-b border-gray-200/60 dark:border-white/5">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-14 items-center gap-4">
                    <!-- LEFT: Brand -->
                    <div class="flex shrink-0 items-center">
                        <UButton to="/" color="neutral" variant="link" class="p-0">
                            <span class="flex items-center gap-2.5">
                                <div
                                    class="grid size-9 place-items-center rounded-xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm">
                                    <UIcon name="i-lucide-shopping-bag" class="size-4.5" />
                                </div>
                                <div class="hidden sm:block text-left leading-tight">
                                    <p class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">{{ appName
                                        }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">Premium Store
                                    </p>
                                </div>
                            </span>
                        </UButton>
                    </div>

                    <!-- CENTER: Search (desktop only) -->
                    <div class="hidden lg:flex flex-1 justify-center px-4">
                        <HeaderSearch />
                    </div>

                    <!-- RIGHT: Actions -->
                    <div class="flex items-center gap-1 ml-auto lg:ml-0">
                        <HeaderActions @open-menu="mobileMenuOpen = true" />
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================ -->
        <!--  ROW 2: Main Navigation Menu (Desktop Only)                      -->
        <!-- ================================================================ -->
        <nav class="hidden border-b border-gray-200/60 lg:block dark:border-white/5">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-12 items-center justify-between">
                    <HeaderDesktopNav />

                    <div class="flex items-center gap-6">
                        <ULink
                            v-for="link in rightUtilityLinks"
                            :key="link.to"
                            :to="link.to"
                            class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors"
                        >
                            {{ link.label }}
                        </ULink>
                    </div>
                </div>
            </div>
        </nav>

        <!--  MOBILE: Bottom Sheet Menu  -->
        <HeaderMobileMenu v-model:open="mobileMenuOpen" />
    </header>
</template>
