<script setup lang="ts">
import { computed } from 'vue'
import { useDashboardLifetime } from '@/composables/useDashboardLifetime'
import type { DashboardLifetimeRewardsData } from '@/types/dashboard'
import LifetimeSummaryCards from '@/components/dashboard/lifetime/LifetimeSummaryCards.vue'
import LifetimeRewardsTableCard from '@/components/dashboard/lifetime/LifetimeRewardsTableCard.vue'
import LifetimeClaimedTableCard from '@/components/dashboard/lifetime/LifetimeClaimedTableCard.vue'

const props = withDefaults(
    defineProps<{
        lifetimeRewards?: DashboardLifetimeRewardsData
    }>(),
    {
        lifetimeRewards: () => ({
            summary: {
                accumulated_left: 0,
                accumulated_right: 0,
                eligible_count: 0,
                claimed_count: 0,
                remaining_count: 0,
            },
            rewards: [],
            claimed: [],
        }),
    }
)

const { summary, rewards, claimed, rewardColumns, claimedColumns, formatIDR } = useDashboardLifetime({
    lifetimeRewards: computed(() => props.lifetimeRewards),
})
</script>

<template>
    <div class="space-y-6">
        <LifetimeSummaryCards :summary="summary" :format-currency="formatIDR" />
        <LifetimeRewardsTableCard :rewards="rewards" :columns="rewardColumns" />
        <LifetimeClaimedTableCard :claimed="claimed" :columns="claimedColumns" />
    </div>
</template>
