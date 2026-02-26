<script setup lang="ts">
import { useDashboardAsideLinks } from '@/composables/useDashboardAsideLinks'
import type { DashboardAsideLink, DashboardSectionKey } from '@/types/dashboard'

const props = withDefaults(
    defineProps<{
        active: DashboardSectionKey
        links: DashboardAsideLink[]
        keyPrefix?: string
    }>(),
    {
        keyPrefix: 'aside',
    }
)

const emit = defineEmits<{
    (e: 'select', value: DashboardSectionKey): void
}>()

const { isLabelLink, isActionLink } = useDashboardAsideLinks()

function onSelect(section: DashboardSectionKey): void {
    emit('select', section)
}
</script>

<template>
    <nav class="space-y-1">
        <template v-for="(link, index) in props.links" :key="`${props.keyPrefix}-${index}`">
            <div v-if="isLabelLink(link)" class="pt-3 pb-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ link.label }}
                </p>
            </div>

            <UButton
                v-else-if="isActionLink(link)"
                :color="link.color ?? 'neutral'"
                :variant="props.active === link.value ? 'solid' : 'ghost'"
                class="w-full justify-start rounded-xl"
                :icon="link.icon"
                :ui="{ base: 'w-full justify-start' }"
                @click="onSelect(link.value)"
            >
                <span class="flex-1 text-left">{{ link.label }}</span>
                <UIcon name="i-lucide-chevron-right" class="size-4 opacity-60" />
            </UButton>
        </template>
    </nav>
</template>
