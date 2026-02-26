<script setup lang="ts">
import type { DashboardWalletTransactionStatus, DashboardWalletTransactionType } from '@/types/dashboard'
import type { WalletFilterOption } from '@/composables/useDashboardWallet'

const search = defineModel<string>('search', { required: true })
const type = defineModel<DashboardWalletTransactionType | 'all'>('type', { required: true })
const status = defineModel<DashboardWalletTransactionStatus | 'all'>('status', { required: true })

defineProps<{
    typeItems: WalletFilterOption[]
    statusItems: WalletFilterOption[]
    isApplying: boolean
}>()

defineEmits<{
    apply: []
    reset: []
}>()
</script>

<template>
    <UCard class="rounded-2xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <UFormField label="Cari transaksi" class="w-full md:flex-1">
                <UInput
                    v-model="search"
                    placeholder="Ref transaksi, metode bayar, catatan..."
                    icon="i-lucide-search"
                    class="w-full"
                />
            </UFormField>

            <UFormField label="Tipe" class="w-full md:w-56">
                <USelectMenu
                    v-model="type"
                    :items="typeItems"
                    value-key="value"
                    label-key="label"
                    class="w-full"
                />
            </UFormField>

            <UFormField label="Status" class="w-full md:w-56">
                <USelectMenu
                    v-model="status"
                    :items="statusItems"
                    value-key="value"
                    label-key="label"
                    class="w-full"
                />
            </UFormField>

            <div class="flex gap-2">
                <UButton color="primary" icon="i-lucide-filter" :loading="isApplying" @click="$emit('apply')">
                    Terapkan
                </UButton>
                <UButton color="neutral" variant="outline" icon="i-lucide-rotate-ccw" @click="$emit('reset')">
                    Reset
                </UButton>
            </div>
        </div>
    </UCard>
</template>
