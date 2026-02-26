<script setup lang="ts">
const props = defineProps<{
    page: number
    total: number
    perPage: number
    from: number | null
    to: number | null
}>()

const emit = defineEmits<{
    pageChange: [value: number]
}>()
</script>

<template>
    <UCard class="rounded-2xl border border-default/70">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-muted">
                Menampilkan
                <span class="font-semibold text-highlighted">{{ props.from ?? 0 }} - {{ props.to ?? 0 }}</span>
                dari
                <span class="font-semibold text-highlighted">{{ props.total }}</span>
                artikel.
            </p>

            <UPagination
                :page="props.page"
                :total="props.total"
                :items-per-page="props.perPage"
                show-edges
                @update:page="(value: number) => emit('pageChange', value)"
            />
        </div>
    </UCard>
</template>
