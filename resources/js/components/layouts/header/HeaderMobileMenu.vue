<script setup lang="ts">
import { computed } from 'vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { router, usePage } from '@inertiajs/vue3'
import { useCategories } from '@/composables/useCategories'
import { useStoreData } from '@/composables/useStoreData'

const modelOpen = defineModel<boolean>('open')

const page = usePage()
const { categories } = useCategories()
const { wishlistCount, cartCount, authCustomer, isLoggedIn, headerNavbarPages } = useStoreData()

function logout() {
    modelOpen.value = false
    router.post('/logout')
}

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

function resolveQuery(url: string): URLSearchParams {
    if (!url) {
        return new URLSearchParams()
    }

    if (url.startsWith('http://') || url.startsWith('https://')) {
        return new URL(url).searchParams
    }

    const [, query = ''] = url.split('?')
    const [queryString] = query.split('#')

    return new URLSearchParams(queryString ?? '')
}

function normalizePath(path: string): string {
    if (path.length <= 1) {
        return path || '/'
    }

    return path.replace(/\/+$/, '')
}

const fullPath = computed(() => page.url ?? '/')
const pathOnly = computed(() => normalizePath(resolvePath(fullPath.value)))
const queryParams = computed(() => resolveQuery(fullPath.value))

function isPath(path: string): boolean {
    return pathOnly.value === normalizePath(path)
}

function isPathPrefix(path: string): boolean {
    const normalized = normalizePath(path)

    return pathOnly.value === normalized || pathOnly.value.startsWith(`${normalized}/`)
}

type MobileConfig = {
    key: string
    label: string
    icon: string
    to?: string
    activeWhen?: (context: { fullPath: string; pathOnly: string; query: URLSearchParams }) => boolean
    children?: () => NavigationMenuItem[]
}

function isActive(c: MobileConfig): boolean {
    if (c.activeWhen) {
        return c.activeWhen({
            fullPath: fullPath.value,
            pathOnly: pathOnly.value,
            query: queryParams.value,
        })
    }

    if (c.to) {
        return isPath(resolvePath(c.to))
    }

    return false
}

// ---- Config menu (tinggal tambah/ubah disini) ----
const mobileConfig = computed<MobileConfig[]>(() => {
    const baseItems: MobileConfig[] = [
        {
        key: 'home',
        label: 'Beranda',
        icon: 'i-lucide-home',
        to: '/',
        activeWhen: () => isPath('/'),
    },
        {
        key: 'shop',
        label: 'Toko',
        icon: 'i-lucide-store',
        to: '/shop',
        activeWhen: () => isPathPrefix('/shop'),
    },
        {
        key: 'categories',
        label: 'Kategori',
        icon: 'i-lucide-layout-grid',
        children: () =>
            categories.value.map((c) => ({
                label: c.label,
                icon: c.icon,
                to: c.to,
            })),
        activeWhen: () => isPathPrefix('/shop'),
    },
        {
        key: 'new',
        label: 'New Arrivals',
        icon: 'i-lucide-sparkles',
        to: '/shop?products=new',
        activeWhen: ({ pathOnly: path, query }) => path === '/shop' && query.get('products') === 'new',
    },
        {
        key: 'articles',
        label: 'Artikel',
        icon: 'i-lucide-newspaper',
        to: '/articles',
        activeWhen: () => isPathPrefix('/articles'),
        },
    ]

    const dynamicItems: MobileConfig[] = headerNavbarPages.value.map((page, index) => ({
        key: `page-${index}-${page.to}`,
        label: page.label,
        icon: 'i-lucide-file-text',
        to: page.to,
        activeWhen: ({ pathOnly: path }) => path === page.to,
    }))

    const merged = [...baseItems, ...dynamicItems]
    const seen = new Set<string>()

    return merged.filter((item) => {
        if (!item.to) {
            return true
        }

        const key = normalizePath(resolvePath(item.to))

        if (seen.has(key)) {
            return false
        }

        seen.add(key)

        return true
    })
})

// ---- Output final utk UNavigationMenu ----
const mobileItems = computed<NavigationMenuItem[]>(() =>
    mobileConfig.value.map((c) => ({
        label: c.label,
        icon: c.icon,
        ...(c.to ? { to: c.to } : {}),
        ...(c.children ? { children: c.children() } : {}),
        active: isActive(c),
    })),
)
</script>

<template>
    <UDrawer v-model:open="modelOpen" title="Menu" description="Navigasi situs" class="lg:hidden" :ui="{
        overlay: 'z-[60]',
        content: 'z-[61] max-h-[65dvh]',
        body: 'overflow-y-auto flex-1',
    }">
        <template #default />

        <template #body>
            <div class="space-y-4">
                <!-- Mobile search -->
                <UInput placeholder="Cari produk, brand, kategori…" icon="i-lucide-search" size="lg" class="w-full"
                    :ui="{ base: 'h-11 rounded-xl bg-gray-100 dark:bg-white/5 border-0' }" />

                <!-- Info akun (login) / tombol auth (belum login) -->
                <div v-if="isLoggedIn"
                    class="flex items-center gap-3 rounded-2xl border border-indigo-200/60 bg-indigo-50/80 p-3 dark:border-indigo-700/30 dark:bg-indigo-950/40">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white">
                        {{
                            authCustomer?.name
                                .split(' ')
                                .slice(0, 2)
                                .map((w: string) => w.charAt(0).toUpperCase())
                        .join('')
                        }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                            {{ authCustomer?.name }}
                        </p>
                        <p class="truncate text-xs text-gray-400">{{ authCustomer?.email }}</p>
                    </div>
                    <UButton icon="i-lucide-log-out" color="error" variant="ghost" size="sm" class="shrink-0 rounded-xl"
                        aria-label="Keluar" @click="logout" />
                </div>

                <div v-else class="grid grid-cols-2 gap-2">
                    <UButton to="/login" color="neutral" variant="outline"
                        class="h-auto justify-center gap-1.5 rounded-xl py-2.5" @click="modelOpen = false">
                        <UIcon name="i-lucide-log-in" class="size-4" />
                        <span class="text-sm font-medium">Masuk</span>
                    </UButton>
                    <UButton to="/register" color="primary" variant="solid"
                        class="h-auto justify-center gap-1.5 rounded-xl py-2.5" @click="modelOpen = false">
                        <UIcon name="i-lucide-user-plus" class="size-4" />
                        <span class="text-sm font-medium">Daftar</span>
                    </UButton>
                </div>

                <!-- Mobile quick actions -->
                <div class="grid grid-cols-3 gap-2">
                    <UButton v-if="isLoggedIn" to="/wishlist" icon="i-lucide-heart" color="neutral" variant="outline"
                        class="h-auto flex-col justify-center gap-1 rounded-xl py-3" @click="modelOpen = false">
                        <span class="text-xs">Wishlist</span>
                        <UBadge v-if="wishlistCount > 0" :label="String(wishlistCount)" color="neutral" variant="solid"
                            size="xs" />
                    </UButton>

                    <UButton to="/cart" icon="i-lucide-shopping-cart" color="neutral" variant="outline"
                        :class="['h-auto flex-col justify-center gap-1 rounded-xl py-3', !isLoggedIn && 'col-span-2']"
                        @click="modelOpen = false">
                        <span class="text-xs">Keranjang</span>
                        <UBadge v-if="cartCount > 0" :label="String(cartCount)" color="neutral" variant="solid"
                            size="xs" />
                    </UButton>

                    <UButton v-if="isLoggedIn" to="/account" icon="i-lucide-user" color="neutral" variant="outline"
                        class="h-auto flex-col justify-center gap-1 rounded-xl py-3" @click="modelOpen = false">
                        <span class="text-xs">Akun</span>
                    </UButton>
                </div>

                <USeparator />

                <!-- Mobile navigation -->
                <UNavigationMenu :items="mobileItems" orientation="vertical" class="-mx-2" />

                <USeparator />

                <!-- Mobile utility links -->
                <div class="flex flex-col gap-1">
                    <UButton to="/orders" icon="i-lucide-package-search" color="neutral" variant="ghost"
                        class="justify-start rounded-lg" @click="modelOpen = false">
                        Lacak Pesanan
                    </UButton>
                    <UButton to="/help" icon="i-lucide-circle-help" color="neutral" variant="ghost"
                        class="justify-start rounded-lg" @click="modelOpen = false">
                        Bantuan
                    </UButton>
                </div>
            </div>
        </template>
    </UDrawer>
</template>
