<script setup lang="ts">
import { computed } from 'vue'
import type { ShopFilters } from '@/composables/useShopCatalog'
import type { DropdownMenuItem } from '@nuxt/ui'
import { useStoreData } from '@/composables/useStoreData'

const props = defineProps<{
    currentFilters: ShopFilters
    activeCategoryLabel: string
    totalProducts: number
    categoriesCount: number
    hasActiveFilters: boolean
    activeFilterCount: number
}>()

const { isLoggedIn } = useStoreData()

const breadcrumbItems = computed(() => [
    { label: 'Home', icon: 'i-lucide-home', to: '/' },
    { label: 'Katalog', to: '/shop' },
    ...(props.currentFilters.category ? [{ label: props.activeCategoryLabel }] : [])
])

const categoryLabel = computed(() =>
    props.currentFilters.category ? props.activeCategoryLabel : 'Semua Kategori'
)

const menuItems = computed<DropdownMenuItem[][]>(() => [
    [
        { label: 'Daftar', icon: 'i-lucide-user-plus', to: '/register' },
        { label: 'Masuk', icon: 'i-lucide-log-in', to: '/login' }
    ]
])
</script>

<template>
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-2 lg:px-8 py-6 sm:py-4">
        <UCard class="rounded-2xl overflow-hidden">
            <template #header>
                <div class="flex flex-col gap-3">
                    <!-- Breadcrumb + badges + actions -->
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <UBreadcrumb :items="breadcrumbItems" />

                        <div class="flex flex-wrap items-center gap-2">
                            <UBadge color="primary" variant="soft" size="sm">
                                <UIcon name="i-lucide-sparkles" class="mr-1 size-3.5" />
                                Premium
                            </UBadge>

                            <UBadge color="neutral" variant="subtle" size="sm">
                                <UIcon name="i-lucide-tag" class="mr-1 size-3.5" />
                                {{ categoryLabel }}
                            </UBadge>

                            <UBadge v-if="hasActiveFilters" color="neutral" variant="subtle" size="sm">
                                <UIcon name="i-lucide-filter" class="mr-1 size-3.5" />
                                {{ activeFilterCount }} filter
                            </UBadge>

                            <!-- Desktop actions -->
                            <div v-if="!isLoggedIn" class="hidden sm:flex items-center gap-2 ml-1">
                                <UButton to="/register" size="sm" color="primary" variant="soft"
                                    icon="i-lucide-user-plus">
                                    Daftar
                                </UButton>

                                <UButton to="/login" size="sm" color="neutral" variant="outline" icon="i-lucide-log-in">
                                    Masuk
                                </UButton>
                            </div>

                            <!-- Mobile actions -->
                            <UDropdownMenu v-if="!isLoggedIn" :items="menuItems" class="sm:hidden">
                                <UButton size="sm" color="neutral" variant="outline" icon="i-lucide-more-horizontal">
                                    Menu
                                </UButton>
                            </UDropdownMenu>
                        </div>
                    </div>
                </div>
            </template>

            <div>
                <!-- Title + description -->
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                            <UIcon name="i-lucide-store" class="size-5 text-primary" />
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-highlighted">
                            Katalog Produk
                        </h1>
                    </div>

                    <p class="text-sm text-muted max-w-2xl">
                        Jelajahi produk premium dengan filter cepat, rentang harga fleksibel, dan sorting yang rapi.
                    </p>
                </div>

                <USeparator class="my-5" />

                <!-- Stats row (default look) -->
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-primary/10">
                            <UIcon name="i-lucide-package" class="size-5 text-primary" />
                        </div>
                        <div>
                            <p class="text-xs text-muted">Produk</p>
                            <p class="text-lg font-bold tabular-nums text-highlighted">{{ totalProducts }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-elevated">
                            <UIcon name="i-lucide-layers" class="size-5 text-muted" />
                        </div>
                        <div>
                            <p class="text-xs text-muted">Kategori</p>
                            <p class="text-lg font-bold tabular-nums text-highlighted">{{ categoriesCount }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-elevated">
                            <UIcon name="i-lucide-sliders-horizontal" class="size-5 text-muted" />
                        </div>
                        <div>
                            <p class="text-xs text-muted">Filter aktif</p>
                            <p class="text-lg font-bold tabular-nums text-highlighted">
                                {{ hasActiveFilters ? activeFilterCount : 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </UCard>
    </div>
</template>
