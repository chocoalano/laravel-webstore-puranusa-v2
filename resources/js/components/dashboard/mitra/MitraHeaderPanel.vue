<script setup lang="ts">
type TabKey = 'active' | 'passive' | 'prospect'
type TabBadge = {
    color: 'success' | 'neutral' | 'warning'
    icon: string
    text: string
}

defineProps<{
    totalMembers: number
    activeTab: TabKey
    tabBadge: TabBadge
}>()
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex size-10 items-center justify-center rounded-2xl border border-default bg-elevated/60">
                <UIcon name="i-lucide-network" class="size-5 text-primary" />
            </div>

            <div class="min-w-0">
                <p class="text-base font-semibold text-highlighted">Jaringan Member</p>
                <p class="mt-0.5 text-sm text-muted">Kelola dan lihat member dalam jaringan Anda</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <UBadge color="neutral" variant="subtle" class="rounded-2xl">
                <UIcon name="i-lucide-users" class="mr-1 size-3.5" />
                {{ totalMembers }}
            </UBadge>

            <UBadge
                :color="activeTab === 'active' ? 'success' : activeTab === 'passive' ? 'neutral' : 'warning'"
                variant="soft"
                class="rounded-2xl hidden sm:inline-flex"
            >
                <UIcon :name="tabBadge.icon" class="mr-1 size-3.5" />
                {{ tabBadge.text }}
            </UBadge>
        </div>
    </div>
</template>
