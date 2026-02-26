<script setup lang="ts">
import type { Customer } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const { formatDate } = useDashboard()

defineProps<{
    customer?: Customer | null
}>()

defineEmits<{
    navigate: [section: string]
}>()
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex items-center justify-between">
                <p class="text-base font-semibold text-gray-900 dark:text-white">Member Sejak</p>
                <UIcon name="i-lucide-calendar" class="size-5 text-gray-500 dark:text-gray-300" />
            </div>
        </template>

        <div class="space-y-2">
            <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ formatDate(customer?.member_since) }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Terima kasih sudah menjadi bagian dari kami. Kamu bisa cek aktivitas dan benefit member di menu Zenner.
            </p>
            <div class="mt-4 flex gap-2">
                <UButton color="primary" variant="soft" class="rounded-xl" size="sm" icon="i-lucide-sparkles"
                    @click="$emit('navigate', 'zenner')">
                    Buka Zenner
                </UButton>
                <UButton color="neutral" variant="outline" class="rounded-xl" size="sm" icon="i-lucide-trophy"
                    @click="$emit('navigate', 'lifetime')">
                    Lifetime
                </UButton>
            </div>
        </div>
    </UCard>
</template>
