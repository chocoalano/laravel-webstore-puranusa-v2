<script setup lang="ts">
import { computed } from 'vue'
import { useDashboardBonus } from '@/composables/useDashboardBonus'
import type { DashboardBonusStat, DashboardBonusTables, DashboardBonusType } from '@/types/dashboard'
import BonusStatsGrid from '@/components/dashboard/bonus/BonusStatsGrid.vue'
import BonusHistoryTableCard from '@/components/dashboard/bonus/BonusHistoryTableCard.vue'

type BonusTab = DashboardBonusType | 'all'

const props = withDefaults(
    defineProps<{
        bonusStats?: DashboardBonusStat[]
        bonusTables?: DashboardBonusTables
    }>(),
    {
        bonusStats: () => [],
        bonusTables: () => ({
            referral_incentive: [],
            team_affiliate_commission: [],
            partner_team_commission: [],
            cashback_commission: [],
            promotions_rewards: [],
            retail_commission: [],
            lifetime_cash_rewards: [],
        }),
    }
)

const {
    activeTab,
    searchQuery,
    page,
    itemsPerPage,
    tabs,
    displayedStats,
    filteredRows,
    paginatedRows,
    columns,
    formatIDR,
} = useDashboardBonus({
    bonusStats: computed(() => props.bonusStats),
    bonusTables: computed(() => props.bonusTables),
})

function onActiveTabChange(value: BonusTab): void {
    activeTab.value = value
}

function onSearchQueryChange(value: string): void {
    searchQuery.value = value
}

function onPageChange(value: number): void {
    page.value = value
}
</script>

<template>
    <div class="space-y-6">
        <BonusStatsGrid :stats="displayedStats" :format-currency="formatIDR" />

        <BonusHistoryTableCard
            :active-tab="activeTab"
            :search-query="searchQuery"
            :page="page"
            :items-per-page="itemsPerPage"
            :tabs="tabs"
            :rows="paginatedRows"
            :total-rows="filteredRows.length"
            :columns="columns"
            @update:active-tab="onActiveTabChange"
            @update:search-query="onSearchQueryChange"
            @update:page="onPageChange"
        />
    </div>
</template>
