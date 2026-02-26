<script setup lang="ts">
import type { DashboardAddress } from '@/types/dashboard'

const isOpen = defineModel<boolean>('open', { required: true })

defineProps<{
    selectedAddress: DashboardAddress | null
    otherAddresses: DashboardAddress[]
    settingDefault: Record<string, boolean>
}>()

defineEmits<{
    createAddress: []
    setDefaultContinue: [address: DashboardAddress]
}>()

function fullAddress(address: DashboardAddress): string {
    const secondLine = address.address_line2 ? `, ${address.address_line2}` : ''
    const postalCode = address.postal_code ? `, ${address.postal_code}` : ''

    return `${address.address_line1}${secondLine}, ${address.city_label}, ${address.province_label}${postalCode}`
}
</script>

<template>
    <UModal v-model:open="isOpen" title="Tidak bisa hapus alamat default" description="Ubah default terlebih dulu.">
        <template #body>
            <div class="w-full max-w-2xl space-y-4">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                    <div class="flex items-start gap-2">
                        <UIcon name="i-lucide-info" class="mt-0.5 size-4" />
                        <div class="min-w-0">
                            <p class="font-semibold">Instruksi</p>
                            <ol class="mt-1 list-decimal pl-5 space-y-1">
                                <li>Pilih alamat lain untuk dijadikan <b>Default</b>.</li>
                                <li>Setelah default berubah, lanjutkan proses hapus.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Alamat yang ingin dihapus:</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">
                        {{ selectedAddress?.label || 'Alamat' }} — {{ selectedAddress ? fullAddress(selectedAddress) : '' }}
                    </p>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Pilih default baru:</p>

                    <div
                        v-if="otherAddresses.length === 0"
                        class="rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"
                    >
                        Kamu belum punya alamat lain. Tambahkan alamat baru dulu, lalu coba hapus lagi.
                        <div class="mt-3">
                            <UButton color="primary" variant="solid" class="rounded-xl" icon="i-lucide-plus" @click="$emit('createAddress')">
                                Tambah alamat
                            </UButton>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div
                            v-for="address in otherAddresses"
                            :key="address.id"
                            class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"
                        >
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                {{ address.label || 'Alamat' }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ address.recipient_name }} • {{ address.recipient_phone }}
                            </p>
                            <p class="mt-2 line-clamp-2 text-sm text-gray-700 dark:text-gray-200">
                                {{ fullAddress(address) }}
                            </p>

                            <div class="mt-3">
                                <UButton
                                    color="primary"
                                    variant="soft"
                                    size="sm"
                                    class="rounded-xl"
                                    :loading="!!settingDefault[String(address.id)]"
                                    @click="$emit('setDefaultContinue', address)"
                                >
                                    Jadikan Default & Lanjut Hapus
                                </UButton>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <UButton color="neutral" variant="outline" class="rounded-xl" @click="isOpen = false">
                        Tutup
                    </UButton>
                </div>
            </div>
        </template>
    </UModal>
</template>
