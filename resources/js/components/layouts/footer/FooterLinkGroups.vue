<script setup lang="ts">
import { computed } from 'vue'
import { useStoreData } from '@/composables/useStoreData'

interface FooterLinkItem {
    label: string
    to: string
}

interface FooterLinkGroup {
    group: string
    items: FooterLinkItem[]
}

const { allFooterPages, storeEmail, storePhone } = useStoreData()

const footerGroupTitles = ['Belanja', 'Dukungan', 'Informasi'] as const

// Utility functions tetap sama, hanya logic display yang kita poles
function uniqueByTo(items: FooterLinkItem[]): FooterLinkItem[] {
    const seen = new Set<string>()
    return items.filter((item) => {
        if (seen.has(item.to)) return false
        seen.add(item.to)
        return true
    })
}

function splitEvenly<T>(items: T[], parts: number): T[][] {
    const safeParts = Math.max(1, parts)
    const baseSize = Math.floor(items.length / safeParts)
    const remainder = items.length % safeParts
    const result: T[][] = Array.from({ length: safeParts }, () => [])
    let cursor = 0
    for (let index = 0; index < safeParts; index += 1) {
        const size = baseSize + (index < remainder ? 1 : 0)
        result[index] = items.slice(cursor, cursor + size)
        cursor += size
    }
    return result
}

const modelPageLinks = computed<FooterLinkItem[]>(() => uniqueByTo(allFooterPages.value))

const footerGroups = computed<FooterLinkGroup[]>(() => {
    const chunks = splitEvenly(modelPageLinks.value, footerGroupTitles.length)
    return footerGroupTitles.map((title, index) => ({
        group: title,
        items: chunks[index] ?? []
    }))
})
</script>

<template>
    <div class="lg:col-span-7 lg:col-start-6">
        <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
            <div
                v-for="group in footerGroups"
                :key="group.group"
                class="flex flex-col gap-4"
            >
                <div class="relative">
                    <h3 class="text-[11px] font-bold uppercase tracking-[0.2em] text-gray-900 dark:text-white">
                        {{ group.group }}
                    </h3>
                    <div class="mt-2 h-0.5 w-4 bg-primary-500 rounded-full" />
                </div>

                <ul class="flex flex-col gap-y-2.5">
                    <li v-for="item in group.items" :key="`${group.group}-${item.to}`">
                        <ULink
                            :to="item.to"
                            class="text-sm font-medium text-gray-500 transition-all duration-200 hover:text-primary-600 hover:translate-x-1 inline-flex items-center dark:text-gray-400 dark:hover:text-primary-400"
                        >
                            {{ item.label }}
                        </ULink>
                    </li>

                    <li v-if="group.items.length === 0" class="text-xs italic text-gray-400 dark:text-gray-600">
                        Belum tersedia
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12 overflow-hidden rounded-2xl border border-gray-100 bg-gray-50/50 p-1 dark:border-gray-800 dark:bg-gray-900/50">
            <div class="grid grid-cols-1 divide-y divide-gray-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0 dark:divide-gray-800">
                <div class="group flex items-center gap-4 p-4 transition-colors hover:bg-white dark:hover:bg-gray-800/50">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-200 group-hover:bg-primary-50 group-hover:ring-primary-100 dark:bg-gray-800 dark:ring-gray-700 dark:group-hover:bg-primary-950 dark:group-hover:ring-primary-900">
                        <UIcon name="i-lucide-mail" class="size-5 text-gray-500 group-hover:text-primary-600 dark:text-gray-400" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-tight text-gray-400">Email Support</p>
                        <p class="truncate text-sm font-semibold text-gray-700 dark:text-gray-200">{{ storeEmail }}</p>
                    </div>
                </div>

                <div class="group flex items-center gap-4 p-4 transition-colors hover:bg-white dark:hover:bg-gray-800/50">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-200 group-hover:bg-primary-50 group-hover:ring-primary-100 dark:bg-gray-800 dark:ring-gray-700 dark:group-hover:bg-primary-950 dark:group-hover:ring-primary-900">
                        <UIcon name="i-lucide-phone-call" class="size-5 text-gray-500 group-hover:text-primary-600 dark:text-gray-400" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-tight text-gray-400">Hubungi Kami</p>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ storePhone }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
