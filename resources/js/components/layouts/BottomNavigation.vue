<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useStoreData } from '@/composables/useStoreData'
import { useHeaderSlideover } from '@/composables/useHeaderSlideover'

type BottomNavLinkItem = {
    kind: 'link'
    label: string
    icon: string
    to: string
    badge?: number
}

type BottomNavPanelItem = {
    kind: 'panel'
    label: string
    icon: string
    panel: 'wishlist' | 'cart'
    badge?: number
}

type BottomNavItem = BottomNavLinkItem | BottomNavPanelItem

const page = usePage()
const { isLoggedIn, cartCount, wishlistCount, bottomMainPages } = useStoreData()
const { cartSlideoverOpen, wishlistSlideoverOpen, openCartSlideover, openWishlistSlideover } = useHeaderSlideover()

const navItems = computed<BottomNavItem[]>(() => {
    const baseItems: BottomNavItem[] = [
        { kind: 'link', label: 'Home', icon: 'i-lucide-house', to: '/' },
        { kind: 'link', label: 'Explore', icon: 'i-lucide-search', to: '/shop' },
    ]
    const dynamicBottomPage = bottomMainPages.value[0]

    if (dynamicBottomPage) {
        baseItems.push({
            kind: 'link',
            label: dynamicBottomPage.label,
            icon: 'i-lucide-file-text',
            to: dynamicBottomPage.to,
        })
    }

    if (!isLoggedIn.value) {
        return baseItems
    }

    return [
        ...baseItems,
        { kind: 'panel', label: 'Wishlist', icon: 'i-lucide-heart', panel: 'wishlist', badge: wishlistCount.value },
        { kind: 'panel', label: 'Cart', icon: 'i-lucide-shopping-cart', panel: 'cart', badge: cartCount.value },
        { kind: 'link', label: 'Account', icon: 'i-lucide-user', to: '/dashboard' },
    ]
})

function resolvePath(url: string): string {
    if (!url) {
        return '/'
    }

    if (url.startsWith('http://') || url.startsWith('https://')) {
        return new URL(url).pathname
    }

    const [path] = url.split(/[?#]/)

    return path || '/'
}

function normalizePath(path: string): string {
    if (path.length <= 1) {
        return path || '/'
    }

    return path.replace(/\/+$/, '')
}

const currentPath = computed(() => normalizePath(resolvePath(page.url)))

function isActive(to: string): boolean {
    const targetPath = normalizePath(resolvePath(to))

    if (targetPath === '/') {
        return currentPath.value === '/'
    }

    return currentPath.value === targetPath || currentPath.value.startsWith(`${targetPath}/`)
}

function isPanelActive(panel: 'wishlist' | 'cart'): boolean {
    return panel === 'wishlist' ? wishlistSlideoverOpen.value : cartSlideoverOpen.value
}

function isItemActive(item: BottomNavItem): boolean {
    if (item.kind === 'link') {
        return isActive(item.to)
    }

    return isPanelActive(item.panel)
}

function openPanel(panel: 'wishlist' | 'cart'): void {
    if (panel === 'wishlist') {
        openWishlistSlideover()
        return
    }

    openCartSlideover()
}
</script>

<template>
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-200 bg-white pb-safe backdrop-blur-xl dark:border-white/5 dark:bg-gray-950/80 lg:hidden">
        <div class="flex items-center justify-around h-16 px-2">
            <template v-for="item in navItems" :key="item.label">
                <Link
                    v-if="item.kind === 'link'"
                    :href="item.to"
                    class="relative flex flex-col items-center justify-center flex-1 gap-1 transition-colors duration-200"
                    :class="[isItemActive(item) ? 'text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']"
                >
                    <div class="relative">
                        <UIcon :name="item.icon" class="size-6" />
                        <span v-if="(item.badge ?? 0) > 0"
                            class="absolute -right-2 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary-600 px-1 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-gray-950">
                            {{ item.badge }}
                        </span>
                    </div>
                    <span class="text-[10px] font-medium leading-none">{{ item.label }}</span>

                    <div v-if="isItemActive(item)"
                        class="absolute -top-px h-0.5 w-6 rounded-full bg-gray-900 dark:bg-white" />
                </Link>

                <button
                    v-else
                    type="button"
                    class="relative flex flex-col items-center justify-center flex-1 gap-1 transition-colors duration-200"
                    :class="[isItemActive(item) ? 'text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']"
                    @click="openPanel(item.panel)"
                >
                    <div class="relative">
                        <UIcon :name="item.icon" class="size-6" />
                        <span v-if="(item.badge ?? 0) > 0"
                            class="absolute -right-2 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary-600 px-1 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-gray-950">
                            {{ item.badge }}
                        </span>
                    </div>
                    <span class="text-[10px] font-medium leading-none">{{ item.label }}</span>

                    <div v-if="isItemActive(item)"
                        class="absolute -top-px h-0.5 w-6 rounded-full bg-gray-900 dark:bg-white" />
                </button>
            </template>
        </div>
    </nav>
</template>

<style scoped>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
