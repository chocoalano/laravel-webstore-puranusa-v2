<script setup lang="ts">
const props = defineProps<{
    search: string
    selectedCategory: string
    categoryItems: Array<{ label: string; value: string }>
}>()

const emit = defineEmits<{
    (e: 'update:search', value: string): void
    (e: 'update:selectedCategory', value: string): void
}>()

function onSearchUpdate(value: string | number): void {
    emit('update:search', String(value ?? ''))
}

function onCategoryUpdate(value: string): void {
    emit('update:selectedCategory', value)
}
</script>

<template>
    <div class="flex flex-col gap-3 md:flex-row md:items-end">
        <UFormField label="Cari konten" class="w-full md:flex-1">
            <UInput
                :model-value="props.search"
                placeholder="Cari judul, kategori, atau ringkasan..."
                icon="i-lucide-search"
                class="w-full"
                @update:model-value="onSearchUpdate"
            />
        </UFormField>

        <UFormField label="Kategori" class="w-full md:w-80">
            <USelectMenu
                :model-value="props.selectedCategory"
                :items="props.categoryItems"
                value-key="value"
                label-key="label"
                class="w-full"
                @update:model-value="onCategoryUpdate"
            />
        </UFormField>
    </div>
</template>
