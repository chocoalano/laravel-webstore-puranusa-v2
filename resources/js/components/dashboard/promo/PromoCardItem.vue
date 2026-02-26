<script setup lang="ts">
import type { DashboardPromo } from '@/types/dashboard'
import type { DashboardPromoTypeMeta } from '@/composables/useDashboardPromo'

const props = defineProps<{
    promo: DashboardPromo
    meta: DashboardPromoTypeMeta
    copiedCode: string | null
    formatExpiry: (expiresAt?: string | null) => string
}>()

const emit = defineEmits<{
    (e: 'copyCode', code: string): void
}>()
</script>

<template>
    <UCard
        class="group relative transition-all duration-300 hover:-translate-y-1 overflow-visible"
        :ui="{
            root: 'ring-1 ring-neutral-200 dark:ring-neutral-800 shadow-none hover:shadow-xl hover:ring-primary-500/50',
            header: 'p-0 overflow-hidden rounded-t-xl',
            body: 'p-5',
        }"
    >
        <div v-if="props.promo.highlight" class="absolute -top-2 -right-2 z-10">
            <UBadge color="primary" variant="solid" size="sm" class="shadow-lg rounded-lg shadow-primary-500/20">
                Populer
            </UBadge>
        </div>

        <template #header>
            <div class="h-2 w-full" :class="props.meta.accentClass" />
        </template>

        <div class="space-y-4">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2 p-2 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                    <UIcon :name="props.meta.icon" class="size-5" :class="props.meta.iconClass" />
                </div>
                <UBadge variant="subtle" :color="props.meta.color" size="xs">
                    {{ props.meta.label }}
                </UBadge>
            </div>

            <div>
                <h4 class="font-bold text-neutral-900 dark:text-white line-clamp-1 uppercase tracking-tight">{{ props.promo.title }}</h4>
                <p class="text-xs text-neutral-500 mt-1 line-clamp-2 leading-relaxed">{{ props.promo.description }}</p>
            </div>

            <div class="flex items-center gap-2 bg-neutral-50 dark:bg-neutral-900 p-2 rounded-xl border border-neutral-100 dark:border-neutral-800">
                <div class="flex-1 px-2">
                    <span class="text-[10px] text-neutral-400 font-bold uppercase block">Kode Promo</span>
                    <span class="font-mono font-bold text-sm tracking-widest">{{ props.promo.code || 'Otomatis' }}</span>
                </div>
                <UButton
                    v-if="props.promo.code"
                    :icon="props.copiedCode === props.promo.code ? 'i-lucide-check' : 'i-lucide-copy'"
                    :color="props.copiedCode === props.promo.code ? 'success' : 'neutral'"
                    variant="ghost"
                    size="sm"
                    @click="emit('copyCode', props.promo.code)"
                />
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800">
                <div class="flex items-center gap-1.5 text-neutral-500">
                    <UIcon name="i-lucide-calendar" class="size-3.5" />
                    <span class="text-[11px] font-medium">{{ props.formatExpiry(props.promo.expires_at) }}</span>
                </div>
                <div v-if="props.promo.quota_left" class="text-[11px] font-bold text-primary-500">
                    Sisa {{ props.promo.quota_left }}
                </div>
            </div>
        </div>

        <template #footer v-if="props.promo.to">
            <UButton :to="props.promo.to" block color="neutral" variant="ghost" trailing-icon="i-lucide-arrow-right" size="sm">
                Gunakan Promo
            </UButton>
        </template>
    </UCard>
</template>
