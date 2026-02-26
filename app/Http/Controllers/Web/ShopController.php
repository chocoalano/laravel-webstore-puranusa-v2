<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\WishlistItem;
use App\Services\Products\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index(Request $request, ProductService $productService)
    {
        $data = $productService->getShopData($request->all());

        if ($request->wantsJson()) {
            return response()->json($data['products']);
        }

        return Inertia::render('Shop/Index', $data);
    }

    public function show(Request $request, string $slug, ProductService $productService)
    {
        $data = $productService->getProductShowData($slug);
        $data['slug'] = $slug;
        $data['isInWishlist'] = $this->checkIsInWishlist($request, $data['product']['id'] ?? null);

        return Inertia::render('Shop/Show', $data);
    }

    /**
     * Cek apakah produk sudah ada di wishlist customer yang sedang login.
     */
    private function checkIsInWishlist(Request $request, int|string|null $productId): bool
    {
        if (! $productId) {
            return false;
        }

        /** @var Customer|null $customer */
        $customer = $request->user('customer');

        if (! $customer) {
            return false;
        }

        return WishlistItem::query()
            ->whereHas('wishlist', fn ($q) => $q->where('customer_id', $customer->id))
            ->where('product_id', $productId)
            ->exists();
    }
}
