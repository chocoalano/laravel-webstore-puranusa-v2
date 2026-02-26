<script setup lang="ts">
type TabKey = 'active' | 'passive' | 'prospect'
type TabItem = { value: TabKey; label: string; icon: string; count: number }

const props = defineProps<{
    tabs: TabItem[]
    activeTab: TabKey
    hintText: string
    searchQuery: string
}>()

const emit = defineEmits<{
    (e: 'update:activeTab', value: TabKey): void
    (e: 'update:searchQuery', value: string): void
}>()

function onSearchUpdate(value: string | number): void {
    emit('update:searchQuery', String(value ?? ''))
}
</script>

<template>
    <UCard class="rounded-2xl" :ui="{ body: 'p-2' }">
        <div class="grid grid-cols-3 gap-2">
            <UButton
                v-for="tab in props.tabs"
                :key="tab.value"
                block
                color="neutral"
                variant="ghost"
                class="rounded-xl py-2.5 px-2.5"
                :class="props.activeTab === tab.value
                    ? 'bg-white dark:bg-gray-950 ring-1 ring-black/5 dark:ring-white/5'
                    : 'hover:bg-white/70 dark:hover:bg-white/10'"
                @click="emit('update:activeTab', tab.value)"
            >
                <div class="flex items-center justify-center gap-2">
                    <UIcon :name="tab.icon" class="size-4" :class="props.activeTab === tab.value ? 'text-primary' : 'text-muted'" />
                    <span class="text-xs sm:text-sm font-semibold text-highlighted">{{ tab.label }}</span>
                    <UBadge color="neutral" variant="subtle" size="xs" class="rounded-xl">
                        {{ tab.count }}
                    </UBadge>
                </div>
            </UButton>
        </div>

        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-muted">
                <UIcon name="i-lucide-info" class="mr-1 inline-block size-3.5" />
                {{ props.hintText }}
            </p>

            <UInput
                :model-value="props.searchQuery"
                icon="i-lucide-search"
                placeholder="Cari nama, username, email, telepon..."
                size="sm"
                class="w-full sm:w-80"
                @update:model-value="onSearchUpdate"
            />
        </div>
    </UCard>
</template>
