<script setup lang="ts">
import { computed } from 'vue'
import AddressList from '@/components/dashboard/addresses/AddressList.vue'
import AddressFormModal from '@/components/dashboard/addresses/AddressFormModal.vue'
import AddressDeleteConfirmModal from '@/components/dashboard/addresses/AddressDeleteConfirmModal.vue'
import AddressDefaultSwitchModal from '@/components/dashboard/addresses/AddressDefaultSwitchModal.vue'
import {
    useDashboardAddresses,
    type DashboardAddressCityOption,
    type DashboardAddressDistrictOption,
    type DashboardAddressProvinceOption,
} from '@/composables/useDashboardAddresses'
import type { DashboardAddress } from '@/types/dashboard'

const props = defineProps<{
    addresses?: DashboardAddress[]
    provinces?: DashboardAddressProvinceOption[]
    cities?: DashboardAddressCityOption[]
    districts?: DashboardAddressDistrictOption[]
}>()

const addresses = computed(() => props.addresses ?? [])
const provinces = computed(() => props.provinces ?? [])
const cities = computed(() => props.cities ?? [])
const districts = computed(() => props.districts ?? [])

const {
    formOpen,
    deleteOpen,
    blockedOpen,
    formMode,
    submitting,
    deleting,
    settingDefault,
    selectedForDelete,
    otherAddressesForDefault,
    form,
    errors,
    provinceItems,
    cityItems,
    districtItems,
    resetForm,
    openCreate,
    openEdit,
    submitForm,
    setAsDefault,
    requestDelete,
    confirmDelete,
    setDefaultThenContinueDelete,
    closeBlockedAndOpenCreate,
} = useDashboardAddresses({
    addresses,
    provinces,
    cities,
    districts,
})
</script>

<template>
    <UCard class="rounded-2xl">
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <UIcon name="i-lucide-map-pin" class="size-5 text-gray-500 dark:text-gray-300" />
                    <div>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">Alamat</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Kelola alamat pengiriman untuk checkout lebih cepat.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <UButton color="primary" variant="soft" size="sm" class="rounded-xl" icon="i-lucide-plus" @click="openCreate">
                        Tambah Alamat
                    </UButton>
                    <UButton to="/account/addresses" color="neutral" variant="outline" size="sm" class="rounded-xl">
                        Lihat Semua
                    </UButton>
                </div>
            </div>
        </template>

        <AddressList
            :addresses="addresses"
            :setting-default="settingDefault"
            @create="openCreate"
            @edit="openEdit"
            @delete="requestDelete"
            @set-default="setAsDefault"
        />

        <AddressFormModal
            v-model:open="formOpen"
            :mode="formMode"
            :form="form"
            :errors="errors"
            :submitting="submitting"
            :province-items="provinceItems"
            :city-items="cityItems"
            :district-items="districtItems"
            @submit="submitForm"
            @reset="resetForm"
        />

        <AddressDeleteConfirmModal
            v-model:open="deleteOpen"
            :address="selectedForDelete"
            :deleting="deleting"
            @confirm="confirmDelete"
        />

        <AddressDefaultSwitchModal
            v-model:open="blockedOpen"
            :selected-address="selectedForDelete"
            :other-addresses="otherAddressesForDefault"
            :setting-default="settingDefault"
            @create-address="closeBlockedAndOpenCreate"
            @set-default-continue="setDefaultThenContinueDelete"
        />
    </UCard>
</template>
