<script setup lang="ts">
/** 
 * Data stats dummy yang mencerminkan sistem afiliasi 
 * (Bisa ditarik dari model CustomerNetwork/Rewards nantinya)
 */
const affiliateBenefits = [
    { label: 'Komisi Langsung', value: '20%', icon: 'i-lucide-badge-percent' },
    { label: 'Bonus Jaringan', value: 'Unlimited', icon: 'i-lucide-users-2' },
    { label: 'Reward Mewah', value: 'Umroh/Mobil', icon: 'i-lucide-trophy' },
]
</script>

<template>
    <section class="py-16 sm:py-24 bg-white dark:bg-gray-950 overflow-hidden">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="lg:flex lg:items-center lg:gap-16">
                <!-- Left: Visual Content -->
                <div class="relative mb-12 lg:mb-0 lg:w-1/2">
                    <!-- Background blobs -->
                    <div
                        class="absolute -left-12 -top-12 size-64 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50" />

                    <div class="relative z-10 grid grid-cols-2 gap-4">
                        <!-- Stat Card 1 -->
                        <div
                            class="col-span-2 rounded-3xl bg-gray-50 dark:bg-white/5 p-8 border border-gray-100 dark:border-white/5 shadow-sm transform transition hover:-translate-y-1 duration-300">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="size-12 rounded-2xl bg-primary-600 text-white grid place-items-center">
                                    <UIcon name="i-lucide-wallet" class="size-6" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Penghasilan Tanpa Batas</h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                                Dapatkan profit retail dan bonus jaringan setiap hari. Sistem bagi hasil yang transparan
                                dan otomatis masuk ke wallet Anda.
                            </p>
                        </div>

                        <!-- Stat Card 2 -->
                        <div
                            class="rounded-3xl bg-primary-600 p-8 text-white shadow-xl transform transition hover:-translate-y-1 duration-300">
                            <UIcon name="i-lucide-trending-up" class="size-8 mb-4 opactiy-80" />
                            <p class="text-3xl font-black">75%</p>
                            <p class="text-xs font-bold uppercase tracking-wider text-primary-100 mt-1">Payout Ratio</p>
                        </div>

                        <!-- Stat Card 3 -->
                        <div
                            class="rounded-3xl bg-indigo-600 p-8 text-white shadow-xl transform transition hover:-translate-y-1 duration-300">
                            <UIcon name="i-lucide-globe" class="size-8 mb-4 opactiy-80" />
                            <p class="text-3xl font-black">100+</p>
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-100 mt-1">Kota Terjangkau
                            </p>
                        </div>
                    </div>

                    <!-- Floating Reward Hint -->
                    <div
                        class="absolute -right-6 bottom-12 z-20 hidden sm:flex items-center gap-3 rounded-2xl bg-white dark:bg-gray-800 p-4 shadow-2xl border border-gray-100 dark:border-white/10 ring-8 ring-white dark:ring-gray-950">
                        <div class="size-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 grid place-items-center">
                            <UIcon name="i-lucide-car" class="size-6 text-orange-600" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase">Target BV
                                Reward</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Raih Expander 2025</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Text Content -->
                <div class="lg:w-1/2">
                    <div class="max-w-xl">
                        <p
                            class="text-sm font-bold uppercase tracking-widest text-primary-600 dark:text-primary-500 mb-4">
                            Entrepreneurship Program
                        </p>
                        <h2
                            class="text-4xl font-black tracking-tight text-gray-900 dark:text-white sm:text-6xl leading-[1.1]">
                            Bangun Kerajaan <br /> Bisnis Anda Sendiri
                        </h2>
                        <p class="mt-6 text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                            Bukan sekadar belanja, ini adalah peluang kemitraan. Manfaatkan sistem pemasaran jaringan
                            kami yang sudah teruji untuk meraih kebebasan finansial dan waktu.
                        </p>

                        <!-- Benefits -->
                        <div class="mt-10 space-y-6">
                            <div v-for="b in affiliateBenefits" :key="b.label" class="flex items-start gap-4">
                                <div
                                    class="mt-1 grid size-8 shrink-0 place-items-center rounded-lg bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-500">
                                    <UIcon :name="b.icon" class="size-4.5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">{{ b.label }} - {{ b.value }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-0.5">Potensi keuntungan
                                        maksimal dengan modal minimal dan sistem yang terukur.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 flex flex-col sm:flex-row items-center gap-4">
                            <UButton to="/register" size="xl" block
                                class="sm:w-auto rounded-2xl px-12 py-4 font-bold shadow-xl shadow-primary-500/10">
                                Gabung Sekarang
                            </UButton>

                            <UButton variant="outline" color="neutral" size="xl" block
                                class="sm:w-auto rounded-2xl px-10 border-gray-200 dark:border-white/10">
                                Pelajari Sistem
                            </UButton>
                        </div>

                        <p class="mt-6 text-center sm:text-left text-xs text-gray-400 dark:text-gray-500">
                            * Syarat dan ketentuan berlaku. BV (Business Volume) dihitung otomatis per transaksi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
