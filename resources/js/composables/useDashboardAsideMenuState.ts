import { ref } from 'vue'
import type { DashboardSectionKey } from '@/types/dashboard'

type UseDashboardAsideMenuStateOptions = {
    onSelect: (section: DashboardSectionKey) => void
}

export function useDashboardAsideMenuState(options: UseDashboardAsideMenuStateOptions) {
    const mobileMenuOpen = ref(false)

    function openMobileMenu(): void {
        mobileMenuOpen.value = true
    }

    function closeMobileMenu(): void {
        mobileMenuOpen.value = false
    }

    function selectSection(section: DashboardSectionKey, closeAfterSelect = false): void {
        options.onSelect(section)

        if (closeAfterSelect) {
            closeMobileMenu()
        }
    }

    return {
        mobileMenuOpen,
        openMobileMenu,
        closeMobileMenu,
        selectSection,
    }
}
