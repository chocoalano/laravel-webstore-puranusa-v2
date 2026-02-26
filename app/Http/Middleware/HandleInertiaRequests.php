<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Repositories\Pages\Contracts\PageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'csrf_token' => fn () => csrf_token(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'wallet' => fn () => $request->session()->get('wallet'),
                'orders' => fn () => $request->session()->get('orders'),
                'checkout' => fn () => $request->session()->get('checkout'),
            ],
            'categories' => fn () => $this->categoriesData(),
            'footer' => fn () => $this->footerData(),
            'auth' => [
                'customer' => fn () => $this->authenticatedCustomer($request),
            ],
            'impersonation' => fn () => $this->impersonationData($request),
            'wishlistCount' => fn () => $this->wishlistCount(),
            'wishlistItems' => fn () => $this->wishlistItemsData(),
            'cartCount' => fn () => $this->cartCount(),
            'cartItems' => fn () => $this->cartItemsData(),
        ];
    }

    /**
     * Data customer yang sedang login, atau null jika belum login.
     *
     * @return array{id: int, name: string, email: string}|null
     */
    private function authenticatedCustomer(Request $request): ?array
    {
        /** @var Customer|null $customer */
        $customer = $request->user('customer');

        if (! $customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
        ];
    }

    /**
     * Data mode impersonation untuk banner storefront.
     *
     * @return array{active: bool, admin_name?: string|null, customer_name?: string|null, stop_url?: string|null}
     */
    private function impersonationData(Request $request): array
    {
        $impersonationSession = $request->session()->get('impersonation', []);

        if (! is_array($impersonationSession) || ! (bool) ($impersonationSession['is_active'] ?? false)) {
            return [
                'active' => false,
            ];
        }

        /** @var Customer|null $customer */
        $customer = $request->user('customer');

        return [
            'active' => true,
            'admin_name' => $impersonationSession['admin_name'] ?? null,
            'customer_name' => $customer?->name,
            'stop_url' => route('impersonation.stop'),
        ];
    }

    /**
     * Jumlah item wishlist customer yang sedang login.
     */
    private function wishlistCount(): int
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return 0;
        }

        return WishlistItem::query()
            ->whereHas('wishlist', fn ($q) => $q->where('customer_id', $customer->id))
            ->count();
    }

    /**
     * Data item wishlist customer yang sedang login untuk header slider.
     *
     * @return array<int, array{
     *     id: int,
     *     productId: int,
     *     name: string,
     *     sku: string,
     *     price: float,
     *     image: string|null,
     *     inStock: bool,
     *     slug: string,
     * }>
     */
    private function wishlistItemsData(): array
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return [];
        }

        /** @var Wishlist|null $wishlist */
        $wishlist = Wishlist::query()
            ->with(['items.product.primaryMedia'])
            ->where('customer_id', $customer->id)
            ->first();

        if (! $wishlist) {
            return [];
        }

        return $wishlist->items
            ->map(function (WishlistItem $item) {
                $product = $item->product;
                $primaryMedia = $product?->primaryMedia->first();

                return [
                    'id'        => $item->id,
                    'productId' => $item->product_id,
                    'name'      => $item->product_name,
                    'sku'       => $item->product_sku,
                    'price'     => (float) ($product?->base_price ?? 0),
                    'image'     => $primaryMedia?->url
                        ? asset('storage/'.$primaryMedia->url)
                        : null,
                    'inStock'   => ($product?->stock ?? 0) > 0,
                    'slug'      => $product?->slug ?? '',
                ];
            })
            ->toArray();
    }

    /**
     * Data item keranjang customer yang sedang login untuk header slider.
     *
     * @return array<int, array{
     *     id: int,
     *     productId: int,
     *     name: string,
     *     sku: string,
     *     variant: string|null,
     *     price: float,
     *     qty: int,
     *     rowTotal: float,
     *     image: string|null,
     *     inStock: bool,
     * }>
     */
    private function cartItemsData(): array
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return [];
        }

        /** @var Cart|null $cart */
        $cart = Cart::query()
            ->with(['items.product.primaryMedia'])
            ->where('customer_id', $customer->id)
            ->first();

        if (! $cart) {
            return [];
        }

        return $cart->items
            ->map(function (CartItem $item) {
                $primaryMedia = $item->product?->primaryMedia->first();
                $meta = $item->meta_json ?? [];

                return [
                    'id'        => $item->id,
                    'productId' => $item->product_id,
                    'name'      => $item->product_name,
                    'sku'       => $item->product_sku,
                    'variant'   => $meta['variant'] ?? null,
                    'price'     => (float) $item->unit_price,
                    'qty'       => $item->qty,
                    'rowTotal'  => (float) $item->row_total,
                    'image'     => $primaryMedia?->url
                        ? asset('storage/'.$primaryMedia->url)
                        : null,
                    'inStock'   => ($item->product?->stock ?? 0) > 0,
                ];
            })
            ->toArray();
    }

    /**
     * Jumlah item di keranjang customer yang sedang login.
     */
    private function cartCount(): int
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return 0;
        }

        return $customer->cart?->items()->count() ?? 0;
    }

    /**
     * Data yang ditampilkan di footer, di-cache selama 1 jam.
     *
     * @return array{
     *     pages: \Illuminate\Support\Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     supportPages: \Illuminate\Support\Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     companyPages: \Illuminate\Support\Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     headerTopBarPages: \Illuminate\Support\Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     headerNavbarPages: \Illuminate\Support\Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     headerBottomBarPages: \Illuminate\Support\Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     bottomMainPages: \Illuminate\Support\Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     paymentMethods: \Illuminate\Support\Collection,
     *     categories: \Illuminate\Support\Collection,
     *     socialLinks: array<string, string>,
     *     store: array<string, string|null>,
     * }
     */
    private function footerData(): array
    {
        return Cache::remember('footer_data_v2', 3600, function () {
            /** @var PageRepositoryInterface $pageRepository */
            $pageRepository = app(PageRepositoryInterface::class);
            $settings = Setting::query()
                ->whereIn('key', [
                    'social.instagram',
                    'social.youtube',
                    'social.tiktok',
                    'social.whatsapp',
                    'social.x',
                    'social.facebook',
                    'store.name',
                    'store.description',
                    'store.email',
                    'store.phone',
                    'branding.tagline',
                ])
                ->pluck('value', 'key');

            $socialLinks = $settings
                ->filter(fn($value, $key) => str_starts_with($key, 'social.') && filled($value))
                ->mapWithKeys(fn($value, $key) => [str_replace('social.', '', $key) => $value])
                ->toArray();

            $pages = $pageRepository->getPublishedNavigationPages();
            $supportTemplates = ['faq', 'contact'];
            $footerPages = $pages
                ->filter(function (array $page): bool {
                    return ($page['show_on'] ?? null) === 'footer_main';
                })
                ->values();

            $supportPages = $footerPages
                ->filter(function (array $page) use ($supportTemplates): bool {
                    $template = strtolower((string) ($page['template'] ?? ''));

                    return in_array($template, $supportTemplates, true);
                })
                ->values();

            $companyPages = $footerPages
                ->reject(function (array $page) use ($supportTemplates): bool {
                    $template = strtolower((string) ($page['template'] ?? ''));

                    return in_array($template, $supportTemplates, true);
                })
                ->values();

            return [
                'pages' => $footerPages,
                'supportPages' => $supportPages,
                'companyPages' => $companyPages,
                'headerTopBarPages' => $pages
                    ->filter(fn (array $page): bool => ($page['show_on'] ?? null) === 'header_top_bar')
                    ->values(),
                'headerNavbarPages' => $pages
                    ->filter(fn (array $page): bool => ($page['show_on'] ?? null) === 'header_navbar')
                    ->values(),
                'headerBottomBarPages' => $pages
                    ->filter(fn (array $page): bool => ($page['show_on'] ?? null) === 'header_bottombar')
                    ->values(),
                'bottomMainPages' => $pages
                    ->filter(fn (array $page): bool => in_array(($page['show_on'] ?? null), ['bottom_main', null], true))
                    ->values(),
                'paymentMethods' => PaymentMethod::query()
                    ->where('is_active', true)
                    ->get(['code', 'name']),
                'categories' => Category::query()
                    ->where('is_active', true)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get(['slug', 'name']),
                'socialLinks' => $socialLinks,
                'store' => [
                    'name' => $settings->get('store.name'),
                    'description' => $settings->get('store.description'),
                    'email' => $settings->get('store.email'),
                    'phone' => $settings->get('store.phone'),
                    'tagline' => $settings->get('branding.tagline'),
                ],
            ];
        });
    }

    /**
     * Kategori produk aktif (level 1), di-cache selama 1 jam.
     *
     * @return \Illuminate\Support\Collection
     */
    private function categoriesData()
    {
        return Cache::remember('shared_categories', 3600, function () {
            return Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get(['id', 'slug', 'name', 'description', 'image'])
                ->map(fn(Category $cat) => [
                    'id' => $cat->id,
                    'slug' => $cat->slug,
                    'name' => $cat->name,
                    'description' => $cat->description,
                    'image' => $cat->image ? asset('storage/' . $cat->image) : null,
                    'productCount' => $cat->products()->count(),
                ]);
        });
    }
}
