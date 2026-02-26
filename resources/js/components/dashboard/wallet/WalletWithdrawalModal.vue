<script setup lang="ts">
const isOpen = defineModel<boolean>('open', { required: true })
const amount = defineModel<number | null>('amount', { required: true })
const password = defineModel<string>('password', { required: true })
const notes = defineModel<string>('notes', { required: true })

defineProps<{
    loading: boolean
}>()

defineEmits<{
    submit: []
}>()
</script>

<template>
    <UModal
        v-model:open="isOpen"
        title="Ajukan Withdrawal Wallet"
        description="Konfirmasi password akun untuk keamanan proses withdrawal."
    >
        <template #body>
            <div class="space-y-4">
                <UFormField label="Nominal withdrawal" required>
                    <UInput
                        v-model="amount"
                        type="number"
                        min="10000"
                        step="1000"
                        placeholder="Contoh: 50000"
                        icon="i-lucide-wallet"
                    />
                </UFormField>

                <UFormField label="Password akun" required help="Password dipakai sebagai konfirmasi withdrawal.">
                    <UInput
                        v-model="password"
                        type="password"
                        placeholder="Masukkan password akun"
                        icon="i-lucide-lock-keyhole"
                    />
                </UFormField>

                <UFormField label="Catatan (opsional)">
                    <UTextarea
                        v-model="notes"
                        :rows="3"
                        placeholder="Catatan untuk tim finance."
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
                    icon="i-lucide-arrow-up-right"
                    :loading="loading"
                    @click="$emit('submit')"
                >
                    Kirim Withdrawal
                </UButton>
            </div>
        </template>
    </UModal>
</template>
