<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })

const props = defineProps<{
    username: string
    maskedPhone: string
    waUrl: string | null
    confirmUrl: string
}>()
</script>

<template>
    <div class="flex min-h-dvh items-center justify-center bg-gray-50 px-4 py-16 dark:bg-gray-950">
        <div class="w-full max-w-md space-y-6">

            <!-- Icon header -->
            <div class="flex flex-col items-center gap-3 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                    <UIcon name="i-lucide-message-circle-warning" class="h-8 w-8 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        Konfirmasi WhatsApp Diperlukan
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Halo <span class="font-semibold text-gray-700 dark:text-gray-300">{{ username }}</span>
                    </p>
                </div>
            </div>

            <!-- Alert -->
            <UAlert
                color="warning"
                variant="soft"
                icon="i-lucide-triangle-alert"
                title="Nomor WhatsApp belum terdeteksi"
                :description="`Nomor ${maskedPhone} belum terdeteksi mengirim pesan ke gateway kami. Ikuti langkah di bawah untuk menyelesaikan konfirmasi.`"
            />

            <!-- Steps -->
            <UCard class="rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800">
                <div class="space-y-4">
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Cara konfirmasi nomor WhatsApp:
                    </p>

                    <ol class="space-y-3">
                        <li class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                                1
                            </span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Klik tombol <span class="font-semibold text-gray-800 dark:text-gray-200">"Buka WhatsApp"</span> di bawah. Pesan sudah terisi otomatis — jangan ubah isinya.
                            </span>
                        </li>

                        <li class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                                2
                            </span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-semibold text-gray-800 dark:text-gray-200">Kirim pesan</span> tersebut ke nomor gateway kami. Langkah ini wajib agar sistem mengenali nomor Anda.
                            </span>
                        </li>

                        <li class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                                3
                            </span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Setelah pesan terkirim, <span class="font-semibold text-gray-800 dark:text-gray-200">klik link konfirmasi</span> yang ada di dalam pesan tersebut untuk menyelesaikan proses.
                            </span>
                        </li>
                    </ol>
                </div>
            </UCard>

            <!-- Actions -->
            <div class="space-y-3">
                <UButton
                    v-if="waUrl"
                    :to="waUrl"
                    target="_blank"
                    color="success"
                    size="lg"
                    icon="i-lucide-message-circle"
                    class="w-full justify-center"
                >
                    Buka WhatsApp & Kirim Pesan
                </UButton>

                <UAlert
                    v-else
                    color="error"
                    variant="soft"
                    icon="i-lucide-wifi-off"
                    title="Gateway WhatsApp belum dikonfigurasi"
                    description="Hubungi admin untuk bantuan konfirmasi manual."
                />

                <div class="flex items-center justify-center gap-1 pt-1 text-sm text-gray-500 dark:text-gray-400">
                    <UIcon name="i-lucide-info" class="h-4 w-4 shrink-0" />
                    <span>Setelah kirim pesan, klik link di dalam pesan WA Anda.</span>
                </div>
            </div>

            <!-- Already sent but still not detected -->
            <UCard class="rounded-2xl ring-1 ring-amber-200 bg-amber-50/50 dark:ring-amber-800/40 dark:bg-amber-950/20">
                <div class="flex gap-3">
                    <UIcon name="i-lucide-headset" class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" />
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">
                            Sudah kirim pesan tapi masih tidak terdeteksi?
                        </p>
                        <p class="text-xs text-amber-700 dark:text-amber-300">
                            Hubungi admin untuk konfirmasi manual. Admin dapat mengaktifkan akun Anda langsung melalui panel kontrol.
                        </p>
                    </div>
                </div>
            </UCard>

            <!-- Back to login -->
            <div class="text-center">
                <Link
                    href="/login"
                    class="text-sm text-primary-600 hover:text-primary-700 hover:underline dark:text-primary-400"
                >
                    Kembali ke halaman masuk
                </Link>
            </div>

        </div>
    </div>
</template>
