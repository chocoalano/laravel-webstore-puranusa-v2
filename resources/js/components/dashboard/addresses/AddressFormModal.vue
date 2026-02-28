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
    loadingProvinces: boolean
    loadingCities: boolean
    loadingDistricts: boolean
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
        : 'Perbarui detail alamat yang sudah tersimpan.',
)

// ✅ Fix z-index modal (pastikan di atas header/drawer/tooltip dll)
const modalUi = {
    overlay: 'z-[120]',  // backdrop
    wrapper: 'z-[121]',  // wrapper container
    content: 'z-[122]',  // modal panel
}

// ✅ Fix z-index dropdown USelectMenu (agar tidak ketiban modal/stacking context)
const selectMenuUi = {
    content: 'z-[130]',
}
</script>

<template>
    <UModal v-model:open="isOpen" :title="title" :description="description" scrollable :ui="modalUi">
        <template #body>
            <div class="w-full max-w-2xl space-y-4">
                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Label (opsional)" class="w-full">
                        <UInput v-model="props.form.label" placeholder="Contoh: Rumah, Kantor" class="w-full" />
                    </UFormField>

                    <div class="w-full">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Jadikan default</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default dipakai otomatis saat checkout.
                        </p>
                        <div class="mt-2 flex items-center gap-2">
                            <UCheckbox v-model="props.form.is_default" />
                            <span class="text-sm text-gray-700 dark:text-gray-200">Set sebagai alamat utama</span>
                        </div>
                    </div>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Nama penerima" required :error="props.errors.recipient_name" class="w-full">
                        <UInput v-model="props.form.recipient_name" placeholder="Nama lengkap" class="w-full" />
                    </UFormField>

                    <UFormField label="No. HP penerima" required :error="props.errors.recipient_phone" class="w-full">
                        <UInput v-model="props.form.recipient_phone" placeholder="08xxxxxxxxxx" class="w-full" />
                    </UFormField>
                </div>

                <UFormField label="Alamat utama" required :error="props.errors.address_line1" class="w-full">
                    <UTextarea v-model="props.form.address_line1" placeholder="Jalan, RT/RW, nomor, patokan..."
                        :rows="3" class="w-full" />
                </UFormField>

                <UFormField label="Alamat tambahan (opsional)" class="w-full">
                    <UInput v-model="props.form.address_line2" placeholder="Contoh: Blok A No. 12" class="w-full" />
                </UFormField>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Provinsi" required
                        :error="props.errors.province_id || props.errors.province_label" class="w-full">
                        <div class="space-y-2">
                            <USelectMenu v-model="props.form.province_id" :items="props.provinceItems" value-key="value"
                                label-key="label" placeholder="Pilih provinsi"
                                :disabled="props.loadingProvinces || props.provinceItems.length === 0" class="w-full" :ui="selectMenuUi"
                                :portal="true" />
                            <p v-if="props.loadingProvinces"
                                class="text-xs text-blue-600 dark:text-blue-300">
                                Memuat data provinsi...
                            </p>
                            <p v-else-if="props.provinceItems.length === 0"
                                class="text-xs text-amber-600 dark:text-amber-300">
                                Data provinsi dari RajaOngkir belum tersedia.
                            </p>
                        </div>
                    </UFormField>

                    <UFormField label="Kota/Kab" required :error="props.errors.city_id || props.errors.city_label"
                        class="w-full">
                        <div class="space-y-2">
                            <USelectMenu v-model="props.form.city_id" :items="props.cityItems" value-key="value"
                                label-key="label" placeholder="Pilih kota/kab"
                                :disabled="!props.form.province_id || props.loadingCities || props.cityItems.length === 0" class="w-full"
                                :ui="selectMenuUi" :portal="true" />
                            <p v-if="props.form.province_id && props.loadingCities"
                                class="text-xs text-blue-600 dark:text-blue-300">
                                Memuat data kota/kabupaten...
                            </p>
                            <p v-else-if="props.form.province_id && props.cityItems.length === 0"
                                class="text-xs text-amber-600 dark:text-amber-300">
                                Data kota/kabupaten untuk provinsi ini belum tersedia.
                            </p>
                        </div>
                    </UFormField>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Kecamatan" required :error="props.errors.district" class="w-full">
                        <div class="space-y-2">
                            <USelectMenu v-model="props.form.district" :items="props.districtItems" value-key="value"
                                label-key="label" placeholder="Pilih kecamatan"
                                :disabled="!props.form.city_id || props.loadingDistricts || props.districtItems.length === 0" class="w-full"
                                :ui="selectMenuUi" :portal="true" />
                            <p v-if="props.form.city_id && props.loadingDistricts"
                                class="text-xs text-blue-600 dark:text-blue-300">
                                Memuat data kecamatan...
                            </p>
                            <p v-else-if="props.form.city_id && props.districtItems.length === 0"
                                class="text-xs text-amber-600 dark:text-amber-300">
                                Data kecamatan untuk kota ini belum tersedia.
                            </p>
                        </div>
                    </UFormField>

                    <UFormField label="District Lion (auto)" :error="props.errors.district_lion" class="w-full">
                        <UInput v-model="props.form.district_lion" placeholder="Terisi otomatis dari opsi kecamatan"
                            readonly class="w-full" />
                    </UFormField>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                    <UFormField label="Kode pos (opsional)" :error="props.errors.postal_code" class="w-full">
                        <UInput v-model="props.form.postal_code" placeholder="12345" class="w-full" />
                    </UFormField>

                    <UFormField label="Negara" class="w-full">
                        <UInput v-model="props.form.country" placeholder="Indonesia" class="w-full" />
                    </UFormField>
                </div>

                <UFormField label="Catatan (opsional)" class="w-full">
                    <UInput v-model="props.form.description" placeholder="Contoh: Titip satpam / rumah pagar hitam"
                        class="w-full" />
                </UFormField>

                <div v-if="Object.keys(props.errors).length"
                    class="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200">
                    <p class="font-semibold">Periksa kembali:</p>
                    <ul class="mt-1 list-disc pl-5 space-y-1">
                        <li v-for="(message, key) in props.errors" :key="key">{{ message }}</li>
                    </ul>
                </div>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <UButton color="neutral" variant="outline" class="rounded-xl" :disabled="props.submitting"
                        @click="isOpen = false">
                        Batal
                    </UButton>

                    <div class="flex gap-2">
                        <UButton color="neutral" variant="ghost" class="rounded-xl" :disabled="props.submitting"
                            @click="$emit('reset')">
                            Reset
                        </UButton>
                        <UButton color="primary" variant="solid" class="rounded-xl" :loading="props.submitting"
                            @click="$emit('submit')">
                            {{ props.mode === 'create' ? 'Simpan' : 'Update' }}
                        </UButton>
                    </div>
                </div>
            </div>
        </template>
    </UModal>
</template>
