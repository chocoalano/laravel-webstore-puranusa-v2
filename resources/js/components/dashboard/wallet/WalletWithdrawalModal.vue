<script setup lang="ts">
import { computed, ref, watch } from 'vue'

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
        isWaConfirmed?: boolean
        waConfirmationUrl?: string | null
        hematMode?: boolean
    }>(),
    {
        maxAmountLabel: '',
        adminFee: 6500,
        isWaConfirmed: false,
        waConfirmationUrl: null,
        hematMode: false,
    }
)

const withdrawalStepAmount = 500
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
const hasAmountKeyup = ref(false)
const showPassword = ref(false)

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

const withdrawalRulesHint = computed(
    () =>
        `Masukkan nominal kelipatan Rp 500, minimal ${formattedMinimumRequestAmount.value}, maksimal ${formattedMaxAmount.value}, dan lebih besar dari biaya admin ${formattedAdminFee.value}.`
)

const amountAlert = computed(() => {
    const requestedAmount = Number(amount.value ?? 0)

    if (!hasAmountKeyup.value) {
        return {
            color: 'info',
            icon: 'i-lucide-info',
            title: 'Panduan nominal withdrawal',
            description: withdrawalRulesHint.value,
        }
    }

    if (!Number.isFinite(requestedAmount) || requestedAmount <= 0) {
        return {
            color: 'warning',
            icon: 'i-lucide-alert-triangle',
            title: 'Nominal belum valid',
            description: 'Masukkan nominal angka yang valid untuk lanjut proses withdrawal.',
        }
    }

    if (!Number.isInteger(requestedAmount)) {
        return {
            color: 'warning',
            icon: 'i-lucide-alert-triangle',
            title: 'Nominal harus bilangan bulat',
            description: 'Nominal withdrawal tidak boleh mengandung desimal.',
        }
    }

    if (requestedAmount % withdrawalStepAmount !== 0) {
        const lowerSuggestion = Math.floor(requestedAmount / withdrawalStepAmount) * withdrawalStepAmount
        const upperSuggestion = Math.ceil(requestedAmount / withdrawalStepAmount) * withdrawalStepAmount
        const suggestions = [lowerSuggestion, upperSuggestion]
            .filter((value, index, all) => value > 0 && all.indexOf(value) === index)
            .map((value) => idrFormatter.format(value))
            .join(' atau ')

        return {
            color: 'warning',
            icon: 'i-lucide-alert-triangle',
            title: 'Nominal wajib kelipatan Rp 500',
            description: suggestions !== ''
                ? `Contoh nominal terdekat yang valid: ${suggestions}.`
                : 'Ubah nominal agar sesuai kelipatan Rp 500.',
        }
    }

    if (requestedAmount < minimumRequestAmount.value) {
        return {
            color: 'warning',
            icon: 'i-lucide-alert-triangle',
            title: 'Nominal di bawah minimum',
            description: `Minimal withdrawal adalah ${formattedMinimumRequestAmount.value}.`,
        }
    }

    if (requestedAmount > maxAmountInputValue.value) {
        return {
            color: 'warning',
            icon: 'i-lucide-alert-triangle',
            title: 'Nominal melebihi saldo',
            description: `Maksimal yang bisa diajukan saat ini ${formattedMaxAmount.value}.`,
        }
    }

    const estimatedReceivedAmount = Math.max(0, requestedAmount - adminFeeValue.value)

    if (estimatedReceivedAmount <= 0) {
        return {
            color: 'warning',
            icon: 'i-lucide-alert-triangle',
            title: 'Nominal belum memenuhi biaya admin',
            description: `Nominal harus lebih besar dari biaya admin ${formattedAdminFee.value}.`,
        }
    }

    return {
        color: 'success',
        icon: 'i-lucide-badge-check',
        title: 'Nominal valid',
        description: `Estimasi dana diterima: ${idrFormatter.format(estimatedReceivedAmount)} setelah biaya admin ${formattedAdminFee.value}.`,
    }
})

function onAmountKeyup(): void {
    hasAmountKeyup.value = true
}

watch(isOpen, (open) => {
    if (!open) {
        hasAmountKeyup.value = false
        showPassword.value = false
    }
})

defineEmits<{
    submit: []
}>()
</script>

<template>
    <UModal v-model:open="isOpen" title="Ajukan Withdrawal Wallet"
        description="Konfirmasi password akun untuk keamanan proses withdrawal." scrollable>
        <template #body>
            <div v-if="!props.hematMode && !props.isWaConfirmed" class="space-y-4">
                <UAlert
                    color="warning"
                    variant="soft"
                    icon="i-lucide-message-circle-warning"
                    title="Verifikasi WhatsApp Diperlukan"
                    description="Nomor WhatsApp Anda belum terkonfirmasi. Konfirmasi terlebih dahulu untuk dapat menggunakan fitur Withdrawal Wallet."
                />
                <div class="flex justify-center">
                    <UButton
                        v-if="props.waConfirmationUrl"
                        color="success"
                        icon="i-lucide-message-circle"
                        :to="props.waConfirmationUrl"
                        target="_blank"
                    >
                        Konfirmasi via WhatsApp
                    </UButton>
                </div>
            </div>

            <div v-else class="space-y-4">
                <UFormField label="Nominal withdrawal" required
                    :help="`Minimal ${formattedMinimumRequestAmount} • Maksimal ${formattedMaxAmount} • Kelipatan Rp 500`">
                    <UInput v-model="amount" type="number" :min="minimumRequestAmount" :max="maxAmountInputValue"
                        :step="withdrawalStepAmount" placeholder="Contoh: 50000" class="w-full" icon="i-lucide-wallet"
                        @keyup="onAmountKeyup" />
                </UFormField>

                <UAlert :color="amountAlert.color" variant="soft" :icon="amountAlert.icon" :title="amountAlert.title"
                    :description="amountAlert.description" />

                <div
                    class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-950/30">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-200">
                        Aturan Withdrawal
                    </p>

                    <div class="mt-2 space-y-3 text-xs text-amber-700 dark:text-amber-300">
                        <p flex items-start>
                            <span class="font-semibold mr-1">1.</span>
                            Setiap penarikan dikenakan biaya administrasi <span
                                class="font-bold text-amber-800 dark:text-amber-100">{{ formattedAdminFee }}</span> (Rp
                            6.500).
                        </p>

                        <div>
                            <p class="font-semibold">2. Periode Pentransferan:</p>
                            <ul class="mt-1 ml-4 list-disc space-y-1">
                                <li>Penarikan <span class="font-medium">00.01 - 12.00 WIB</span>: Ditransfer pukul <span
                                        class="font-bold">14.00 WIB</span> (hari yang sama).</li>
                                <li>Penarikan <span class="font-medium">12.01 - 16.00 WIB</span>: Ditransfer pukul <span
                                        class="font-bold">17.00 WIB</span> (hari yang sama).</li>
                                <li>Penarikan <span class="font-medium">16.01 - 00.00 WIB</span>: Ditransfer pukul <span
                                        class="font-bold">14.00 WIB</span> (keesokan harinya).</li>
                            </ul>
                        </div>

                        <p>
                            <span class="font-semibold">3.</span> Pastikan data rekening Anda sudah benar untuk
                            menghindari kegagalan transaksi.
                        </p>
                    </div>
                </div>

                <UFormField label="Password akun" required help="Password dipakai sebagai konfirmasi withdrawal.">
                    <UInput v-model="password" id="withdrawal-password" :type="showPassword ? 'text' : 'password'"
                        class="w-full" placeholder="Masukkan password akun" icon="i-lucide-lock-keyhole"
                        :ui="{ trailing: 'pe-1' }">
                        <template #trailing>
                            <UButton color="neutral" type="button" variant="link" size="sm"
                                :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                :aria-pressed="showPassword" aria-controls="withdrawal-password"
                                @click="showPassword = !showPassword" />
                        </template>
                    </UInput>
                </UFormField>

                <UFormField label="Catatan (opsional)">
                    <UTextarea v-model="notes" :rows="3" class="w-full" placeholder="Catatan untuk tim finance." />
                </UFormField>
            </div>
        </template>
        <template #footer>
            <div class="flex w-full justify-end gap-2">
                <UButton color="neutral" variant="outline" @click="isOpen = false">
                    Batal
                </UButton>
                <UButton v-if="props.hematMode || props.isWaConfirmed" color="primary" icon="i-lucide-arrow-up-right" :loading="loading"
                    :disabled="loading || hasInvalidSubmission" @click="$emit('submit')">
                    Kirim Withdrawal
                </UButton>
            </div>
        </template>
    </UModal>
</template>
