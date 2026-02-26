<script setup lang="ts">
import type { DashboardMitraMember } from '@/types/dashboard'

type TabKey = 'active' | 'passive' | 'prospect'
type PositionBadge = {
    color: 'neutral' | 'primary' | 'info'
    variant: 'subtle' | 'soft'
    text: string
}
type TabBadge = {
    color: 'success' | 'neutral' | 'warning'
    icon: string
    text: string
}

const props = defineProps<{
    member: DashboardMitraMember
    activeTab: TabKey
    hasLeft: boolean
    hasRight: boolean
    formatDate: (value: string | null | undefined) => string
    formatCurrency: (value: number) => string
    getPositionBadge: (position: string | null | undefined) => PositionBadge
    tabStatusBadge: (tab: TabKey) => TabBadge
}>()

const emit = defineEmits<{
    (e: 'openDetail', member: DashboardMitraMember): void
    (e: 'placeMember', member: DashboardMitraMember): void
}>()
</script>

<template>
    <UCard class="rounded-2xl" :ui="{ root: 'hover:bg-elevated/25 transition-colors' }">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="truncate text-sm sm:text-base font-semibold text-highlighted">
                        {{ props.member.name }}
                        <span class="text-sm font-normal text-muted">(@{{ props.member.username }})</span>
                    </p>

                    <UBadge :color="props.getPositionBadge(props.member.position).color" :variant="props.getPositionBadge(props.member.position).variant" class="rounded-2xl">
                        {{ props.getPositionBadge(props.member.position).text }}
                    </UBadge>

                    <UBadge v-if="props.member.level" color="neutral" variant="subtle" class="rounded-2xl">
                        Level {{ props.member.level }}
                    </UBadge>
                </div>

                <div class="mt-2 grid gap-1 text-xs sm:text-sm text-muted">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span class="inline-flex items-center gap-1.5 min-w-0">
                            <UIcon name="i-lucide-mail" class="size-4" />
                            <span class="truncate">{{ props.member.email }}</span>
                        </span>

                        <span v-if="props.member.phone" class="inline-flex items-center gap-1.5">
                            <UIcon name="i-lucide-phone" class="size-4" />
                            <span class="truncate">{{ props.member.phone }}</span>
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span v-if="props.member.package_name" class="inline-flex items-center gap-1.5">
                            <UIcon name="i-lucide-badge" class="size-4" />
                            <span class="text-primary font-semibold">Paket: {{ props.member.package_name }}</span>
                        </span>

                        <span class="inline-flex items-center gap-1.5">
                            <UIcon name="i-lucide-calendar" class="size-4" />
                            Bergabung: {{ props.formatDate(props.member.joined_at) }}
                        </span>
                    </div>

                    <div v-if="(props.member.total_left ?? 0) > 0 || (props.member.total_right ?? 0) > 0" class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                        <UBadge color="info" variant="subtle" class="rounded-xl">Kiri: {{ props.member.total_left ?? 0 }}</UBadge>
                        <UBadge color="success" variant="subtle" class="rounded-xl">Kanan: {{ props.member.total_right ?? 0 }}</UBadge>
                    </div>
                </div>
            </div>

            <div class="shrink-0 flex flex-col items-end gap-2">
                <UBadge :color="props.tabStatusBadge(props.activeTab).color" variant="soft" class="rounded-2xl">
                    <UIcon :name="props.tabStatusBadge(props.activeTab).icon" class="mr-1 size-3.5" />
                    {{ props.tabStatusBadge(props.activeTab).text }}
                </UBadge>

                <div
                    class="text-xs sm:text-sm font-semibold tabular-nums"
                    :class="props.activeTab === 'active'
                        ? 'text-emerald-600 dark:text-emerald-400'
                        : props.activeTab === 'passive'
                            ? 'text-orange-600 dark:text-orange-400'
                            : 'text-muted'"
                >
                    Omzet: {{ props.formatCurrency(props.member.omzet ?? 0) }}
                </div>

                <div class="flex items-center gap-2">
                    <UButton size="xs" color="neutral" variant="outline" class="rounded-xl" icon="i-lucide-eye" @click="emit('openDetail', props.member)">
                        Detail
                    </UButton>

                    <UButton
                        v-if="props.activeTab === 'passive' && (!props.hasLeft || !props.hasRight)"
                        size="xs"
                        color="primary"
                        variant="soft"
                        class="rounded-xl"
                        icon="i-lucide-git-branch"
                        @click="emit('placeMember', props.member)"
                    >
                        Tempatkan
                    </UButton>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-1.5">
                    <UBadge v-if="props.activeTab === 'passive' && props.member.has_purchase" color="primary" variant="subtle" class="rounded-xl">
                        Sudah Belanja
                    </UBadge>
                    <UBadge v-if="props.activeTab === 'prospect' && !props.member.has_purchase" color="neutral" variant="subtle" class="rounded-xl">
                        Belum Belanja
                    </UBadge>
                </div>
            </div>
        </div>
    </UCard>
</template>
