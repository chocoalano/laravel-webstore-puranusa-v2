<script setup lang="ts">
import type { Customer, Address, Stats, NetworkProfile, NetworkStats, SecuritySummary } from '@/types/dashboard'
import DashboardStatCards from '@/components/dashboard/DashboardStatCards.vue'
import DashboardAddressWidget from '@/components/dashboard/DashboardAddressWidget.vue'
import DashboardNetworkProfile from '@/components/dashboard/DashboardNetworkProfile.vue'
import DashboardNetworkStats from '@/components/dashboard/DashboardNetworkStats.vue'
import DashboardMemberCard from '@/components/dashboard/DashboardMemberCard.vue'
import DashboardLifetimeCard from '@/components/dashboard/DashboardLifetimeCard.vue'
import DashboardSecurityZone from '@/components/dashboard/DashboardSecurityZone.vue'

defineProps<{
    customer?: Customer | null
    defaultAddress?: Address | null
    stats?: Stats
    networkProfile?: NetworkProfile
    networkStats?: NetworkStats
    securitySummary?: SecuritySummary
}>()

defineEmits<{
    navigate: [section: string]
}>()
</script>

<template>
    <div class="space-y-6">
        <DashboardStatCards :stats="stats" @navigate="$emit('navigate', $event)" />

        <DashboardAddressWidget :default-address="defaultAddress" @navigate="$emit('navigate', $event)" />

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <DashboardNetworkProfile :customer="customer" :network-profile="networkProfile" />
            <DashboardNetworkStats :network-stats="networkStats" @navigate="$emit('navigate', $event)" />
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <DashboardMemberCard :customer="customer" @navigate="$emit('navigate', $event)" />
            <DashboardLifetimeCard :stats="stats" />
        </div>

        <DashboardSecurityZone :security-summary="securitySummary" @navigate="$emit('navigate', $event)" />
    </div>
</template>
