import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

interface FooterData {
    pages: { title: string; slug: string; template?: string | null; show_on?: string | null }[]
    supportPages?: { title: string; slug: string; template?: string | null; show_on?: string | null }[]
    companyPages?: { title: string; slug: string; template?: string | null; show_on?: string | null }[]
    headerTopBarPages?: { title: string; slug: string; template?: string | null; show_on?: string | null }[]
    headerNavbarPages?: { title: string; slug: string; template?: string | null; show_on?: string | null }[]
    headerBottomBarPages?: { title: string; slug: string; template?: string | null; show_on?: string | null }[]
    bottomMainPages?: { title: string; slug: string; template?: string | null; show_on?: string | null }[]
    paymentMethods: { code: string; name: string }[]
    categories: { slug: string; name: string }[]
    socialLinks: Record<string, string>
    store: {
        name: string | null
        description: string | null
        email: string | null
        phone: string | null
        tagline: string | null
    }
}

export interface AuthCustomer {
    id: number
    name: string
    email: string
}

export interface WishlistItemData {
    id: number
    productId: number
    name: string
    sku: string
    price: number
    image?: string | null
    inStock: boolean
    slug: string
}

export interface CartItemData {
    id: number
    productId: number
    name: string
    sku: string
    variant?: string | null
    price: number
    qty: number
    rowTotal: number
    image?: string | null
    inStock: boolean
}

export function useStoreData() {
    const page = usePage<{
        footer: FooterData
        appName: string
        wishlistCount: number
        wishlistItems: WishlistItemData[]
        cartCount: number
        cartItems: CartItemData[]
        auth: { customer: AuthCustomer | null }
    }>()

    const footer = computed(() => page.props.footer)
    const appName = computed(() => page.props.appName ?? 'Store')
    const wishlistCount = computed(() => page.props.wishlistCount ?? 0)
    const wishlistItems = computed(() => page.props.wishlistItems ?? [])
    const cartCount = computed(() => page.props.cartCount ?? 0)
    const cartItems = computed(() => page.props.cartItems ?? [])
    const authCustomer = computed(() => page.props.auth?.customer ?? null)
    const isLoggedIn = computed(() => authCustomer.value !== null)

    const storeEmail = computed(() => footer.value?.store?.email ?? 'hello@puranusa.id')
    const storePhone = computed(() => footer.value?.store?.phone ?? '+62 812 3456 7890')
    const storeDescription = computed(() => footer.value?.store?.description ?? 'Temukan produk pilihan berkualitas tinggi dengan harga terbaik.')

    const socialLinks = computed(() => {
        const socialIconMap: Record<string, string> = {
            instagram: 'i-lucide-instagram',
            youtube: 'i-lucide-youtube',
            tiktok: 'i-lucide-music',
            facebook: 'i-lucide-facebook',
            x: 'i-lucide-twitter',
            whatsapp: 'i-lucide-message-circle'
        }

        const socialLabelMap: Record<string, string> = {
            instagram: 'Instagram',
            youtube: 'YouTube',
            tiktok: 'TikTok',
            facebook: 'Facebook',
            x: 'X',
            whatsapp: 'WhatsApp'
        }

        return Object.entries(footer.value?.socialLinks ?? {}).map(([key, url]) => ({
            label: socialLabelMap[key] ?? key,
            icon: socialIconMap[key] ?? 'i-lucide-link',
            to: url
        }))
    })

    const mapPageLinks = (pages: Array<{ title: string; slug: string }>) => pages
        .filter((page) => page.slug?.trim() !== '')
        .map((page) => ({
            label: page.title,
            to: `/page/${page.slug}`
        }))

    const uniqueLinksByUrl = <T extends { to: string }>(links: T[]): T[] => {
        const seen = new Set<string>()

        return links.filter((link) => {
            if (seen.has(link.to)) {
                return false
            }

            seen.add(link.to)
            return true
        })
    }

    const allFooterPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.pages ?? [])))
    const headerTopBarPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.headerTopBarPages ?? [])))
    const headerNavbarPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.headerNavbarPages ?? [])))
    const headerBottomBarPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.headerBottomBarPages ?? [])))
    const bottomMainPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.bottomMainPages ?? [])))

    const supportPages = computed(() => {
        const sharedSupportPages = uniqueLinksByUrl(mapPageLinks(footer.value?.supportPages ?? []))
        if (sharedSupportPages.length > 0) {
            return sharedSupportPages
        }

        const sourcePages = footer.value?.pages ?? []
        const supportTemplates = new Set(['faq', 'contact'])
        const supportPagesFromTemplate = uniqueLinksByUrl(
            mapPageLinks(
                sourcePages.filter((page) => {
                    const template = (page.template ?? '').toLowerCase()
                    return supportTemplates.has(template)
                })
            )
        )

        if (supportPagesFromTemplate.length > 0) {
            return supportPagesFromTemplate
        }

        const supportKeyword = /(faq|kontak|contact|bantuan|help|dukungan|support|pengiriman|returns?)/i
        return uniqueLinksByUrl(
            mapPageLinks(
                sourcePages.filter((page) => supportKeyword.test(`${page.slug} ${page.title}`))
            )
        )
    })

    const companyPages = computed(() => {
        const supportLinkSet = new Set(supportPages.value.map((page) => page.to))

        const sharedCompanyPages = uniqueLinksByUrl(mapPageLinks(footer.value?.companyPages ?? []))
        if (sharedCompanyPages.length > 0) {
            return sharedCompanyPages.filter((page) => !supportLinkSet.has(page.to))
        }

        return uniqueLinksByUrl(
            mapPageLinks(footer.value?.pages ?? []).filter((page) => !supportLinkSet.has(page.to))
        )
    })

    return {
        footer,
        appName,
        wishlistCount,
        wishlistItems,
        cartCount,
        cartItems,
        authCustomer,
        isLoggedIn,
        storeEmail,
        storePhone,
        storeDescription,
        socialLinks,
        allFooterPages,
        headerTopBarPages,
        headerNavbarPages,
        headerBottomBarPages,
        bottomMainPages,
        companyPages,
        supportPages,
        paymentMethods: computed(() => footer.value?.paymentMethods ?? []),
    }
}
