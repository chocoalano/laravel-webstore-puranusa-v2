<script setup lang="ts">
/**
 * Komponen qty stepper yang dapat digunakan ulang.
 * Pure presentational — semua logika dikendalikan via v-model + events dari parent.
 */
const props = defineProps<{
    modelValue: number
    max: number
    disabled?: boolean
    compact?: boolean
}>()

const emit = defineEmits<{
    'update:modelValue': [value: number]
}>()

function decrease(): void {
    if (props.modelValue > 1) emit('update:modelValue', props.modelValue - 1)
}

function increase(): void {
    if (props.modelValue < props.max) emit('update:modelValue', props.modelValue + 1)
}

function onInput(event: Event): void {
    const raw = parseInt((event.target as HTMLInputElement).value, 10)

    if (isNaN(raw) || raw < 1) {
        emit('update:modelValue', 1)
    } else if (raw > props.max) {
        emit('update:modelValue', props.max)
    } else {
        emit('update:modelValue', raw)
    }
}
</script>

<template>
    <div class="flex items-center gap-2">
        <div
            class="flex items-center overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"
        >
            <button
                type="button"
                :disabled="disabled || modelValue <= 1"
                class="flex items-center justify-center text-gray-500 transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-400 dark:hover:bg-gray-800"
                :class="compact ? 'h-9 w-9' : 'h-10 w-10'"
                @click="decrease"
            >
                <UIcon name="i-lucide-minus" :class="compact ? 'size-3.5' : 'size-4'" />
            </button>

            <input
                v-if="!compact"
                :value="modelValue"
                type="number"
                :min="1"
                :max="max"
                :disabled="disabled"
                class="h-10 w-14 border-x border-gray-200 bg-transparent text-center text-sm font-bold text-gray-900 focus:outline-none disabled:opacity-50 dark:border-gray-700 dark:text-white [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                @input="onInput"
            />
            <span
                v-else
                class="w-8 text-center text-sm font-bold text-gray-900 dark:text-white"
            >
                {{ modelValue }}
            </span>

            <button
                type="button"
                :disabled="disabled || modelValue >= max"
                class="flex items-center justify-center text-gray-500 transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-400 dark:hover:bg-gray-800"
                :class="compact ? 'h-9 w-9' : 'h-10 w-10'"
                @click="increase"
            >
                <UIcon name="i-lucide-plus" :class="compact ? 'size-3.5' : 'size-4'" />
            </button>
        </div>

        <span v-if="!compact" class="text-xs text-gray-400 dark:text-gray-500">
            Maks. {{ max }} pcs
        </span>
    </div>
</template>
