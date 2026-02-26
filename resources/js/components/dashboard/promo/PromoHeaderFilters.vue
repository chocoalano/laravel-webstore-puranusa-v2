<script setup lang="ts">
import type { DashboardPromoFilterType } from '@/composables/useDashboardPromo'

const props = defineProps<{
    filteredCount: number
    searchQuery: string
    selectedType: DashboardPromoFilterType
    onlyAvailable: boolean
    selectedTypeIcon: string
    typeItems: Array<{ label: string; value: DashboardPromoFilterType; icon: string }>
}>()

const emit = defineEmits<{
    (e: 'update:searchQuery', value: string): void
    (e: 'update:selectedType', value: DashboardPromoFilterType): void
    (e: 'update:onlyAvailable', value: boolean): void
}>()

function onSearchUpdate(value: string | number): void {
    emit('update:searchQuery', String(value ?? ''))
}

function onTypeUpdate(value: string | number): void {
    emit('update:selectedType', String(value ?? 'all') as DashboardPromoFilterType)
}
</script>

<template>
    <section class="flex flex-col gap-6">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">Daftar Promo</h2>
                <p class="text-neutral-500 text-sm">Kelola dan gunakan promo aktif Anda di sini.</p>
            </div>
            <UBadge variant="subtle" size="lg" class="hidden sm:flex font-mono">
                {{ props.filteredCount }} Aktif
            </UBadge>
        </div>

        <div class="flex flex-col md:flex-row gap-3">
            <UInput
                :model-value="props.searchQuery"
                icon="i-lucide-search"
                placeholder="Cari nama promo atau kode..."
                class="flex-1"
                size="md"
                @update:model-value="onSearchUpdate"
            />
            <div class="flex gap-2">
                <USelectMenu
                    :model-value="props.selectedType"
                    :items="props.typeItems"
                    value-key="value"
                    class="w-48"
                    size="md"
                    @update:model-value="onTypeUpdate"
                >
                    <template #leading>
                        <UIcon :name="props.selectedTypeIcon" />
                    </template>
                </USelectMenu>

                <UButton
                    :variant="props.onlyAvailable ? 'solid' : 'outline'"
                    :color="props.onlyAvailable ? 'primary' : 'neutral'"
                    icon="i-lucide-clock-check"
                    label="Aktif"
                    size="md"
                    @click="emit('update:onlyAvailable', !props.onlyAvailable)"
                />
            </div>
        </div>
    </section>
</template>
