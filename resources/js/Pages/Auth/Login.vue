<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import LoginInfoPanel from '@/components/auth/LoginInfoPanel.vue'
import LoginFormPanel from '@/components/auth/LoginFormPanel.vue'
import SeoHead from '@/components/SeoHead.vue'
import { useLoginForm } from '@/composables/useLoginForm'

defineOptions({ layout: AppLayout })

defineProps<{
    seo: { title: string; description: string; canonical: string }
}>()

const { form, validate, firstError, flashStatus, onSubmit } = useLoginForm()
</script>

<template>
    <SeoHead :title="seo.title" :description="seo.description" :canonical="seo.canonical" />

    <div class="grid min-h-dvh lg:grid-cols-2">
        <LoginInfoPanel />

        <LoginFormPanel
            :form="form"
            :validate="validate"
            :first-error="firstError"
            :flash-status="flashStatus"
            @submit="onSubmit"
        />
    </div>
</template>
