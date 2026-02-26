<script setup lang="ts">
import type { GalleryItem } from '@/composables/useProductDetail'

const props = defineProps<{
    items: GalleryItem[]
    activeIndex: number
    discountPercent?: number | null
}>()

const emit = defineEmits<{
    'update:activeIndex': [index: number]
}>()

function selectImage(index: number) {
    emit('update:activeIndex', index)
}
</script>

<template>
    <UCard class="overflow-hidden bg-primary-50 dark:bg-primary-950/40" :ui="{ body: 'p-0' }">
        <div class="relative aspect-4/3 bg-gray-100 dark:bg-gray-900/40">
            <img
                v-if="items[activeIndex]"
                :src="items[activeIndex].src"
                :alt="items[activeIndex].alt"
                class="h-full w-full object-cover rounded-xl"
            />
            <div v-else class="flex h-full w-full items-center justify-center">
                <UIcon name="i-lucide-image" class="size-10 text-gray-300 dark:text-gray-700" />
            </div>

            <div class="absolute left-3 top-3 flex gap-2">
                <UBadge v-if="discountPercent" color="warning" variant="soft">
                    Hemat {{ discountPercent }}%
                </UBadge>
            </div>
        </div>

        <div class="p-4">
            <UCarousel
                v-if="items.length"
                :items="items"
                :ui="{ container: 'gap-3' }"
                class="w-full"
            >
                <template #default="{ item, index }">
                    <button
                        type="button"
                        class="h-20 w-20 overflow-hidden rounded-xl border transition-all"
                        :class="index === activeIndex
                            ? 'border-primary-500 ring-2 ring-primary-500/30'
                            : 'border-gray-200 dark:border-gray-800'"
                        @click="selectImage(index)"
                    >
                        <img :src="item.src" :alt="item.alt" class="h-full w-full object-cover" />
                    </button>
                </template>
            </UCarousel>
        </div>
    </UCard>
</template>
