<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import SeoHead from '@/components/SeoHead.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({ layout: AppLayout })

const props = defineProps<{
    resetUrl: string
    seo: { title: string; description: string; canonical: string }
}>()

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const form = useForm({
    password: '',
    password_confirmation: '',
})

function onSubmit(): void {
    form.post(props.resetUrl, {
        preserveScroll: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
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
                    <UIcon name="i-lucide-lock-keyhole" class="size-6 text-primary-500" />
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Buat Kata Sandi Baru</h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Masukkan kata sandi baru untuk akun Anda. Gunakan kombinasi huruf, angka, dan simbol agar lebih aman.
                </p>
            </div>

            <!-- Form -->
            <UForm :state="form" class="space-y-5" @submit.prevent="onSubmit">
                <UFormField label="Kata Sandi Baru" name="password" required :error="form.errors.password">
                    <UInput
                        v-model="form.password"
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Minimal 8 karakter"
                        autocomplete="new-password"
                        class="w-full"
                        :ui="{ trailing: 'pe-1' }"
                    >
                        <template #trailing>
                            <UButton
                                color="neutral"
                                type="button"
                                variant="link"
                                size="sm"
                                :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                :aria-pressed="showPassword"
                                aria-controls="password"
                                @click="showPassword = !showPassword"
                            />
                        </template>
                    </UInput>
                </UFormField>

                <UFormField label="Konfirmasi Kata Sandi" name="password_confirmation" required :error="form.errors.password_confirmation">
                    <UInput
                        v-model="form.password_confirmation"
                        id="password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        placeholder="Ulangi kata sandi baru"
                        autocomplete="new-password"
                        class="w-full"
                        :ui="{ trailing: 'pe-1' }"
                    >
                        <template #trailing>
                            <UButton
                                color="neutral"
                                type="button"
                                variant="link"
                                size="sm"
                                :icon="showPasswordConfirmation ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                :aria-label="showPasswordConfirmation ? 'Sembunyikan konfirmasi' : 'Tampilkan konfirmasi'"
                                :aria-pressed="showPasswordConfirmation"
                                aria-controls="password_confirmation"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                            />
                        </template>
                    </UInput>
                </UFormField>

                <UButton
                    type="submit"
                    block
                    size="lg"
                    color="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    leading-icon="i-lucide-lock-keyhole"
                >
                    {{ form.processing ? 'Menyimpan…' : 'Simpan Kata Sandi Baru' }}
                </UButton>
            </UForm>
        </div>
    </div>
</template>
