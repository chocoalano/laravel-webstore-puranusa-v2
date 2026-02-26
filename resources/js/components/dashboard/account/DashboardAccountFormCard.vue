<script setup lang="ts">
import { computed } from 'vue'
import { useDashboardAccountForm } from '@/composables/useDashboardAccountForm'
import type { Address, Customer } from '@/types/dashboard'

const props = withDefaults(
    defineProps<{
        customer?: Customer | null
        defaultAddress?: Address | null
    }>(),
    {
        customer: null,
        defaultAddress: null,
    }
)

const customerName = computed(() => props.customer?.name?.trim() || '—')
const hasCompleteDefaultAddress = computed(() => {
    const address = props.defaultAddress

    if (!address) {
        return false
    }

    const requiredFields = [
        address.recipient_name,
        address.phone,
        address.address_line,
        address.city,
        address.province,
        address.postal_code,
    ]

    return requiredFields.every((value) => {
        const normalized = value.trim()

        return normalized.length > 0 && !['-', '—'].includes(normalized)
    })
})

const {
    form,
    errors,
    submitting,
    genderItems,
    npwpGenderItems,
    yesNoItems,
    submit,
    resetToCurrentCustomerData,
} = useDashboardAccountForm({
    customer: computed(() => props.customer),
})
</script>

<template>
    <UCard class="rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Pengaturan Akun</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Pastikan data profil dan rekening sudah benar untuk kelancaran penarikan dana.
                    </p>
                </div>
                <UBadge color="primary" variant="subtle" class="rounded-full px-3">
                    Profil Publik
                </UBadge>
            </div>
        </template>

        <div class="space-y-6">
            <UAlert
                v-if="!hasCompleteDefaultAddress"
                color="warning"
                variant="subtle"
                icon="i-lucide-map-pin-off"
                title="Default Alamat Lengkap Belum Tersedia"
                description="Silakan atur alamat utama Anda di menu Alamat agar data pengiriman dan dokumen akun bisa tervalidasi."
                :ui="{ title: 'font-bold' }"
            />

            <UAlert
                color="warning"
                variant="subtle"
                icon="i-lucide-shield-check"
                title="Verifikasi Rekening"
                :description="`Nama pemilik rekening harus sesuai dengan nama terdaftar: ${customerName}. Ketidaksesuaian dapat menyebabkan pembatalan otomatis pada proses withdrawal.`"
                :ui="{ title: 'font-bold' }"
            />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <UFormField
                    label="Username"
                    required
                    :error="errors.username"
                    help="Gunakan huruf kecil, angka, atau titik. Minimal 5 karakter."
                >
                    <UInput v-model="form.username" placeholder="contoh: tumbur.siahaan" icon="i-lucide-at-sign" class="w-full"/>
                </UFormField>

                <UFormField
                    label="Nama Lengkap"
                    required
                    :error="errors.name"
                    help="Harus sesuai dengan nama yang tertera di KTP/Buku Tabungan."
                >
                    <UInput v-model="form.name" placeholder="Input nama lengkap Anda" icon="i-lucide-user" class="w-full"/>
                </UFormField>

                <UFormField
                    label="NIK"
                    required
                    :error="errors.nik"
                    help="16 digit Nomor Induk Kependudukan sesuai KTP."
                >
                    <UInput v-model="form.nik" placeholder="32xxxxxxxxxxxxxx" inputmode="numeric" icon="i-lucide-id-card" class="w-full"/>
                </UFormField>

                <UFormField
                    label="Jenis Kelamin"
                    required
                    :error="errors.gender"
                >
                    <USelectMenu
                        v-model="form.gender"
                        :items="genderItems"
                        value-key="value"
                        label-key="label"
                        placeholder="Pilih jenis kelamin"
                        icon="i-lucide-venus-and-mars"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Email"
                    required
                    :error="errors.email"
                    help="Email aktif untuk notifikasi keamanan dan transaksi."
                >
                    <UInput v-model="form.email" type="email" placeholder="nama@email.com" icon="i-lucide-mail" class="w-full"/>
                </UFormField>

                <UFormField
                    label="WhatsApp / No. Telepon"
                    required
                    :error="errors.phone"
                    help="Gunakan format 08 (contoh: 08123456789)."
                >
                    <UInput v-model="form.phone" placeholder="08xxxxxxxxxx" inputmode="tel" icon="i-lucide-phone" class="w-full"/>
                </UFormField>
            </div>

            <USeparator label="Informasi Rekening Bank" :ui="{ label: 'text-xs font-bold uppercase tracking-widest text-gray-400' }" />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <UFormField
                    label="Bank Utama"
                    required
                    :error="errors.bank_name"
                    help="Pilih atau tuliskan nama bank Anda."
                >
                    <UInput v-model="form.bank_name" placeholder="BCA / Mandiri / BRI" icon="i-lucide-landmark" class="w-full"/>
                </UFormField>

                <UFormField
                    label="Nomor Rekening"
                    required
                    :error="errors.bank_account"
                    help="Pastikan digit nomor rekening sudah tepat tanpa tanda baca."
                >
                    <UInput v-model="form.bank_account" inputmode="numeric" placeholder="Contoh: 712345678" icon="i-lucide-credit-card" class="w-full"/>
                </UFormField>
            </div>

            <USeparator label="Data NPWP (Opsional)" :ui="{ label: 'text-xs font-bold uppercase tracking-widest text-gray-400' }" />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <UFormField
                    label="Nama pada NPWP"
                    :error="errors.npwp_nama"
                >
                    <UInput
                        v-model="form.npwp_nama"
                        placeholder="Nama sesuai NPWP"
                        icon="i-lucide-user-round-search"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Nomor NPWP"
                    :error="errors.npwp_number"
                >
                    <UInput
                        v-model="form.npwp_number"
                        placeholder="00.000.000.0-000.000"
                        icon="i-lucide-file-badge"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Jenis Kelamin NPWP"
                    :error="errors.npwp_jk"
                >
                    <USelectMenu
                        v-model="form.npwp_jk"
                        :items="npwpGenderItems"
                        value-key="value"
                        label-key="label"
                        placeholder="Pilih jenis kelamin"
                        icon="i-lucide-venus-and-mars"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Tanggal NPWP"
                    :error="errors.npwp_date"
                >
                    <UInput
                        v-model="form.npwp_date"
                        type="date"
                        icon="i-lucide-calendar-days"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Status Menikah"
                    :error="errors.npwp_menikah"
                >
                    <USelectMenu
                        v-model="form.npwp_menikah"
                        :items="yesNoItems"
                        value-key="value"
                        label-key="label"
                        placeholder="Pilih status"
                        icon="i-lucide-heart"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Status Bekerja"
                    :error="errors.npwp_kerja"
                >
                    <USelectMenu
                        v-model="form.npwp_kerja"
                        :items="yesNoItems"
                        value-key="value"
                        label-key="label"
                        placeholder="Pilih status"
                        icon="i-lucide-briefcase-business"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Jumlah Anak"
                    :error="errors.npwp_anak"
                >
                    <UInput
                        v-model="form.npwp_anak"
                        inputmode="numeric"
                        placeholder="Contoh: 0"
                        icon="i-lucide-baby"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Nama Kantor"
                    :error="errors.npwp_office"
                >
                    <UInput
                        v-model="form.npwp_office"
                        placeholder="Nama tempat kerja"
                        icon="i-lucide-building-2"
                        class="w-full"
                    />
                </UFormField>
            </div>

            <UFormField
                label="Alamat NPWP"
                :error="errors.npwp_alamat"
            >
                <UTextarea
                    v-model="form.npwp_alamat"
                    :rows="3"
                    placeholder="Alamat sesuai dokumen NPWP"
                    class="w-full"
                />
            </UFormField>

            <UAlert
                v-if="Object.keys(errors).length"
                color="error"
                variant="subtle"
                icon="i-lucide-alert-circle"
                title="Terdapat Kesalahan Input"
            >
                <template #description>
                    <ul class="list-disc pl-4 mt-2 space-y-1">
                        <li v-for="(message, key) in errors" :key="key">{{ message }}</li>
                    </ul>
                </template>
            </UAlert>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                <UButton
                    color="neutral"
                    variant="ghost"
                    class="rounded-xl px-6"
                    :disabled="submitting"
                    @click="resetToCurrentCustomerData"
                    label="Batalkan"
                />
                <UButton
                    color="primary"
                    variant="solid"
                    class="rounded-xl px-8 shadow-lg shadow-primary-500/20"
                    :loading="submitting"
                    @click="submit"
                    label="Simpan Data Akun"
                />
            </div>
        </div>
    </UCard>
</template>
