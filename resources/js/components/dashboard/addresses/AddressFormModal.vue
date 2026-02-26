<script setup lang="ts">
import { computed } from 'vue'
import type {
    DashboardAddressFormMode,
    DashboardAddressFormState,
    DashboardAddressSelectItem,
} from '@/composables/useDashboardAddresses'

const isOpen = defineModel<boolean>('open', { required: true })

const props = defineProps<{
    mode: DashboardAddressFormMode
    form: DashboardAddressFormState
    errors: Record<string, string>
    submitting: boolean
    provinceItems: DashboardAddressSelectItem[]
    cityItems: DashboardAddressSelectItem[]
    districtItems: DashboardAddressSelectItem[]
}>()

defineEmits<{
    submit: []
    reset: []
}>()

const title = computed(() => (props.mode === 'create' ? 'Tambah Alamat' : 'Edit Alamat'))
const description = computed(() =>
    props.mode === 'create'
        ? 'Isi detail alamat untuk pengiriman & checkout.'
        : 'Perbarui detail alamat yang sudah tersimpan.'
)
</script>

<template>
    <UModal v-model:open="isOpen" :title="title" :description="description" scrollable>
        <template #body>
            <div class="w-full max-w-2xl space-y-4">
                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Label (opsional)" class="w-full">
                        <UInput v-model="form.label" placeholder="Contoh: Rumah, Kantor" class="w-full" />
                    </UFormField>

                    <div class="w-full">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Jadikan default</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default dipakai otomatis saat checkout.</p>
                        <div class="mt-2 flex items-center gap-2">
                            <UCheckbox v-model="form.is_default" />
                            <span class="text-sm text-gray-700 dark:text-gray-200">Set sebagai alamat utama</span>
                        </div>
                    </div>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Nama penerima" required :error="errors.recipient_name" class="w-full">
                        <UInput v-model="form.recipient_name" placeholder="Nama lengkap" class="w-full" />
                    </UFormField>

                    <UFormField label="No. HP penerima" required :error="errors.recipient_phone" class="w-full">
                        <UInput v-model="form.recipient_phone" placeholder="08xxxxxxxxxx" class="w-full" />
                    </UFormField>
                </div>

                <UFormField label="Alamat utama" required :error="errors.address_line1" class="w-full">
                    <UTextarea v-model="form.address_line1" placeholder="Jalan, RT/RW, nomor, patokan..." :rows="3" class="w-full" />
                </UFormField>

                <UFormField label="Alamat tambahan (opsional)" class="w-full">
                    <UInput v-model="form.address_line2" placeholder="Contoh: Blok A No. 12" class="w-full" />
                </UFormField>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Provinsi" required :error="errors.province_id || errors.province_label" class="w-full">
                        <div class="space-y-2">
                            <USelectMenu
                                v-model="form.province_id"
                                :items="provinceItems"
                                value-key="value"
                                label-key="label"
                                placeholder="Pilih provinsi"
                                :disabled="provinceItems.length === 0"
                                class="w-full"
                            />
                            <p v-if="provinceItems.length === 0" class="text-xs text-amber-600 dark:text-amber-300">
                                Data target pengiriman untuk provinsi belum tersedia.
                            </p>
                        </div>
                    </UFormField>

                    <UFormField label="Kota/Kab" required :error="errors.city_id || errors.city_label" class="w-full">
                        <div class="space-y-2">
                            <USelectMenu
                                v-model="form.city_id"
                                :items="cityItems"
                                value-key="value"
                                label-key="label"
                                placeholder="Pilih kota/kab"
                                :disabled="!form.province_id || cityItems.length === 0"
                                class="w-full"
                            />
                            <p
                                v-if="form.province_id && cityItems.length === 0"
                                class="text-xs text-amber-600 dark:text-amber-300"
                            >
                                Kota/Kab untuk provinsi ini belum tersedia di target pengiriman.
                            </p>
                        </div>
                    </UFormField>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Kecamatan" required :error="errors.district" class="w-full">
                        <div class="space-y-2">
                            <USelectMenu
                                v-model="form.district"
                                :items="districtItems"
                                value-key="value"
                                label-key="label"
                                placeholder="Pilih kecamatan"
                                :disabled="!form.city_id || districtItems.length === 0"
                                class="w-full"
                            />
                            <p
                                v-if="form.city_id && districtItems.length === 0"
                                class="text-xs text-amber-600 dark:text-amber-300"
                            >
                                Kecamatan untuk kota ini belum tersedia di target pengiriman.
                            </p>
                        </div>
                    </UFormField>

                    <UFormField label="District Lion (auto)" :error="errors.district_lion" class="w-full">
                        <UInput
                            v-model="form.district_lion"
                            placeholder="Terisi otomatis dari target pengiriman"
                            readonly
                            class="w-full"
                        />
                    </UFormField>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Kode pos (opsional)" :error="errors.postal_code" class="w-full">
                        <UInput v-model="form.postal_code" placeholder="12345" class="w-full" />
                    </UFormField>

                    <UFormField label="Negara" class="w-full">
                        <UInput v-model="form.country" placeholder="Indonesia" class="w-full" />
                    </UFormField>
                </div>

                <UFormField label="Catatan (opsional)" class="w-full">
                    <UInput v-model="form.description" placeholder="Contoh: Titip satpam / rumah pagar hitam" class="w-full" />
                </UFormField>

                <div
                    v-if="Object.keys(errors).length"
                    class="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200"
                >
                    <p class="font-semibold">Periksa kembali:</p>
                    <ul class="mt-1 list-disc pl-5 space-y-1">
                        <li v-for="(message, key) in errors" :key="key">{{ message }}</li>
                    </ul>
                </div>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <UButton color="neutral" variant="outline" class="rounded-xl" :disabled="submitting" @click="isOpen = false">
                        Batal
                    </UButton>

                    <div class="flex gap-2">
                        <UButton color="neutral" variant="ghost" class="rounded-xl" :disabled="submitting" @click="$emit('reset')">
                            Reset
                        </UButton>
                        <UButton color="primary" variant="solid" class="rounded-xl" :loading="submitting" @click="$emit('submit')">
                            {{ mode === 'create' ? 'Simpan' : 'Update' }}
                        </UButton>
                    </div>
                </div>
            </div>
        </template>
    </UModal>
</template>
