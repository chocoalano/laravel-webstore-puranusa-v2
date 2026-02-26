<script setup lang="ts">
const features = [
    { icon: 'i-lucide-truck', title: 'Gratis Ongkir', description: 'Untuk pembelian di atas Rp 150k' },
    { icon: 'i-lucide-shield-check', title: 'Pembayaran Aman', description: 'Transaksi terenkripsi 100%' },
    { icon: 'i-lucide-headset', title: 'Support 24/7', description: 'Tim kami siap membantu Anda' },
    { icon: 'i-lucide-rotate-ccw', title: 'Easy Returns', description: 'Pengembalian gratis 30 hari' },
]
</script>

<template>
    <section class="border-y border-gray-200/60 bg-white py-10 dark:border-white/5 dark:bg-gray-950/50">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-6 sm:gap-8 lg:grid-cols-4">
                <div v-for="feat in features" :key="feat.title"
                    class="flex items-start gap-4 sm:flex-col sm:items-center sm:text-center">
                    <div
                        class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gray-100 text-gray-600 transition-colors dark:bg-white/5 dark:text-gray-400">
                        <UIcon :name="feat.icon" class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ feat.title }}</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ feat.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
