<script setup lang="ts">
import type { DashboardPromo, DashboardPromoType } from '@/types/dashboard'
import type { DashboardPromoTypeMeta } from '@/composables/useDashboardPromo'
import PromoCardItem from '@/components/dashboard/promo/PromoCardItem.vue'

const props = withDefaults(
    defineProps<{
        loading?: boolean
        promos: DashboardPromo[]
        copiedCode: string | null
        typeMeta: Record<DashboardPromoType, DashboardPromoTypeMeta>
        formatExpiry: (expiresAt?: string | null) => string
    }>(),
    {
        loading: false,
    }
)

const emit = defineEmits<{
    (e: 'copyCode', code: string): void
    (e: 'resetFilters'): void
}>()
</script>

<template>
    <div v-if="props.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <USkeleton v-for="i in 6" :key="i" class="h-48 w-full rounded-xl" />
    </div>

    <div
        v-else-if="props.promos.length === 0"
        class="flex flex-col items-center justify-center py-20 border-2 border-dashed border-neutral-200 dark:border-neutral-800 rounded-3xl"
    >
        <div class="p-4 bg-neutral-100 dark:bg-neutral-900 rounded-full mb-4">
            <UIcon name="i-lucide-ticket-dash" class="size-10 text-neutral-400" />
        </div>
        <h3 class="text-lg font-semibold">Tidak Ada Promo</h3>
        <p class="text-neutral-500 max-w-xs text-center text-sm mt-1">Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
        <UButton label="Lihat Semua" variant="link" class="mt-2" @click="emit('resetFilters')" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <PromoCardItem
            v-for="promo in props.promos"
            :key="promo.id"
            :promo="promo"
            :meta="props.typeMeta[promo.type]"
            :copied-code="props.copiedCode"
            :format-expiry="props.formatExpiry"
            @copy-code="(code) => emit('copyCode', code)"
        />
    </div>
</template>
