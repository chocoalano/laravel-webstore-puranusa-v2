<script setup lang="ts">
import type { DashboardAddress } from '@/types/dashboard'

const isOpen = defineModel<boolean>('open', { required: true })

defineProps<{
    address: DashboardAddress | null
    deleting: boolean
}>()

defineEmits<{
    confirm: []
}>()

function fullAddress(address: DashboardAddress): string {
    const secondLine = address.address_line2 ? `, ${address.address_line2}` : ''
    const postalCode = address.postal_code ? `, ${address.postal_code}` : ''

    return `${address.address_line1}${secondLine}, ${address.city_label}, ${address.province_label}${postalCode}`
}
</script>

<template>
    <UModal v-model:open="isOpen" title="Hapus alamat" description="Aksi ini tidak bisa dibatalkan.">
        <template #body>
            <div class="w-full max-w-lg space-y-3">
                <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ address?.label || 'Alamat' }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ address?.recipient_name }} • {{ address?.recipient_phone }}
                    </p>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                        {{ address ? fullAddress(address) : '' }}
                    </p>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                    <div class="flex items-start gap-2">
                        <UIcon name="i-lucide-alert-triangle" class="mt-0.5 size-4" />
                        <p>Pastikan alamat ini tidak diperlukan untuk pengiriman berikutnya.</p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <UButton color="neutral" variant="outline" class="rounded-xl" :disabled="deleting" @click="isOpen = false">
                        Batal
                    </UButton>

                    <UButton color="error" variant="solid" class="rounded-xl" :loading="deleting" @click="$emit('confirm')">
                        Hapus Permanen
                    </UButton>
                </div>
            </div>
        </template>
    </UModal>
</template>
