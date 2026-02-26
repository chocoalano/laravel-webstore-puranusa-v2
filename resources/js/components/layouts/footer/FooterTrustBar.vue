<script setup lang="ts">
const trustFeatures = [
    { icon: 'i-lucide-shield-check', label: '100% Aman', description: 'Transaksi terenkripsi' },
    { icon: 'i-lucide-truck', label: 'Gratis Ongkir', description: 'Min. belanja Rp 499K' },
    { icon: 'i-lucide-refresh-cw', label: 'Easy Return', description: '30 hari pengembalian' },
    { icon: 'i-lucide-headphones', label: 'Support 24/7', description: 'Siap membantu Anda' }
]
</script>

<template>
    <div class="border-t border-b border-gray-200/60 bg-gray-50/50 dark:border-white/5 dark:bg-white/[0.02]">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4 py-6 lg:grid-cols-4 lg:gap-8">
                <div v-for="feat in trustFeatures" :key="feat.label" class="flex items-center gap-3">
                    <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-gray-100 dark:bg-white/5">
                        <UIcon :name="feat.icon" class="size-5 text-gray-600 dark:text-gray-300" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ feat.label }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ feat.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
