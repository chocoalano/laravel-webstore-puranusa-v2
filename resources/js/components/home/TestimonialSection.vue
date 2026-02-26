<script setup lang="ts">
interface Testimonial {
    id: number
    name: string
    avatar?: string
    role?: string
    rating: number
    text: string
}

defineProps<{
    testimonials?: Testimonial[]
}>()

const placeholders: Testimonial[] = [
    { id: 1, name: 'Sarah Wijaya', role: 'Fashion Enthusiast', rating: 5, text: 'Kualitas produknya luar biasa! Pengiriman cepat dan packaging sangat rapi. Pasti akan belanja lagi di sini.' },
    { id: 2, name: 'Budi Santoso', role: 'Tech Reviewer', rating: 5, text: 'Harga competitive dengan produk original. Customer service sangat responsif dan membantu.' },
    { id: 3, name: 'Anita Putri', role: 'Beauty Blogger', rating: 4, text: 'Koleksi skincare-nya lengkap banget! Semua produk original dan ada garansi. Sangat direkomendasikan.' },
    { id: 4, name: 'Reza Mahendra', role: 'Athlete', rating: 5, text: 'Sepatu olahraga yang saya beli kualitasnya top. Proses belanja mudah dan pengirimannya on time.' },
    { id: 5, name: 'Dewi Lestari', role: 'Interior Designer', rating: 5, text: 'Produk home decor-nya cantik-cantik dan berkualitas premium. Harga juga sangat worth it!' },
    { id: 6, name: 'Fikri Rahman', role: 'Gadget Lover', rating: 4, text: 'Banyak pilihan gadget dengan harga lebih murah dari toko lain. Garansi resmi juga tersedia.' },
]
</script>

<template>
    <section class="py-16 sm:py-20 bg-gray-50/50 dark:bg-gray-900/30">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                    Kata Pelanggan Kami
                </h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Ribuan pelanggan puas berbelanja bersama kami
                </p>
            </div>

            <!-- Testimonials carousel -->
            <UCarousel v-slot="{ item }" :items="testimonials ?? placeholders" loop :autoplay="{ delay: 4000 }" :ui="{
                item: 'basis-[90%] sm:basis-1/2 lg:basis-1/3',
            }">
                <div
                    class="h-full rounded-2xl border border-gray-200/60 bg-white p-6 transition-all duration-300 hover:shadow-lg hover:shadow-gray-200/50 dark:border-white/5 dark:bg-gray-900/80 dark:hover:shadow-gray-950/50">
                    <!-- Stars -->
                    <div class="flex items-center gap-0.5 mb-4">
                        <UIcon v-for="i in 5" :key="i" name="i-lucide-star"
                            :class="i <= item.rating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700'"
                            class="size-4" />
                    </div>

                    <!-- Text -->
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 mb-6 line-clamp-4">
                        "{{ item.text }}"
                    </p>

                    <!-- Author -->
                    <div class="flex items-center gap-3 mt-auto">
                        <div
                            class="grid size-10 place-items-center rounded-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 text-sm font-bold text-gray-600 dark:text-gray-300">
                            {{ item.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.name }}</p>
                            <p v-if="item.role" class="text-xs text-gray-400 dark:text-gray-500">{{ item.role }}</p>
                        </div>
                    </div>
                </div>
            </UCarousel>
        </div>
    </section>
</template>
