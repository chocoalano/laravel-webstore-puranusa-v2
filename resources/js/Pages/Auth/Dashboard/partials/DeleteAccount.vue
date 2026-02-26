<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const confirmed = ref(false)

function deleteAccount() {
    router.delete('/account')
}
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex items-center gap-3">
                <UIcon name="i-lucide-user-x" class="size-5 text-rose-500" />
                <p class="text-base font-semibold text-gray-900 dark:text-white">Delete Account</p>
            </div>
        </template>

        <div class="space-y-4">
            <UCard
                class="rounded-2xl border border-rose-200 bg-rose-50/60 dark:border-rose-900/50 dark:bg-rose-950/30">
                <div class="flex items-start gap-3">
                    <div class="grid size-10 place-items-center rounded-xl bg-rose-100 dark:bg-rose-950/50">
                        <UIcon name="i-lucide-triangle-alert" class="size-5 text-rose-600 dark:text-rose-300" />
                    </div>
                    <div class="min-w-0 space-y-1">
                        <p class="text-sm font-semibold text-rose-800 dark:text-rose-200">Peringatan</p>
                        <p class="text-xs text-rose-700 dark:text-rose-300">
                            Menghapus akun bersifat <strong>permanen</strong> dan tidak dapat dibatalkan.
                            Semua data, termasuk pesanan, saldo wallet, dan bonus akan dihapus selamanya.
                        </p>
                    </div>
                </div>
            </UCard>

            <UCheckbox v-model="confirmed"
                label="Saya memahami bahwa tindakan ini permanen dan tidak dapat dibatalkan." />

            <div class="flex justify-end">
                <UButton :disabled="!confirmed" color="error" variant="solid" class="rounded-xl"
                    icon="i-lucide-user-x" @click="deleteAccount">
                    Hapus Akun Sekarang
                </UButton>
            </div>
        </div>
    </UCard>
</template>
