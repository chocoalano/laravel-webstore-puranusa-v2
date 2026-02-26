<script setup lang="ts">
type Benefit = {
    icon: string
    title: string
    desc: string
}

type Stat = {
    value: string
    label: string
}

const benefits: Benefit[] = [
    {
        icon: 'i-lucide-wallet',
        title: 'E-Wallet & Bonus Otomatis',
        desc: 'Saldo dan bonus langsung masuk ke e-wallet Anda secara real-time.',
    },
    {
        icon: 'i-lucide-users',
        title: 'Jaringan Affiliasi Terstruktur',
        desc: 'Kembangkan jaringan binary tree dan raih bonus pairing tanpa batas.',
    },
    {
        icon: 'i-lucide-tag',
        title: 'Harga Eksklusif Member',
        desc: 'Dapatkan diskon dan akses ke harga khusus untuk seluruh katalog produk.',
    },
]

const stats: Stat[] = [
    { value: '50K+', label: 'Member Aktif' },
    { value: '200+', label: 'Produk Unggulan' },
    { value: '99%', label: 'Kepuasan Pelanggan' },
]
</script>

<template>
    <div class="relative hidden overflow-hidden bg-linear-to-br from-primary via-primary-800 to-primary-500 lg:flex lg:flex-col lg:justify-between lg:p-12">
        <!-- Background decorations -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/5" />
            <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-white/5" />
            <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/3" />
        </div>

        <!-- Logo -->
        <div class="relative flex items-center gap-2">
            <UIcon name="i-lucide-sparkles" class="size-6 text-white/80" />
            <span class="text-xl font-black tracking-tight text-white">Puranusa</span>
        </div>

        <!-- Main content -->
        <div class="relative space-y-8">
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white/90 backdrop-blur">
                    Platform Jual/Beli & E-Commerce Terpercaya
                </div>

                <h1 class="text-4xl font-black leading-tight tracking-tight text-white">
                    Selamat Datang<br />Kembali!
                </h1>

                <p class="text-lg text-white/75">
                    Masuk dan nikmati semua keuntungan eksklusif sebagai member Puranusa.
                </p>
            </div>

            <!-- Benefits -->
            <div class="space-y-3">
                <div
                    v-for="benefit in benefits"
                    :key="benefit.title"
                    class="flex items-start gap-3 rounded-2xl bg-white/10 p-4 backdrop-blur-sm"
                >
                    <UIcon :name="benefit.icon" class="mt-0.5 size-5 shrink-0 text-white" />
                    <div>
                        <div class="font-semibold text-white">{{ benefit.title }}</div>
                        <div class="text-sm text-white/70">{{ benefit.desc }}</div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 divide-x divide-white/20 rounded-2xl bg-white/10 backdrop-blur-sm">
                <div v-for="stat in stats" :key="stat.label" class="flex flex-col items-center px-3 py-4">
                    <span class="text-2xl font-black text-white">{{ stat.value }}</span>
                    <span class="mt-0.5 text-xs text-white/60">{{ stat.label }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="relative text-sm text-white/50">
            © {{ new Date().getFullYear() }} Puranusa. Semua hak dilindungi.
        </div>
    </div>
</template>
