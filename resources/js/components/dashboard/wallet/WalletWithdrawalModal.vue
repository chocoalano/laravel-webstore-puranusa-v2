<script setup lang="ts">
import { computed } from 'vue'

const isOpen = defineModel<boolean>('open', { required: true })
const amount = defineModel<number | null>('amount', { required: true })
const password = defineModel<string>('password', { required: true })
const notes = defineModel<string>('notes', { required: true })

const props = withDefaults(
    defineProps<{
        loading: boolean
        maxAmount: number
        maxAmountLabel?: string
        adminFee?: number
    }>(),
    {
        maxAmountLabel: '',
        adminFee: 6500,
    }
)

const withdrawalStepAmount = 1000
const minimumWithdrawalAmount = 10000

const idrFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
})

const maxAmountInputValue = computed(() => Math.max(0, Number(props.maxAmount ?? 0)))

const formattedMaxAmount = computed(() => {
    if (props.maxAmountLabel.trim() !== '') {
        return props.maxAmountLabel
    }

    return idrFormatter.format(maxAmountInputValue.value)
})

const adminFeeValue = computed(() => Math.max(0, Number(props.adminFee ?? 0)))
const formattedAdminFee = computed(() => idrFormatter.format(adminFeeValue.value))

const minimumRequestAmount = computed(() => {
    const rounded = Math.ceil(minimumWithdrawalAmount / withdrawalStepAmount) * withdrawalStepAmount

    return Math.max(withdrawalStepAmount, rounded)
})

const formattedMinimumRequestAmount = computed(() => idrFormatter.format(minimumRequestAmount.value))

const hasInvalidAmount = computed(() => {
    const requestedAmount = Number(amount.value ?? 0)

    if (!Number.isFinite(requestedAmount)) {
        return true
    }

    if (!Number.isInteger(requestedAmount) || requestedAmount % withdrawalStepAmount !== 0) {
        return true
    }

    if (requestedAmount < minimumRequestAmount.value) {
        return true
    }

    if (requestedAmount > maxAmountInputValue.value) {
        return true
    }

    return false
})

const hasInvalidSubmission = computed(() => hasInvalidAmount.value || password.value.trim() === '')

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
                <UFormField
                    label="Nominal withdrawal"
                    required
                    :help="`Minimal ${formattedMinimumRequestAmount} • Maksimal ${formattedMaxAmount} • Kelipatan Rp 1.000`"
                >
                    <UInput
                        v-model="amount"
                        type="number"
                        :min="minimumRequestAmount"
                        :max="maxAmountInputValue"
                        :step="withdrawalStepAmount"
                        placeholder="Contoh: 50000"
                        class="w-full"
                        icon="i-lucide-wallet"
                    />
                </UFormField>

                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 dark:border-amber-900/40 dark:bg-amber-950/30">
                    <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">
                        Aturan withdrawal
                    </p>
                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                        Setiap transaksi withdrawal dikenakan biaya admin {{ formattedAdminFee }}.
                    </p>
                </div>

                <UFormField label="Password akun" required help="Password dipakai sebagai konfirmasi withdrawal.">
                    <UInput
                        v-model="password"
                        type="password"
                        class="w-full"
                        placeholder="Masukkan password akun"
                        icon="i-lucide-lock-keyhole"
                    />
                </UFormField>

                <UFormField label="Catatan (opsional)">
                    <UTextarea
                        v-model="notes"
                        :rows="3"
                        class="w-full"
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
                    :disabled="loading || hasInvalidSubmission"
                    @click="$emit('submit')"
                >
                    Kirim Withdrawal
                </UButton>
            </div>
        </template>
    </UModal>
</template>
