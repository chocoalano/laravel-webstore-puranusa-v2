import type {
    DashboardAsideActionLink,
    DashboardAsideLabelLink,
    DashboardAsideLink,
} from '@/types/dashboard'

export function useDashboardAsideLinks() {
    function isLabelLink(link: DashboardAsideLink): link is DashboardAsideLabelLink {
        return 'type' in link && link.type === 'label'
    }

    function isActionLink(link: DashboardAsideLink): link is DashboardAsideActionLink {
        return !isLabelLink(link)
    }

    return {
        isLabelLink,
        isActionLink,
    }
}
