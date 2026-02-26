<script setup lang="ts">
defineProps<{
    totalContents: number
    totalCategories: number
    shownCount: number
}>()
</script>

<template>
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div class="flex items-center gap-3">
            <UIcon name="i-lucide-sparkles" class="size-5 text-gray-500 dark:text-gray-300" />
            <div>
                <p class="text-base font-semibold text-gray-900 dark:text-white">Zenner Content Center</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ totalContents }} konten dari {{ totalCategories }} kategori
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <UBadge color="primary" variant="soft" class="rounded-full">
                {{ shownCount }} ditampilkan
            </UBadge>
        </div>
    </div>
</template>
