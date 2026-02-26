<script setup lang="ts">
type RegisterBenefit = {
    icon: string
    title: string
    description: string
}

const benefits: RegisterBenefit[] = [
    {
        icon: 'i-lucide-badge-check',
        title: 'Akses khusus member',
        description: 'Katalog & fitur yang dirancang untuk pertumbuhan Anda.',
    },
    {
        icon: 'i-lucide-users',
        title: 'Komunitas & dukungan',
        description: 'Lebih mudah mulai karena ada arahan dan support.',
    },
    {
        icon: 'i-lucide-shield-check',
        title: 'Data rapi & terorganisir',
        description: 'Kelola akun Anda dengan lebih nyaman dan aman.',
    },
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
                    Gratis daftar • Mulai lebih cepat
                </div>

                <h1 class="text-4xl font-black leading-tight tracking-tight text-white">
                    Jadi Member <br />
                    Puranusa hari ini.
                </h1>

                <p class="text-lg text-white/75">
                    Dapatkan akses member untuk memulai, lebih mudah, lebih rapi, dan siap dikembangkan.
                </p>
            </div>

            <div class="space-y-3">
                <div
                    v-for="benefit in benefits"
                    :key="benefit.title"
                    class="flex items-start gap-3 rounded-2xl bg-white/10 p-4 backdrop-blur-sm"
                >
                    <UIcon :name="benefit.icon" class="mt-0.5 size-5 shrink-0 text-white" />
                    <div>
                        <div class="font-semibold text-white">{{ benefit.title }}</div>
                        <div class="text-sm text-white/70">{{ benefit.description }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="relative text-sm text-white/50">
            © {{ new Date().getFullYear() }} Puranusa. Semua hak dilindungi.
        </div>
    </div>
</template>
