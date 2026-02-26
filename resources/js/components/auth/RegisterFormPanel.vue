<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { RegisterData, RegisterForm, RegisterValidationError } from '@/composables/useRegisterForm'

const props = defineProps<{
    form: RegisterForm
    validate: (state: Partial<RegisterData>) => RegisterValidationError[]
    firstError: string | undefined
}>()

const emit = defineEmits<{
    submit: []
}>()

const genderOptions = [
    { label: 'Laki-laki', value: 'L' },
    { label: 'Perempuan', value: 'P' },
]
</script>

<template>
    <div class="flex min-h-dvh flex-col items-center justify-center bg-white px-6 py-12 dark:bg-slate-950 lg:px-12">
        <!-- Mobile brand -->
        <div class="mb-8 flex items-center gap-2 lg:hidden">
            <UIcon name="i-lucide-sparkles" class="size-5 text-primary-500" />
            <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Puranusa</span>
        </div>

        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="mb-8">
                <div class="mb-2 flex items-center gap-2">
                    <UIcon name="i-lucide-user-plus" class="size-6 text-primary-500" />
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pendaftaran Member</h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Isi data berikut untuk membuat akun baru.</p>
            </div>

            <!-- Server error alert -->
            <UAlert
                v-if="firstError"
                class="mb-6"
                color="error"
                variant="soft"
                icon="i-lucide-alert-triangle"
                title="Pendaftaran gagal"
                :description="String(firstError)"
            />

            <!-- Form -->
            <UForm :state="props.form" :validate="props.validate" @submit="emit('submit')" class="space-y-6">
                <!-- Section: Data Diri -->
                <div class="space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Data Diri</p>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <UFormField label="Nama Lengkap" name="name" required :error="props.form.errors.name">
                            <UInput
                                v-model="props.form.name"
                                placeholder="Nama sesuai KTP"
                                autocomplete="name"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Username" name="username" required :error="props.form.errors.username">
                            <UInput
                                v-model="props.form.username"
                                placeholder="Contoh: puranusa_partner"
                                autocomplete="username"
                                class="w-full"
                            />
                        </UFormField>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <UFormField label="Email" name="email" required :error="props.form.errors.email">
                            <UInput
                                v-model="props.form.email"
                                type="email"
                                placeholder="email@contoh.com"
                                autocomplete="email"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Nomor WhatsApp" name="telp" required :error="props.form.errors.telp">
                            <UInput
                                v-model="props.form.telp"
                                type="tel"
                                placeholder="08xxxxxxxxxx"
                                autocomplete="tel"
                                class="w-full"
                            />
                        </UFormField>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <UFormField label="NIK (opsional)" name="nik" :error="props.form.errors.nik">
                            <UInput
                                v-model="props.form.nik"
                                placeholder="16 digit"
                                autocomplete="off"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Jenis Kelamin" name="gender" required :error="props.form.errors.gender">
                            <USelect
                                v-model="props.form.gender"
                                :items="genderOptions"
                                placeholder="Pilih jenis kelamin"
                                class="w-full"
                            />
                        </UFormField>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <UFormField label="Alamat (opsional)" name="alamat" :error="props.form.errors.alamat">
                            <UInput
                                v-model="props.form.alamat"
                                placeholder="Alamat lengkap untuk pengiriman"
                                autocomplete="street-address"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Kode Referral (opsional)" name="referral_code" :error="props.form.errors.referral_code">
                            <UInput
                                v-model="props.form.referral_code"
                                placeholder="Jika ada, masukkan di sini"
                                autocomplete="off"
                                class="w-full"
                            />
                        </UFormField>
                    </div>
                </div>

                <!-- Section: Keamanan -->
                <div class="space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Keamanan Akun</p>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <UFormField label="Kata Sandi" name="password" required :error="props.form.errors.password">
                            <UInput
                                v-model="props.form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Konfirmasi Kata Sandi" name="password_confirmation" required :error="props.form.errors.password_confirmation">
                            <UInput
                                v-model="props.form.password_confirmation"
                                type="password"
                                placeholder="Ulangi kata sandi"
                                autocomplete="new-password"
                                class="w-full"
                            />
                        </UFormField>
                    </div>
                </div>

                <!-- Terms & Submit -->
                <div class="space-y-4">
                    <UFormField name="terms" :error="props.form.errors.terms">
                        <UCheckbox
                            v-model="props.form.terms"
                            label="Saya setuju dengan Syarat & Ketentuan"
                            :description="props.form.errors.terms ? undefined : 'Wajib dicentang untuk melanjutkan.'"
                        />
                    </UFormField>

                    <UButton
                        type="submit"
                        block
                        size="lg"
                        color="primary"
                        :loading="props.form.processing"
                        :disabled="props.form.processing"
                        leading-icon="i-lucide-user-plus"
                    >
                        {{ props.form.processing ? 'Sedang membuat akun…' : 'Daftar Sekarang' }}
                    </UButton>
                </div>

                <!-- Footer links -->
                <div class="space-y-3 text-center">
                    <p class="text-xs text-slate-500">
                        Dengan mendaftar, Anda menyetujui
                        <a href="#" class="font-semibold text-primary-600 hover:text-primary-500">Syarat & Ketentuan</a>.
                    </p>
                    <div class="text-sm text-slate-500">
                        Sudah punya akun?
                        <Link href="/login" class="font-bold text-primary-600 hover:text-primary-500">Masuk</Link>
                    </div>
                </div>
            </UForm>
        </div>
    </div>
</template>
