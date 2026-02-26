<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useStoreData } from '@/composables/useStoreData'
import CartButtonSlider from './CartButtonSlider.vue'
import WishlistButtonSlider from './WishlistButtonSlider.vue'

const { wishlistCount, authCustomer, isLoggedIn } = useStoreData()

defineEmits(['openMenu'])

/** Ambil dua huruf inisial dari nama customer */
const initials = computed(() => {
    if (!authCustomer.value) return ''
    return authCustomer.value.name
        .split(' ')
        .slice(0, 2)
        .map((w) => w.charAt(0).toUpperCase())
        .join('')
})

const accountItems = computed(() => [
    [
        {
            label: authCustomer.value?.name ?? '',
            slot: 'account-header',
            disabled: true,
        },
    ],
    [
        { label: 'Profil Saya', icon: 'i-lucide-user', to: '/dashboard' },
        { label: 'Pesanan Saya', icon: 'i-lucide-package-search', to: '/dashboard?tab=orders' },
        {
            label: `Keranjang (${wishlistCount.value})`,
            icon: 'i-lucide-shopping-cart',
            to: '/cart',
        },
        {
            label: `Wishlist (${wishlistCount.value})`,
            icon: 'i-lucide-heart',
            to: '/wishlist',
        },
    ],
    [
        {
            label: 'Keluar',
            icon: 'i-lucide-log-out',
            color: 'error' as const,
            onSelect: () => router.post('/logout'),
        },
    ],
])
</script>

<template>
    <div class="flex items-center gap-1.5 sm:gap-2">
        <UColorModeButton size="md" variant="ghost" class="rounded-xl" />

        <!-- Wishlist: hanya tampil saat login -->
        <WishlistButtonSlider v-if="isLoggedIn" />

        <!-- Keranjang: selalu tampil -->
        <CartButtonSlider v-if="isLoggedIn" />

        <USeparator orientation="vertical" class="mx-1 hidden h-5 sm:block dark:border-white/10" />

        <!-- Sudah login: avatar dropdown -->
        <UDropdownMenu v-if="isLoggedIn" :items="accountItems" :ui="{ content: 'w-56 z-50' }">
            <UButton color="neutral" variant="ghost" class="hidden rounded-xl sm:inline-flex" aria-label="Akun saya">
                <div
                    class="flex size-7 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-violet-500 text-[11px] font-bold text-white">
                    {{ initials }}
                </div>
            </UButton>

            <template #account-header>
                <div class="px-1 py-0.5">
                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                        {{ authCustomer?.name }}
                    </p>
                    <p class="truncate text-xs text-gray-400">{{ authCustomer?.email }}</p>
                </div>
            </template>
        </UDropdownMenu>

        <!-- Belum login: tombol Masuk + Daftar -->
        <template v-else>
            <UButton to="/login" color="neutral" variant="ghost"
                class="hidden rounded-xl text-sm font-medium sm:inline-flex">
                Masuk
            </UButton>
            <UButton to="/register" color="primary" variant="solid"
                class="hidden rounded-xl text-sm font-medium sm:inline-flex">
                Daftar
            </UButton>
        </template>


        <!-- Mobile hamburger -->
        <UButton icon="i-lucide-menu" color="neutral" variant="ghost" class="rounded-xl lg:hidden"
            @click="$emit('openMenu')" />
    </div>
</template>
