<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import SeoHead from '@/components/SeoHead.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineOptions({ layout: AppLayout })

defineProps<{
    seo: { title: string; description: string; canonical: string }
}>()

const page = usePage()
const flashStatus = computed<string | undefined>(() => (page.props as any)?.status as string | undefined)
const firstError = computed<string | undefined>(() => (page.props.errors as any)?.error as string | undefined)

const form = useForm({
    username: '',
    telp: '',
})

function onSubmit(): void {
    form.post('/forgot-password', {
        preserveScroll: true,
    })
}
</script>

<template>
    <SeoHead :title="seo.title" :description="seo.description" :canonical="seo.canonical" />

    <div class="flex min-h-dvh flex-col items-center justify-center bg-white px-6 py-12 dark:bg-slate-950 lg:px-12">
        <!-- Mobile brand -->
        <div class="mb-8 flex items-center gap-2 lg:hidden">
            <UIcon name="i-lucide-sparkles" class="size-5 text-primary-500" />
            <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Puranusa</span>
        </div>

        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="mb-8">
                <div class="mb-2 flex items-center gap-2">
                    <UIcon name="i-lucide-message-circle" class="size-6 text-primary-500" />
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Lupa Kata Sandi</h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Masukkan username dan nomor WhatsApp yang terdaftar untuk mendapatkan link reset kata sandi.
                </p>
            </div>

            <!-- Flash status -->
            <UAlert
                v-if="flashStatus"
                class="mb-6"
                color="info"
                variant="soft"
                icon="i-lucide-info"
                :title="flashStatus"
            />

            <!-- Error alert -->
            <UAlert
                v-if="firstError"
                class="mb-6"
                color="error"
                variant="soft"
                icon="i-lucide-alert-triangle"
                title="Gagal mengirim link"
                :description="String(firstError)"
            />

            <!-- Form -->
            <UForm :state="form" class="space-y-5" @submit.prevent="onSubmit">
                <UFormField label="Username" name="username" required :error="form.errors.username">
                    <UInput
                        v-model="form.username"
                        placeholder="Masukkan username Anda"
                        autocomplete="username"
                        class="w-full"
                    />
                </UFormField>

                <UFormField label="Nomor WhatsApp" name="telp" required :error="form.errors.telp">
                    <UInput
                        v-model="form.telp"
                        type="tel"
                        placeholder="Contoh: 08123456789"
                        autocomplete="tel"
                        class="w-full"
                    />
                </UFormField>

                <UButton
                    type="submit"
                    block
                    size="lg"
                    color="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    leading-icon="i-lucide-message-circle"
                >
                    {{ form.processing ? 'Memproses…' : 'Kirim via WhatsApp' }}
                </UButton>
            </UForm>

            <!-- Footer -->
            <div class="mt-6 text-center">
                <Link
                    href="/login"
                    class="text-sm text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400"
                >
                    Kembali ke halaman masuk
                </Link>
            </div>
        </div>
    </div>
</template>
