<script setup lang="ts">
import type { Address } from '@/types/dashboard'

defineProps<{
    defaultAddress?: Address | null
}>()

defineEmits<{
    navigate: [section: string]
}>()
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Kelola Alamat</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Atur alamat default untuk mempercepat checkout.
                    </p>
                </div>
                <div class="flex gap-2">
                    <UButton color="neutral" variant="outline" class="rounded-xl" size="sm"
                        @click="$emit('navigate', 'addresses')">
                        Lihat semua
                    </UButton>
                    <UButton color="primary" variant="solid" class="rounded-xl" size="sm" icon="i-lucide-plus"
                        @click="$emit('navigate', 'addresses')">
                        Tambah
                    </UButton>
                </div>
            </div>
        </template>

        <div v-if="defaultAddress"
            class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ defaultAddress.label }}
                        <UBadge v-if="defaultAddress.is_default" label="Default" color="success" variant="soft"
                            size="xs" class="ml-2 rounded-full" />
                    </p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ defaultAddress.recipient_name }} • {{ defaultAddress.phone }}
                    </p>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                        {{ defaultAddress.address_line }}, {{ defaultAddress.city }},
                        {{ defaultAddress.province }}, {{ defaultAddress.postal_code }}
                    </p>
                </div>
                <UButton color="neutral" variant="ghost" class="rounded-xl" size="sm" icon="i-lucide-pencil"
                    @click="$emit('navigate', 'addresses')">
                    Edit
                </UButton>
            </div>
        </div>

        <div v-else class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 p-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-300">Kamu belum punya alamat. Tambahkan sekarang.</p>
            <div class="mt-4">
                <UButton color="primary" variant="solid" class="rounded-xl" icon="i-lucide-plus"
                    @click="$emit('navigate', 'addresses')">
                    Tambah alamat
                </UButton>
            </div>
        </div>
    </UCard>
</template>
