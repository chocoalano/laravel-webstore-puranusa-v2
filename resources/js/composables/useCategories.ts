import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

interface Category {
    id: number
    slug: string
    name: string
    description: string | null
    image: string | null
    productCount: number
}

export function useCategories() {
    const page = usePage<{ categories: Category[] }>()

    const categoriesRaw = computed(() => page.props.categories ?? [])

    const categoryIconMap: Record<string, string> = {
        fashion: 'i-lucide-shirt',
        elektronik: 'i-lucide-smartphone',
        kecantikan: 'i-lucide-sparkles',
        beauty: 'i-lucide-sparkles',
        olahraga: 'i-lucide-dumbbell',
        rumah: 'i-lucide-lamp',
        makanan: 'i-lucide-utensils',
        anak: 'i-lucide-baby',
        kesehatan: 'i-lucide-heart-pulse',
        health: 'i-lucide-heart-pulse',
        otomotif: 'i-lucide-car',
        buku: 'i-lucide-book-open',
    }

    const gradientMap: Record<string, string> = {
        fashion: 'from-pink-500 to-rose-500',
        elektronik: 'from-blue-500 to-cyan-500',
        kecantikan: 'from-purple-500 to-violet-500',
        beauty: 'from-purple-500 to-violet-500',
        olahraga: 'from-emerald-500 to-teal-500',
        rumah: 'from-amber-500 to-orange-500',
        makanan: 'from-red-500 to-pink-500',
        anak: 'from-sky-500 to-blue-500',
        kesehatan: 'from-green-500 to-emerald-500',
        health: 'from-green-500 to-emerald-500',
        otomotif: 'from-slate-500 to-gray-600',
        buku: 'from-indigo-500 to-purple-500',
    }

    const defaultGradients = [
        'from-pink-500 to-rose-500',
        'from-blue-500 to-cyan-500',
        'from-purple-500 to-violet-500',
        'from-emerald-500 to-teal-500',
        'from-amber-500 to-orange-500',
        'from-red-500 to-pink-500',
    ]

    const getCategoryIcon = (slug: string): string => {
        const s = (slug || '').toLowerCase()
        for (const [key, icon] of Object.entries(categoryIconMap)) {
            if (s.includes(key)) return icon
        }
        return 'i-lucide-tag'
    }

    const getCategoryGradient = (slug: string, index: number): string => {
        const s = (slug || '').toLowerCase()
        for (const [key, gradient] of Object.entries(gradientMap)) {
            if (s.includes(key)) return gradient
        }
        return defaultGradients[index % defaultGradients.length]
    }

    const mappedCategories = computed(() => {
        return categoriesRaw.value.map((cat, index) => ({
            ...cat,
            icon: getCategoryIcon(cat.slug),
            gradient: getCategoryGradient(cat.slug, index),
            href: `/shop?category=${cat.slug}`,
            label: cat.name,
            to: `/shop?category=${cat.slug}`, // for consistency with header
        }))
    })

    return {
        categories: mappedCategories,
        getCategoryIcon,
        getCategoryGradient,
    }
}
