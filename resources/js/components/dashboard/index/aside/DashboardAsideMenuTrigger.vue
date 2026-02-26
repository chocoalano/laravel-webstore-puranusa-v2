<script setup lang="ts">
import { useDashboard } from '@/composables/useDashboard'

const props = withDefaults(
    defineProps<{
        walletBalance?: number | null
    }>(),
    {
        walletBalance: 0,
    }
)

const emit = defineEmits<{
    (e: 'open'): void
}>()

const { formatIDR } = useDashboard()

function openMenu(): void {
    emit('open')
}
</script>

<template>
    <UButton
        color="neutral"
        variant="outline"
        icon="i-lucide-menu"
        class="w-full justify-start rounded-xl"
        @click="openMenu"
    >
        <span class="flex-1 text-left">Menu Akun</span>
        <UBadge :label="formatIDR(props.walletBalance ?? 0)" color="primary" variant="soft" class="rounded-full" />
    </UButton>
</template>
