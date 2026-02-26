<script setup lang="ts">
import type { Address, Customer } from '@/types/dashboard'
import DashboardAccountFormCard from '@/components/dashboard/account/DashboardAccountFormCard.vue'

defineProps<{
    customer?: Customer | null
    defaultAddress?: Address | null
}>()
</script>

<template>
    <div class="space-y-6">
        <DashboardAccountFormCard :customer="customer" :default-address="defaultAddress" />
    </div>
</template>
