<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import { useStoreData } from '@/composables/useStoreData'

const { appName, storeDescription, socialLinks } = useStoreData()
const toast = useToast()

const form = useForm({
    email: '',
})

function submitNewsletter(): void {
    form.clearErrors()

    form.post('/newsletter/subscribe', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.add({
                title: 'Berhasil',
                description: 'Permintaan langganan promo sudah diproses.',
                color: 'success',
            })
            form.reset('email')
        },
        onError: () => {
            const message = form.errors.email || 'Terjadi kendala saat memproses langganan.'

            toast.add({
                title: 'Gagal berlangganan',
                description: message,
                color: 'error',
            })
        },
    })
}
</script>

<template>
    <div class="sm:col-span-2 lg:col-span-4">
        <div class="flex items-center gap-2.5">
            <div
                class="grid size-10 place-items-center rounded-xl bg-gray-900 text-white dark:bg-white dark:text-gray-900">
                <UIcon name="i-lucide-shopping-bag" class="size-5" />
            </div>
            <span class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">
                {{ appName }}
            </span>
        </div>

        <p class="mt-4 max-w-xs text-sm leading-relaxed text-gray-600 dark:text-gray-400">
            {{ storeDescription }}
        </p>

        <div class="mt-6 max-w-sm">
            <p class="text-sm font-medium text-gray-900 dark:text-white">Dapatkan promo terbaru</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Diskon eksklusif langsung ke inbox Anda</p>

            <form class="mt-3 flex gap-2" @submit.prevent="submitNewsletter">
                <UInput v-model="form.email" placeholder="Alamat email" icon="i-lucide-mail" class="flex-1" :ui="{
                    base: 'h-10 rounded-xl bg-gray-100/70 border border-gray-200/60 text-gray-900 placeholder:text-gray-500 focus:ring-2 focus:ring-primary/30 dark:bg-white/5 dark:border-white/10 dark:text-white dark:placeholder:text-gray-500'
                }" :disabled="form.processing" />
                <UButton class="h-10 shrink-0 rounded-xl" aria-label="Subscribe" type="submit"
                    :loading="form.processing" :disabled="form.processing">
                    Langganan
                </UButton>
            </form>

            <p v-if="form.errors.email" class="mt-2 text-xs text-rose-600 dark:text-rose-300">
                {{ form.errors.email }}
            </p>
        </div>

        <div class="mt-6 flex items-center gap-1">
            <UButton v-for="s in socialLinks" :key="s.label" :to="s.to" target="_blank" color="neutral" variant="ghost"
                class="rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                :aria-label="s.label" :icon="s.icon" />
        </div>
    </div>
</template>
