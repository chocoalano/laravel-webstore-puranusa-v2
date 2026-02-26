<script setup lang="ts">
import type { DashboardMitraMember } from '@/types/dashboard'

const props = withDefaults(
    defineProps<{
        open: boolean
        selectedMember: DashboardMitraMember | null
        selectedPosition: 'left' | 'right' | null
        hasLeft: boolean
        hasRight: boolean
        processing: boolean
        uplineId: number
    }>(),
    {
        selectedMember: null,
        selectedPosition: null,
    }
)

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'update:selectedPosition', value: 'left' | 'right' | null): void
    (e: 'close'): void
    (e: 'submit'): void
}>()

function closeModal(): void {
    emit('close')
    emit('update:open', false)
}

function submitPlacement(): void {
    emit('submit')
}
</script>

<template>
    <UModal
        :open="open"
        title="Tempatkan Member ke Binary Tree"
        :description="selectedMember ? `Pilih posisi untuk menempatkan ${selectedMember.name} di jaringan binary tree Anda.` : ''"
        :ui="{ overlay: 'fixed inset-0 z-[9998] bg-black/50 backdrop-blur-sm', content: 'fixed z-[9999] w-full max-w-lg' }"
        @update:open="(value) => emit('update:open', value)"
    >
        <template #body>
            <UAlert
                v-if="selectedMember"
                icon="i-lucide-info"
                color="neutral"
                variant="subtle"
                class="rounded-2xl"
                :title="`Menempatkan: ${selectedMember.name}`"
                :description="`Upline ID: ${uplineId}`"
            />

            <div class="mt-4 grid grid-cols-2 gap-3">
                <UButton
                    block
                    size="lg"
                    color="neutral"
                    variant="outline"
                    class="rounded-2xl py-6"
                    :disabled="hasLeft"
                    :ui="{ base: selectedPosition === 'left' ? 'ring-2 ring-primary' : '' }"
                    @click="!hasLeft && emit('update:selectedPosition', 'left')"
                >
                    <div class="flex flex-col items-center gap-2">
                        <UIcon name="i-lucide-git-branch" class="size-7 rotate-90" />
                        <div class="text-sm font-semibold">Posisi Kiri</div>
                        <div class="text-xs text-muted">{{ hasLeft ? 'Sudah Terisi' : 'Left Position' }}</div>
                    </div>
                </UButton>

                <UButton
                    block
                    size="lg"
                    color="neutral"
                    variant="outline"
                    class="rounded-2xl py-6"
                    :disabled="hasRight"
                    :ui="{ base: selectedPosition === 'right' ? 'ring-2 ring-primary' : '' }"
                    @click="!hasRight && emit('update:selectedPosition', 'right')"
                >
                    <div class="flex flex-col items-center gap-2">
                        <UIcon name="i-lucide-git-branch" class="size-7 -rotate-90" />
                        <div class="text-sm font-semibold">Posisi Kanan</div>
                        <div class="text-xs text-muted">{{ hasRight ? 'Sudah Terisi' : 'Right Position' }}</div>
                    </div>
                </UButton>
            </div>

            <UAlert
                v-if="selectedPosition"
                class="mt-4 rounded-2xl"
                color="primary"
                variant="soft"
                icon="i-lucide-check"
                :title="`Posisi terpilih: ${selectedPosition === 'left' ? 'Kiri' : 'Kanan'}`"
                description="Klik tombol Tempatkan untuk menyimpan perubahan."
            />
        </template>

        <template #footer>
            <div class="flex justify-end gap-2">
                <UButton color="neutral" variant="outline" class="rounded-xl" :disabled="processing" @click="closeModal">
                    Batal
                </UButton>
                <UButton
                    color="primary"
                    class="rounded-xl"
                    :disabled="!selectedPosition || processing"
                    :loading="processing"
                    @click="submitPlacement"
                >
                    Tempatkan
                </UButton>
            </div>
        </template>
    </UModal>
</template>
