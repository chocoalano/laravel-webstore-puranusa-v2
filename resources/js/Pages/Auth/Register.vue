<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import RegisterInfoPanel from '@/components/auth/RegisterInfoPanel.vue'
import RegisterFormPanel from '@/components/auth/RegisterFormPanel.vue'
import SeoHead from '@/components/SeoHead.vue'
import { useRegisterForm } from '@/composables/useRegisterForm'

defineOptions({ layout: AppLayout })

const props = defineProps<{
    seo: { title: string; description: string; canonical: string }
    referralCode?: string
    referralUsername?: string
    debugMode: boolean
}>()

const { form, validate, firstError, onSubmit, autofillDebugForm, resetRegisterForm } = useRegisterForm(
    props.referralCode,
    props.referralUsername,
)
const isReferralReadonly = Boolean(props.referralUsername ?? props.referralCode)
</script>

<template>
    <SeoHead :title="seo.title" :description="seo.description" :canonical="seo.canonical" />

    <div class="grid min-h-dvh lg:grid-cols-2">
        <RegisterInfoPanel />

        <RegisterFormPanel
            :form="form"
            :validate="validate"
            :first-error="firstError"
            :is-referral-readonly="isReferralReadonly"
            :debug-mode="props.debugMode"
            :on-autofill-debug-form="autofillDebugForm"
            :on-reset-register-form="resetRegisterForm"
            @submit="onSubmit"
        />
    </div>
</template>
