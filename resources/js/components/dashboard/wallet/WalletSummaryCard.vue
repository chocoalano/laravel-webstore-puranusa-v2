<script setup lang="ts">
defineProps<{
    formattedBalance: string
    hasPendingWithdrawal: boolean
}>()

defineEmits<{
    topup: []
    withdrawal: []
}>()
</script>

<template>
    <UCard class="overflow-hidden rounded-2xl" :ui="{ body: 'p-0' }">
        <div class="flex flex-col justify-between gap-4 p-6 md:flex-row md:items-center">
            <div class="flex items-center gap-4">
                <div class="rounded-xl bg-primary-50 p-3 dark:bg-primary-950">
                    <UIcon name="i-lucide-wallet" class="size-8 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Saldo Wallet</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ formattedBalance }}</h3>
                </div>
            </div>

            <div class="flex flex-col items-start gap-3 md:items-end">
                <div class="flex flex-wrap items-center gap-2">
                    <UButton color="primary" icon="i-lucide-circle-plus" class="rounded-xl" @click="$emit('topup')">
                        Topup Midtrans
                    </UButton>
                    <UButton
                        color="neutral"
                        variant="outline"
                        icon="i-lucide-arrow-up-right"
                        class="rounded-xl"
                        :disabled="hasPendingWithdrawal"
                        @click="$emit('withdrawal')"
                    >
                        Withdrawal
                    </UButton>
                </div>
                <UBadge v-if="hasPendingWithdrawal" color="warning" variant="soft" class="rounded-full">
                    Ada withdrawal yang masih menunggu proses.
                </UBadge>
            </div>
        </div>
    </UCard>
</template>
