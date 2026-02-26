<script setup lang="ts">
/**
 * Tombol-tombol aksi produk: Keranjang, Wishlist, dan Share.
 * Pure presentational — semua state dan handler diterima via props/events.
 */
defineProps<{
    disabled: boolean

    isAddingToCart: boolean
    addedToCart: boolean

    isInWishlist: boolean
    isToggling: boolean
    justWishlisted: boolean

    isSharing: boolean
}>()

defineEmits<{
    'add-to-cart': []
    'toggle-wishlist': []
    share: []
}>()
</script>

<template>
    <div class="grid gap-2">
        <!-- Tambah ke Keranjang -->
        <UButton
            color="primary"
            size="lg"
            :icon="addedToCart ? 'i-lucide-check' : 'i-lucide-shopping-cart'"
            :loading="isAddingToCart"
            :disabled="disabled || isAddingToCart"
            block
            class="font-semibold transition-all duration-200"
            :class="addedToCart ? 'bg-green-600! hover:bg-green-700!' : ''"
            @click="$emit('add-to-cart')"
        >
            {{ addedToCart ? 'Ditambahkan!' : (disabled ? 'Stok Habis' : 'Tambah ke Keranjang') }}
        </UButton>

        <!-- Wishlist + Share -->
        <div class="grid grid-cols-2 gap-2">
            <UButton
                :color="isInWishlist ? 'error' : 'neutral'"
                :variant="isInWishlist ? 'soft' : 'outline'"
                :loading="isToggling"
                :disabled="isToggling"
                block
                class="font-medium transition-all duration-200"
                @click="$emit('toggle-wishlist')"
            >
                <UIcon
                    name="i-lucide-heart"
                    class="mr-1.5 size-4 transition-transform duration-200"
                    :class="[
                        isInWishlist ? 'fill-current text-red-500' : 'text-gray-500',
                        justWishlisted ? 'scale-125' : '',
                    ]"
                />
                {{ isInWishlist ? 'Tersimpan' : 'Wishlist' }}
            </UButton>

            <UButton
                color="neutral"
                variant="outline"
                icon="i-lucide-share-2"
                :loading="isSharing"
                block
                class="font-medium"
                @click="$emit('share')"
            >
                Bagikan
            </UButton>
        </div>
    </div>
</template>
