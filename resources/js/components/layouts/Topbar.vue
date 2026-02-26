<script setup lang="ts">
import { computed } from 'vue'
import { useStoreData } from '@/composables/useStoreData'

const { headerTopBarPages } = useStoreData()

const utilityLinks = computed(() => {
    if (headerTopBarPages.value.length > 0) {
        return headerTopBarPages.value
    }

    return [
        { label: 'Lacak Pesanan', to: '/orders' },
        { label: 'Bantuan', to: '/help' },
    ]
})
</script>

<template>
    <div
        class="sticky top-0 z-50 border-b border-gray-200/60 bg-gray-950 text-gray-200 dark:border-white/5 dark:bg-gray-950">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-9 items-center justify-between gap-4 text-xs">
                <!-- Left: promo -->
                <div class="flex min-w-0 items-center gap-2">
                    <UIcon name="i-lucide-truck" class="size-3.5 shrink-0 text-gray-400" />
                    <span class="min-w-0 truncate">
                        Gratis ongkir untuk pesanan di atas <span class="font-medium text-white">Rp 499.000</span>
                    </span>
                </div>

                <!-- Right: utility links -->
                <div class="hidden items-center divide-x divide-gray-700 sm:flex">
                    <UButton
                        v-for="link in utilityLinks"
                        :key="link.to"
                        :to="link.to"
                        color="neutral"
                        variant="link"
                        class="px-3 text-xs text-gray-300 hover:text-white"
                    >
                        {{ link.label }}
                    </UButton>
                </div>
            </div>
        </div>
    </div>
</template>
