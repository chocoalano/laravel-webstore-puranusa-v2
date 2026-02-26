<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from '@nuxt/ui/runtime/vue/stubs/inertia.js'
import { useCategories } from '@/composables/useCategories'
import { useStoreData } from '@/composables/useStoreData'

type NavItem =
    | {
        key: string
        label: string
        to: string
        icon: string
        kind?: 'link'
        activeWhen?: (fullPath: string, pathOnly: string) => boolean
    }
    | {
        key: string
        label: string
        icon: string
        kind: 'categories'
        activeWhen?: (fullPath: string, pathOnly: string) => boolean
    }

const route = useRoute()
const { categories } = useCategories()
const { headerNavbarPages } = useStoreData()

const navLinkClass =
    'inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors'
const navLinkActiveClass = 'text-gray-900 dark:text-white'

// Helpers
const fullPath = computed(() => route.fullPath ?? '/')
const pathOnly = computed(() => (fullPath.value.split('?')[0] || '/'))

const isActive = (item: NavItem) => {
    if (item.activeWhen) return item.activeWhen(fullPath.value, pathOnly.value)
    if ('to' in item) return pathOnly.value === item.to
    return false
}

const navItems = computed<NavItem[]>(() => {
    const baseItems: NavItem[] = [
        {
            key: 'home',
            kind: 'link',
            label: 'Beranda',
            to: '/',
            icon: 'i-lucide-home',
            activeWhen: (_, path) => path === '/',
        },
        {
            key: 'shop',
            kind: 'link',
            label: 'Toko',
            to: '/shop',
            icon: 'i-lucide-store',
            activeWhen: (_, path) => path === '/shop',
        },
        {
            key: 'categories',
            kind: 'categories',
            label: 'Kategori',
            icon: 'i-lucide-layout-grid',
            activeWhen: (_, path) => path.startsWith('/shop'),
        },
        {
            key: 'new',
            kind: 'link',
            label: 'New Arrivals',
            to: '/shop?products=new',
            icon: 'i-lucide-sparkles',
            // anggap aktif untuk semua /shop (atau bisa dipersempit ke query "products=new")
            activeWhen: (fp, path) => path === '/shop' && fp.includes('products=new'),
        },
        {
            key: 'articles',
            kind: 'link',
            label: 'Artikel',
            to: '/articles',
            icon: 'i-lucide-newspaper',
            activeWhen: (_, path) => path.startsWith('/articles'),
        },
    ]

    const dynamicItems: NavItem[] = headerNavbarPages.value.map((page, index) => ({
        key: `page-${index}-${page.to}`,
        kind: 'link',
        label: page.label,
        to: page.to,
        icon: 'i-lucide-file-text',
        activeWhen: (_, path) => path === page.to,
    }))

    const mergedItems = [...baseItems, ...dynamicItems]
    const seen = new Set<string>()

    return mergedItems.filter((item) => {
        if (!('to' in item)) {
            return true
        }

        const key = item.to.split('?')[0]
        if (seen.has(key)) {
            return false
        }

        seen.add(key)
        return true
    })
})
</script>

<template>
    <div class="flex items-center">
        <template v-for="item in navItems" :key="item.key">
            <!-- Categories (Megamenu) -->
            <UPopover v-if="item.kind === 'categories'" mode="hover" :open-delay="100"
                :content="{ align: 'start', side: 'bottom', sideOffset: 4 }">
                <UButton color="neutral" variant="link" trailing-icon="i-lucide-chevron-down"
                    :class="[navLinkClass, isActive(item) && navLinkActiveClass]">
                    <UIcon :name="item.icon" class="size-4" />
                    {{ item.label }}
                </UButton>

                <template #content>
                    <div class="p-4 w-150 dark:bg-gray-950/80">
                        <div class="grid grid-cols-2 gap-4">
                            <ULink v-for="cat in categories" :key="cat.to" :to="cat.to"
                                class="group flex items-start gap-3 rounded-xl p-3 transition-colors hover:bg-gray-100 dark:hover:bg-white/5">
                                <div
                                    class="grid size-10 place-items-center rounded-lg bg-gray-100 transition-colors group-hover:bg-white dark:bg-white/5 dark:group-hover:bg-white/10">
                                    <UIcon :name="cat.icon" class="size-5 text-gray-600 dark:text-gray-300" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ cat.label }}</p>
                                    <p class="mt-0.5 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ cat.description }}
                                    </p>
                                </div>
                            </ULink>
                        </div>

                        <USeparator class="my-4 dark:border-white/10" />

                        <div class="flex justify-end">
                            <UButton to="/shop" variant="ghost" color="neutral" size="sm"
                                trailing-icon="i-lucide-arrow-right">
                                Lihat Semua Kategori
                            </UButton>
                        </div>
                    </div>
                </template>
            </UPopover>

            <!-- Standard Link -->
            <UButton v-else :to="item.to" color="neutral" variant="link"
                :class="[navLinkClass, isActive(item) && navLinkActiveClass]">
                <UIcon :name="item.icon" class="size-4" />
                {{ item.label }}
            </UButton>
        </template>
    </div>
</template>
