<script setup lang="ts">
import { ref } from 'vue'

const search = ref('')
</script>

<template>
    <div class="relative w-full max-w-md">
        <UInput v-model="search" placeholder="Cari produk, brand, kategori…" icon="i-lucide-search" size="md"
            class="w-full" :ui="{
                base: 'h-10 rounded-xl bg-gray-100/50 dark:bg-white/5 border-0 focus:ring-2 focus:ring-primary/20 transition-all'
            }" />
        <div class="absolute right-3 top-1/2 -translate-y-1/2 hidden sm:flex items-center gap-1 opacity-40">
            <UKbd>⌘</UKbd>
            <UKbd>K</UKbd>
        </div>
    </div>
</template>
