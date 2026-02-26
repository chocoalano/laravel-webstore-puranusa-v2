<script setup lang="ts">
import { computed } from 'vue'
import type { DashboardAddress } from '@/types/dashboard'

const props = defineProps<{
    addresses: DashboardAddress[]
    settingDefault: Record<string, boolean>
}>()

defineEmits<{
    create: []
    edit: [address: DashboardAddress]
    delete: [address: DashboardAddress]
    setDefault: [address: DashboardAddress]
}>()

const hasAddresses = computed(() => props.addresses.length > 0)

function formatPhoneDisplay(phone: string): string {
    return phone || '—'
}

function fullAddress(address: DashboardAddress): string {
    const secondLine = address.address_line2 ? `, ${address.address_line2}` : ''
    const postalCode = address.postal_code ? `, ${address.postal_code}` : ''

    return `${address.address_line1}${secondLine}, ${address.city_label}, ${address.province_label}${postalCode}`
}
</script>

<template>
    <div v-if="!hasAddresses" class="py-12 text-center text-gray-500 dark:text-gray-400">
        <UIcon name="i-lucide-map-pin" class="mx-auto size-10 opacity-40" />
        <p class="mt-3 text-sm">Belum ada alamat tersimpan.</p>
        <p class="mt-1 text-xs">Tambahkan alamat untuk mempercepat proses checkout.</p>

        <div class="mt-4">
            <UButton color="primary" variant="solid" class="rounded-xl" icon="i-lucide-plus" @click="$emit('create')">
                Tambah Alamat Pertama
            </UButton>
        </div>
    </div>

    <div v-else class="space-y-3">
        <div
            v-for="address in addresses"
            :key="address.id"
            class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ address.label || 'Alamat' }}
                        </p>
                        <UBadge
                            v-if="address.is_default"
                            label="Default"
                            color="success"
                            variant="soft"
                            size="xs"
                            class="rounded-full"
                        />
                    </div>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ address.recipient_name }} • {{ formatPhoneDisplay(address.recipient_phone) }}
                    </p>

                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                        {{ fullAddress(address) }}
                    </p>

                    <p v-if="address.description" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Catatan: {{ address.description }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    <UButton
                        v-if="!address.is_default"
                        color="neutral"
                        variant="outline"
                        size="sm"
                        class="rounded-xl"
                        icon="i-lucide-check"
                        :loading="!!settingDefault[String(address.id)]"
                        @click="$emit('setDefault', address)"
                    >
                        Jadikan Default
                    </UButton>

                    <UButton
                        color="neutral"
                        variant="outline"
                        size="sm"
                        class="rounded-xl"
                        icon="i-lucide-pencil"
                        @click="$emit('edit', address)"
                    >
                        Edit
                    </UButton>

                    <UButton
                        color="error"
                        variant="soft"
                        size="sm"
                        class="rounded-xl"
                        icon="i-lucide-trash-2"
                        @click="$emit('delete', address)"
                    >
                        Hapus
                    </UButton>
                </div>
            </div>
        </div>
    </div>
</template>
