<script setup lang="ts">
import { computed } from 'vue'
import { useStoreData } from '@/composables/useStoreData'

const { appName, paymentMethods } = useStoreData()
const year = computed(() => new Date().getFullYear())
</script>

<template>
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
        <!-- Payment methods -->
        <div v-if="paymentMethods.length" class="border-t border-gray-200/60 pb-8 pt-8 dark:border-white/10">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Metode Pembayaran
            </p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <div v-for="pm in paymentMethods" :key="pm.code"
                    class="flex items-center gap-1.5 rounded-lg border border-gray-200/60 bg-gray-50 px-3 py-1.5 text-xs text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                    <UIcon name="i-lucide-credit-card" class="size-3.5" />
                    <span>{{ pm.name }}</span>
                </div>
            </div>
        </div>

        <!-- Bottom bar -->
        <div
            class="flex flex-col gap-4 border-t border-gray-200/60 py-6 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                &copy; {{ year }} {{ appName }}. All rights reserved.
            </p>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
                <div class="flex items-center gap-1.5">
                    <UIcon name="i-lucide-shield-check" class="size-3.5 text-gray-500 dark:text-gray-400" />
                    <span class="text-xs text-gray-500 dark:text-gray-400">Transaksi aman & terenkripsi</span>
                </div>

                <USeparator orientation="vertical" class="hidden h-3 sm:block"
                    :ui="{ root: 'border-gray-200/60 dark:border-white/10' }" />

                <div class="flex items-center gap-1.5">
                    <UIcon name="i-lucide-lock" class="size-3.5 text-gray-500 dark:text-gray-400" />
                    <span class="text-xs text-gray-500 dark:text-gray-400">SSL Secured</span>
                </div>
            </div>
        </div>
    </div>
</template>
