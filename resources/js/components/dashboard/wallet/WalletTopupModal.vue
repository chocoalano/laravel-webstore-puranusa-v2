<script setup lang="ts">
const isOpen = defineModel<boolean>('open', { required: true })
const amount = defineModel<number | null>('amount', { required: true })
const notes = defineModel<string>('notes', { required: true })

defineProps<{
    loading: boolean
    syncing: boolean
}>()

defineEmits<{
    submit: []
}>()
</script>

<template>
    <UModal
        v-model:open="isOpen"
        title="Topup Wallet via Midtrans"
        description="Masukkan nominal topup, lalu lanjutkan pembayaran di popup Midtrans."
    >
        <template #body>
            <div class="space-y-4">
                <UFormField label="Nominal topup" required>
                    <UInput
                        v-model="amount"
                        type="number"
                        min="10000"
                        step="1000"
                        placeholder="Contoh: 100000"
                        icon="i-lucide-banknote"
                        class="w-full"
                    />
                </UFormField>

                <UFormField label="Catatan (opsional)">
                    <UTextarea
                        v-model="notes"
                        :rows="3"
                        placeholder="Catatan tambahan untuk topup."
                        class="w-full"
                    />
                </UFormField>
            </div>
        </template>
        <template #footer>
            <div class="flex w-full justify-end gap-2">
                <UButton color="neutral" variant="outline" @click="isOpen = false">
                    Batal
                </UButton>
                <UButton
                    color="primary"
                    icon="i-lucide-credit-card"
                    :loading="loading || syncing"
                    @click="$emit('submit')"
                >
                    Bayar Topup
                </UButton>
            </div>
        </template>
    </UModal>
</template>
