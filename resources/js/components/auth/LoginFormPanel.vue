<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { LoginData, LoginForm, LoginValidationError } from '@/composables/useLoginForm'

const props = defineProps<{
    form: LoginForm
    validate: (state: Partial<LoginData>) => LoginValidationError[]
    firstError: string | undefined
    flashStatus: string | undefined
}>()

const emit = defineEmits<{
    submit: []
}>()
</script>

<template>
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
                    <UIcon name="i-lucide-log-in" class="size-6 text-primary-500" />
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Masuk ke Akun</h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Selamat datang kembali, member Puranusa!</p>
            </div>

            <!-- Flash status -->
            <UAlert
                v-if="props.flashStatus"
                class="mb-6"
                color="info"
                variant="soft"
                icon="i-lucide-info"
                :title="props.flashStatus"
            />

            <!-- Server error alert -->
            <UAlert
                v-if="props.firstError"
                class="mb-6"
                color="error"
                variant="soft"
                icon="i-lucide-alert-triangle"
                title="Gagal masuk"
                :description="String(props.firstError)"
            />

            <!-- Form -->
            <UForm :state="props.form" :validate="props.validate" class="space-y-5" @submit="emit('submit')">
                <UFormField label="Username" name="username" required :error="props.form.errors.username">
                    <UInput
                        v-model="props.form.username"
                        placeholder="Masukkan username Anda"
                        autocomplete="username"
                        class="w-full"
                    />
                </UFormField>

                <UFormField label="Kata Sandi" name="password" required :error="props.form.errors.password">
                    <template #hint>
                        <Link
                            href="/forgot-password"
                            class="text-xs text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400"
                            tabindex="-1"
                        >
                            Lupa kata sandi?
                        </Link>
                    </template>
                    <UInput
                        v-model="props.form.password"
                        type="password"
                        placeholder="Masukkan kata sandi"
                        autocomplete="current-password"
                        class="w-full"
                    />
                </UFormField>

                <UFormField name="remember">
                    <UCheckbox
                        v-model="props.form.remember"
                        label="Ingat saya selama 30 hari"
                    />
                </UFormField>

                <UButton
                    type="submit"
                    block
                    size="lg"
                    color="primary"
                    :loading="props.form.processing"
                    :disabled="props.form.processing"
                    leading-icon="i-lucide-log-in"
                >
                    {{ props.form.processing ? 'Memproses…' : 'Masuk ke Akun' }}
                </UButton>
            </UForm>

            <!-- Footer links -->
            <div class="mt-6 space-y-4 text-center">
                <div class="text-sm text-slate-500">
                    Belum punya akun?
                    <Link href="/register" class="font-bold text-primary-600 hover:text-primary-500">
                        Daftar sekarang
                    </Link>
                </div>

                <!-- Trust indicators -->
                <div class="flex flex-wrap items-center justify-center gap-4 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <UIcon name="i-lucide-lock" class="size-3.5" />
                        SSL Terenkripsi
                    </span>
                    <span class="flex items-center gap-1.5">
                        <UIcon name="i-lucide-shield" class="size-3.5" />
                        Data Aman
                    </span>
                    <span class="flex items-center gap-1.5">
                        <UIcon name="i-lucide-clock" class="size-3.5" />
                        Akses 24/7
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
