export type CheckoutItem = {
    id: number
    product_id: number
    name: string
    sku: string
    variant?: string | null
    price: number
    qty: number
    row_total: number
    image?: string | null
    weight_gram?: number | null
}

export type CartTotals = {
    subtotal: number
    discount: number
    shipping: number
    tax: number
    total: number
}

export type CheckoutAddress = {
    id: number | string
    label: string
    recipient_name: string
    phone: string
    address_line: string
    address_line2?: string | null
    province: string
    province_id?: number | null
    city: string
    city_id?: number | null
    postal_code: string
    description?: string | null
    is_default?: boolean
}

export type ShippingRate = {
    product: string
    total_tariff: number
    estimasi_sla: string
}

export type AddressMode = 'saved' | 'manual'

export type PaymentMethod = 'saldo' | 'midtrans'
export type OrderPlanType = 'planA' | 'planB'

export type MidtransConfig = {
    env: 'sandbox' | 'production'
    client_key: string
}

export type CheckoutPageProps = {
    items: CheckoutItem[]
    cart?: CartTotals | null
    addresses: CheckoutAddress[]
    saldo: number
    midtrans: MidtransConfig
}

export type AddressPayload =
    | { address_mode: 'saved'; address_id: number | string }
    | {
          address_mode: 'manual'
          recipient_name: string
          phone: string
          address_line: string
          province: string
          city: string
          district?: string
          postal_code: string
          notes?: string
      }
