<script setup lang="ts">
import { ref } from 'vue'
import { useStoreData } from '@/composables/useStoreData'

const { appName, storeDescription, socialLinks } = useStoreData()
const email = ref('')
</script>

<template>
    <div class="sm:col-span-2 lg:col-span-4">
        <div class="flex items-center gap-2.5">
            <div
                class="grid size-10 place-items-center rounded-xl bg-gray-900 text-white dark:bg-white dark:text-gray-900">
                <UIcon name="i-lucide-shopping-bag" class="size-5" />
            </div>
            <span class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">
                {{ appName }}
            </span>
        </div>

        <p class="mt-4 max-w-xs text-sm leading-relaxed text-gray-600 dark:text-gray-400">
            {{ storeDescription }}
        </p>

        <div class="mt-6 max-w-sm">
            <p class="text-sm font-medium text-gray-900 dark:text-white">Dapatkan promo terbaru</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Diskon eksklusif langsung ke inbox Anda</p>

            <div class="mt-3 flex gap-2">
                <UInput v-model="email" placeholder="Alamat email" icon="i-lucide-mail" class="flex-1" :ui="{
                    base: 'h-10 rounded-xl bg-gray-100/70 border border-gray-200/60 text-gray-900 placeholder:text-gray-500 focus:ring-2 focus:ring-primary/30 dark:bg-white/5 dark:border-white/10 dark:text-white dark:placeholder:text-gray-500'
                }" />
                <UButton class="h-10 shrink-0 rounded-xl" aria-label="Subscribe">
                    Langganan
                </UButton>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-1">
            <UButton v-for="s in socialLinks" :key="s.label" :to="s.to" target="_blank" color="neutral" variant="ghost"
                class="rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                :aria-label="s.label" :icon="s.icon" />
        </div>
    </div>
</template>
