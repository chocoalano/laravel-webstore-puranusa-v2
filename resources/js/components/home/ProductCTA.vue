<script setup lang="ts">
const features = [
    { icon: 'i-lucide-award', label: 'Eksklusif', description: 'Produk premium terakurasi' },
    { icon: 'i-lucide-percent', label: 'Hemat', description: 'Diskon member hingga 30%' },
    { icon: 'i-lucide-clock', label: 'Terbatas', description: 'Penawaran kilat mingguan' }
]
</script>

<template>
    <section class="py-16 sm:py-24 bg-white dark:bg-slate-950 transition-colors duration-500">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div
                class="relative overflow-hidden rounded-3xl bg-slate-50 border border-slate-200 shadow-xl dark:bg-slate-900 dark:border-white/5 dark:shadow-2xl">

                <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-30 dark:opacity-40">
                    <div
                        class="absolute -left-24 -top-24 size-96 rounded-full bg-primary-500/20 blur-[100px] animate-pulse dark:bg-primary-600/30" />
                    <div class="absolute -right-24 -bottom-24 size-96 rounded-full bg-blue-500/10 blur-[100px] animate-pulse dark:bg-blue-600/20"
                        style="animation-delay: 2s" />
                </div>

                <div class="relative flex flex-col items-center lg:flex-row lg:items-center">
                    <div class="z-10 w-full p-8 sm:p-12 lg:w-3/5 lg:p-20 text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-primary-600 backdrop-blur-md border border-primary-100 mb-6 dark:bg-white/10 dark:text-primary-400 dark:border-white/10">
                            <UIcon name="i-lucide-sparkles" class="size-3.5" />
                            Koleksi Eksklusif
                        </div>

                        <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white sm:text-6xl leading-[1.1]">
                            Kesehatan & Kecantikan <br class="hidden sm:block" />
                            <span
                                class="text-transparent bg-clip-text bg-linear-to-r from-primary-600 to-primary-600 dark:from-primary-400 dark:to-blue-400 italic">Tanpa
                                Batas</span>
                        </h2>

                        <p class="mt-6 max-w-xl text-lg text-slate-600 dark:text-gray-400 leading-relaxed mx-auto lg:mx-0">
                            Masuk ke ekosistem wellness kami. Temukan produk revolusioner yang dirancang khusus untuk
                            meningkatkan kualitas hidup Anda.
                        </p>

                        <div
                            class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 border-t border-slate-200 dark:border-white/10 pt-10">
                            <div v-for="f in features" :key="f.label" class="flex flex-col items-center lg:items-start">
                                <UIcon :name="f.icon" class="size-6 text-primary-600 dark:text-primary-500 mb-3" />
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ f.label }}</h3>
                                <p class="text-xs text-slate-500 dark:text-gray-500 mt-1">{{ f.description }}</p>
                            </div>
                        </div>

                        <div class="mt-12 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                            <UButton to="/shop" size="xl"
                                class="w-full sm:w-auto rounded-2xl px-10 py-4 font-bold shadow-xl shadow-primary-500/20">
                                Jelajahi Produk
                                <template #trailing>
                                    <UIcon name="i-lucide-arrow-right" class="size-5" />
                                </template>
                            </UButton>

                            <UButton variant="ghost" color="neutral" size="xl"
                                class="text-slate-700 hover:bg-slate-200/50 dark:text-white dark:hover:bg-white/5 rounded-2xl px-8">
                                Konsultasi Gratis
                            </UButton>
                        </div>
                    </div>

                    <div class="relative w-full lg:w-2/5 p-8 lg:p-0 flex items-center justify-center">
                        <div class="relative size-72 sm:size-96 lg:size-112.5">
                            <div
                                class="absolute top-[10%] -left-[5%] z-20 w-48 rounded-2xl bg-white/70 p-4 shadow-xl backdrop-blur-xl border border-white dark:bg-white/10 dark:border-white/20 animate-in fade-in slide-in-from-bottom-10 duration-700">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-xl bg-primary-100 dark:bg-primary-500/20 grid place-items-center">
                                        <UIcon name="i-lucide-shield-check" class="size-5 text-primary-600 dark:text-primary-400" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-slate-500 dark:text-gray-400">Terverifikasi</p>
                                        <p class="text-xs font-bold text-slate-900 dark:text-white">BPOM & Halal</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="absolute bottom-[10%] -right-[5%] z-20 w-48 rounded-2xl bg-primary-600 p-4 shadow-2xl border border-white/20 animate-in fade-in slide-in-from-bottom-20 duration-1000">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-xl bg-white/20 grid place-items-center">
                                        <UIcon name="i-lucide-trending-up" class="size-5 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-primary-100">Terlaris</p>
                                        <p class="text-xs font-bold text-white">100K+ Terjual</p>
                                    </div>
                                </div>
                            </div>

                            <div class="absolute inset-0 grid place-items-center">
                                <div
                                    class="size-64 sm:size-80 rounded-full border border-slate-200 dark:border-white/10 animate-[spin_10s_linear_infinite]" />
                                <div
                                    class="absolute size-48 sm:size-64 rounded-full border border-primary-500/30 dark:border-primary-500/20 animate-[spin_15s_linear_infinite_reverse]" />
                                <div class="absolute size-32 rounded-3xl bg-primary-500/10 rotate-45 animate-pulse dark:bg-primary-500/10" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
