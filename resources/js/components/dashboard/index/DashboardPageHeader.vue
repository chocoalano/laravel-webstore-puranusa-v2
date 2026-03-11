<script setup lang="ts">
import { computed } from 'vue'
import type { Customer, NetworkProfile } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'

const props = withDefaults(
    defineProps<{
        customer?: Customer | null
        networkProfile?: NetworkProfile | null
        promoActive?: number | null
    }>(),
    {
        customer: null,
        networkProfile: null,
        promoActive: 0,
    }
)

const emit = defineEmits<{
    (e: 'logout'): void
}>()

const { formatDate, copyToClipboard } = useDashboard()

const referralPath = computed<string | null>(() => {
    const username = props.networkProfile?.username?.trim()

    if (!username) {
        return null
    }

    return `/?username=${encodeURIComponent(username)}`
})

const referralUrlLabel = computed<string>(() => {
    if (!referralPath.value) {
        return '—'
    }

    if (typeof window === 'undefined') {
        return referralPath.value
    }

    return `${window.location.origin}${referralPath.value}`
})

function handleLogout(): void {
    emit('logout')
}

function handleCopyReferral(): void {
    const username = props.networkProfile?.username ?? ''
    void copyToClipboard(username)
}
</script>

<template>
    <UPageHeader class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 pt-8">
        <template #title>
            <div class="flex items-center gap-3">
                <div class="size-10 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 grid place-items-center">
                    <img v-if="props.customer?.avatar_url" :src="props.customer.avatar_url" :alt="props.customer?.name || 'User'" class="h-full w-full object-cover" />
                    <UIcon v-else name="i-lucide-user" class="size-5 text-gray-500 dark:text-gray-300" />
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xl font-semibold text-gray-900 dark:text-white">
                        {{ props.customer?.name || 'Dashboard Akun' }}
                    </p>
                    <p class="truncate text-sm text-gray-600 dark:text-gray-300">
                        {{ props.customer?.email || '—' }}
                        <span v-if="props.customer?.phone" class="mx-2 text-gray-400">•</span>
                        <span v-if="props.customer?.phone">{{ props.customer?.phone }}</span>
                    </p>
                </div>
            </div>
        </template>

        <template #description>
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <UBadge :label="props.customer?.tier ? `Member ${props.customer.tier}` : 'Member'" color="neutral" variant="soft" class="rounded-full" />
                    <UBadge :label="`Member sejak ${formatDate(props.customer?.member_since)}`" color="neutral" variant="soft" class="rounded-full" />
                    <UBadge :label="props.promoActive ? `${props.promoActive} promo aktif` : 'Tidak ada promo aktif'" :color="props.promoActive ? 'primary' : 'neutral'" variant="soft" class="rounded-full" />
                </div>

                <div v-if="referralPath" class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Link Referral:</span>
                    <a
                        :href="referralPath"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="max-w-full truncate text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300 dark:hover:text-primary-200"
                    >
                        {{ referralUrlLabel }}
                    </a>
                    <UButton
                        color="neutral"
                        variant="ghost"
                        size="xs"
                        icon="i-lucide-copy"
                        class="rounded-lg"
                        @click="handleCopyReferral"
                    >
                        Copy
                    </UButton>
                </div>
            </div>
        </template>

        <template #right>
            <div class="flex flex-wrap items-center gap-2">
                <UButton to="/products" color="primary" variant="solid" class="rounded-xl" icon="i-lucide-shopping-bag">
                    Belanja
                </UButton>
                <UButton to="/cart" color="neutral" variant="outline" class="rounded-xl" icon="i-lucide-shopping-cart">
                    Keranjang
                </UButton>
                <UButton color="neutral" variant="ghost" class="rounded-xl" icon="i-lucide-log-out" @click="handleLogout">
                    Keluar
                </UButton>
            </div>
        </template>
    </UPageHeader>
</template>
