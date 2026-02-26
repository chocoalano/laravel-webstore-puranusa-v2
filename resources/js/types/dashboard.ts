export type CustomerNpwp = {
    nama?: string | null
    npwp?: string | null
    jk?: number | null
    npwp_date?: string | null
    alamat?: string | null
    menikah?: 'Y' | 'N' | null
    anak?: string | null
    kerja?: 'Y' | 'N' | null
    office?: string | null
}

export type Customer = {
    id?: number
    username?: string | null
    nik?: string | null
    name: string
    gender?: string | null
    email?: string
    phone?: string
    alamat?: string | null
    bank_name?: string | null
    bank_account?: string | null
    npwp?: CustomerNpwp | null
    avatar_url?: string
    tier?: string
    member_since?: string // ISO
    wallet_balance?: number
}

export type Address = {
    id: number | string
    label: string
    recipient_name: string
    phone: string
    address_line: string
    city: string
    province: string
    postal_code: string
    is_default?: boolean
}

export type DashboardAddress = {
    id: number | string
    label?: string | null
    is_default: boolean
    recipient_name: string
    recipient_phone: string
    address_line1: string
    address_line2?: string | null
    province_label: string
    province_id: number
    city_label: string
    city_id: number
    district?: string | null
    district_lion?: string | null
    postal_code?: string | null
    country?: string | null
    description?: string | null
}

export type Stats = {
    orders_total?: number
    orders_pending?: number
    network_total?: number
    network_active?: number
    network_level?: number
    bonus_month?: number
    bonus_lifetime?: number
    bonus_available?: number
    wallet_balance?: number
    promo_active?: number
}

export type NetworkProfile = {
    username?: string
    level?: string | number
    referral_code?: string
    balance?: number
}

export type NetworkStats = {
    left_count?: number
    right_count?: number
    total_downline?: number
    omset_nb_left?: number
    omset_nb_right?: number
    omset_retail_left?: number
    omset_retail_right?: number
    omset_group?: number
}

export type DashboardMitraMember = {
    id: number
    username: string
    name: string
    email: string
    phone?: string | null
    package_name?: string | null
    total_left?: number
    total_right?: number
    position?: string | null
    level?: string | number | null
    has_placement: boolean
    has_purchase: boolean
    omzet: number
    joined_at?: string | null
    status: number
    status_label: string
}

export type DashboardNetworkTreeNode = {
    id: number
    member_id: number
    name: string
    username: string
    email?: string | null
    phone?: string | null
    package_name?: string | null
    total_left: number
    total_right: number
    position?: 'left' | 'right' | null
    level: number
    status: boolean
    joined_at?: string | null
    has_children: boolean
    left: DashboardNetworkTreeNode | null
    right: DashboardNetworkTreeNode | null
}

export type DashboardNetworkTreeStats = {
    left_count: number
    right_count: number
    total_downlines: number
}

export type DashboardWalletTransactionType =
    | 'topup'
    | 'withdrawal'
    | 'bonus'
    | 'purchase'
    | 'refund'
    | 'tax'
    | 'other'

export type DashboardWalletTransactionDirection = 'credit' | 'debit'

export type DashboardWalletTransactionStatus = 'pending' | 'completed' | 'failed' | 'cancelled'

export type DashboardWalletTransaction = {
    id: number | string
    type: DashboardWalletTransactionType
    type_label: string
    direction: DashboardWalletTransactionDirection
    status: DashboardWalletTransactionStatus
    status_label: string
    amount: number
    balance_before: number
    balance_after: number
    payment_method?: string | null
    transaction_ref?: string | null
    midtrans_transaction_id?: string | null
    notes?: string | null
    is_system: boolean
    created_at?: string | null
    completed_at?: string | null
    description: string
    to?: string | null
}

export type DashboardWalletFilters = {
    search?: string | null
    type?: DashboardWalletTransactionType | null
    status?: DashboardWalletTransactionStatus | null
}

export type DashboardWalletTransactionsPagination = {
    data: DashboardWalletTransaction[]
    current_page: number
    next_page: number | null
    has_more: boolean
    per_page: number
    total: number
    filters?: DashboardWalletFilters
}

export type DashboardBonusType =
    | 'referral_incentive'
    | 'team_affiliate_commission'
    | 'partner_team_commission'
    | 'cashback_commission'
    | 'promotions_rewards'
    | 'retail_commission'
    | 'lifetime_cash_rewards'

export type DashboardBonusStatus = 'pending' | 'released'

export type DashboardBonusMeta = {
    index_value?: number | null
    level?: number | null
    pairing_count?: number | null
    order_id?: number | null
    reward_type?: string | null
    reward_name?: string | null
    reward?: number | null
    bv?: number | null
}

export type DashboardBonusRow = {
    id: number | string
    type: DashboardBonusType
    type_label: string
    amount: number
    status: DashboardBonusStatus
    status_label: string
    description?: string | null
    created_at?: string | null
    from_member?: {
        name: string
        email?: string | null
    } | null
    meta?: DashboardBonusMeta
}

export type DashboardBonusTables = {
    referral_incentive: DashboardBonusRow[]
    team_affiliate_commission: DashboardBonusRow[]
    partner_team_commission: DashboardBonusRow[]
    cashback_commission: DashboardBonusRow[]
    promotions_rewards: DashboardBonusRow[]
    retail_commission: DashboardBonusRow[]
    lifetime_cash_rewards: DashboardBonusRow[]
}

export type DashboardBonusStat = {
    key: DashboardBonusType | 'total_bonus'
    title: string
    icon: string
    amount: number
    count: number
}

export type DashboardLifetimeReward = {
    id: number
    name: string
    reward?: string | null
    bv: number
    value: number
    can_claim: boolean
    is_claimed: boolean
    accumulated_left: number
    accumulated_right: number
    progress_left: number
    progress_right: number
    progress_percent: number
}

export type DashboardLifetimeClaimed = {
    id: number
    reward?: string | null
    bv: number
    amount: number
    status: 'pending' | 'released'
    status_label: string
    description?: string | null
    created_at?: string | null
}

export type DashboardLifetimeSummary = {
    accumulated_left: number
    accumulated_right: number
    eligible_count: number
    claimed_count: number
    remaining_count: number
}

export type DashboardLifetimeRewardsData = {
    summary: DashboardLifetimeSummary
    rewards: DashboardLifetimeReward[]
    claimed: DashboardLifetimeClaimed[]
}

export type DashboardOrderItemPreview = {
    id: number | string
    name: string
    sku?: string | null
    variant?: string | null
    qty: number
    price: number
    row_total?: number
    image?: string | null
}

export type DashboardOrderShippingAddress = {
    recipient_name?: string | null
    recipient_phone?: string | null
    address_line1?: string | null
    address_line2?: string | null
    district?: string | null
    city?: string | null
    province?: string | null
    postal_code?: string | null
    country?: string | null
}

export type DashboardOrder = {
    id: number | string
    code: string
    created_at: string
    status: 'pending' | 'paid' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded'
    payment_status?: 'unpaid' | 'paid' | 'refunded' | 'failed' | null
    payment_method?: string | null
    payment_method_code?: string | null
    shipping_method?: string | null
    subtotal?: number
    discount_amount?: number
    shipping_cost?: number
    tax_amount?: number
    total: number
    items_count: number
    items?: DashboardOrderItemPreview[]
    items_preview?: DashboardOrderItemPreview[]
    tracking_number?: string | null
    notes?: string | null
    paid_at?: string | null
    shipping_address?: DashboardOrderShippingAddress | null
    customer?: { name: string; email?: string | null }
    to?: string | null
}

export type DashboardOrdersPagination = {
    data: DashboardOrder[]
    current_page: number
    next_page: number | null
    has_more: boolean
    per_page: number
    total: number
}

export type DashboardMidtransConfig = {
    env: 'sandbox' | 'production'
    client_key: string
}

export type DashboardPromoType = 'voucher' | 'discount' | 'flash' | 'shipping' | 'bundle' | 'member'

export type DashboardPromo = {
    id: number | string
    title: string
    description?: string | null
    code?: string | null
    type: DashboardPromoType
    discount_label?: string | null
    min_spend?: number | null
    max_discount?: number | null
    quota_left?: number | null
    expires_at?: string | null
    terms?: string[] | null
    claimed?: boolean
    to?: string | null
    highlight?: boolean
}

export type DashboardZennerCategory = {
    id: number
    parent_id?: number | null
    name: string
    slug: string
    contents_count?: number
}

export type DashboardZennerContent = {
    id: number
    category_id?: number | null
    category_name?: string | null
    category_slug?: string | null
    title: string
    slug: string
    excerpt: string
    content?: string | null
    file?: string | null
    vlink?: string | null
    status?: string | null
    status_label?: string
    created_at?: string | null
    updated_at?: string | null
}

export type SecuritySummary = {
    account_status_label?: string
    email_verified?: boolean
    has_bank_account?: boolean
    has_npwp?: boolean
    last_order_at?: string | null
}

export type DashboardSeo = {
    title: string
    description: string
    canonical: string
}

export type DashboardProvinceOption = {
    id: number
    label: string
}

export type DashboardCityOption = {
    id: number
    province_id: number
    label: string
}

export type DashboardDistrictOption = {
    province_id: number
    city_id: number
    label: string
    district_lion: string
}

export type DashboardPageProps = {
    seo: DashboardSeo
    customer?: Customer | null
    currentCustomerId?: number | null
    defaultAddress?: Address | null
    addresses?: DashboardAddress[]
    provinces?: DashboardProvinceOption[]
    cities?: DashboardCityOption[]
    districts?: DashboardDistrictOption[]
    orders?: DashboardOrdersPagination
    walletTransactions?: DashboardWalletTransactionsPagination
    hasPendingWithdrawal?: boolean
    bonusStats?: DashboardBonusStat[]
    bonusTables?: DashboardBonusTables
    lifetimeRewards?: DashboardLifetimeRewardsData
    promos?: DashboardPromo[]
    zennerCategories?: DashboardZennerCategory[]
    zennerContents?: DashboardZennerContent[]
    midtrans?: DashboardMidtransConfig
    activeMembers?: DashboardMitraMember[]
    passiveMembers?: DashboardMitraMember[]
    prospectMembers?: DashboardMitraMember[]
    binaryTree?: DashboardNetworkTreeNode | null
    networkTreeStats?: DashboardNetworkTreeStats | null
    hasLeft?: boolean
    hasRight?: boolean
    stats?: Stats
    networkProfile?: NetworkProfile
    networkStats?: NetworkStats
    securitySummary?: SecuritySummary
}

export type DashboardSectionKey =
    | 'dashboard'
    | 'form_account'
    | 'orders'
    | 'promo'
    | 'wallet'
    | 'zenner'
    | 'mitra'
    | 'network'
    | 'bonus'
    | 'lifetime'
    | 'addresses'
    | 'delete'

export type DashboardAsideLabelLink = {
    label: string
    type: 'label'
}

export type DashboardAsideActionLink = {
    label: string
    icon: string
    value: DashboardSectionKey
    color?: 'neutral' | 'primary' | 'error'
}

export type DashboardAsideLink = DashboardAsideLabelLink | DashboardAsideActionLink
