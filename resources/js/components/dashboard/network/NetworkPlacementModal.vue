<script setup lang="ts">
import { computed } from 'vue'
import type { DashboardMitraMember } from '@/types/dashboard'

const props = withDefaults(
    defineProps<{
        open: boolean
        selectedPosition: 'left' | 'right' | null
        selectedUplineId: number | null
        memberSearchQuery: string
        filteredPassiveMembers: DashboardMitraMember[]
        selectedMemberId: number | null
        processing: boolean
    }>(),
    {
        open: false,
        selectedPosition: null,
        selectedUplineId: null,
        memberSearchQuery: '',
        filteredPassiveMembers: () => [],
        selectedMemberId: null,
        processing: false,
    }
)

const emit = defineEmits<{
    'update:open': [value: boolean]
    'update:memberSearchQuery': [value: string]
    selectMember: [member: DashboardMitraMember]
    submit: []
    cancel: []
}>()

const modalOpen = computed({
    get: () => props.open,
    set: (value: boolean) => {
        emit('update:open', value)

        if (!value) {
            emit('cancel')
        }
    },
})

function handleMemberSearchModelUpdate(value: string | number | null | undefined): void {
    emit('update:memberSearchQuery', String(value ?? ''))
}

function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}
</script>

<template>
    <UModal
        v-model:open="modalOpen"
        title="Tempatkan Member ke Binary Tree"
        :description="selectedPosition ? `Posisi terpilih: ${selectedPosition === 'left' ? 'Kiri' : 'Kanan'}` : ''"
    >
        <template #body>
            <UAlert
                color="neutral"
                variant="subtle"
                icon="i-lucide-info"
                class="rounded-2xl"
                :title="`Upline Node ID: ${selectedUplineId ?? '-'}`"
                description="Pilih satu member pasif untuk placement pada node ini."
            />

            <div class="mt-4 space-y-3">
                <UInput
                    :model-value="memberSearchQuery"
                    icon="i-lucide-search"
                    placeholder="Cari nama, email, atau telepon..."
                    size="sm"
                    class="w-full"
                    @update:model-value="handleMemberSearchModelUpdate"
                />

                <div v-if="filteredPassiveMembers.length === 0" class="rounded-2xl border border-dashed border-default p-6 text-center text-sm text-muted">
                    Tidak ada member pasif yang cocok.
                </div>

                <div v-else class="max-h-80 space-y-2 overflow-auto pr-1">
                    <button
                        v-for="member in filteredPassiveMembers"
                        :key="member.id"
                        type="button"
                        class="w-full rounded-2xl border px-3 py-2 text-left transition"
                        :class="selectedMemberId === member.id
                            ? 'border-primary bg-primary/10'
                            : 'border-default hover:bg-elevated/60'"
                        @click="emit('selectMember', member)"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-highlighted">{{ member.name }}</p>
                                <p class="truncate text-xs text-muted">{{ member.email }}</p>
                                <p class="truncate text-xs text-muted">{{ member.phone ?? '-' }}</p>
                                <p class="text-[11px] text-muted">Join: {{ formatDate(member.joined_at) }}</p>
                            </div>
                            <UBadge
                                :color="member.has_purchase ? 'success' : 'neutral'"
                                :variant="member.has_purchase ? 'soft' : 'subtle'"
                                size="xs"
                                class="rounded-full"
                            >
                                {{ member.has_purchase ? 'Purchase' : 'No Purchase' }}
                            </UBadge>
                        </div>
                    </button>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex w-full justify-end gap-2">
                <UButton color="neutral" variant="outline" class="rounded-xl" :disabled="processing" @click="emit('cancel')">
                    Batal
                </UButton>
                <UButton
                    color="primary"
                    class="rounded-xl"
                    :loading="processing"
                    :disabled="!selectedMemberId || !selectedPosition || processing"
                    @click="emit('submit')"
                >
                    Tempatkan
                </UButton>
            </div>
        </template>
    </UModal>
</template>
