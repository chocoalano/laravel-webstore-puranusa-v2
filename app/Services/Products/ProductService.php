<?php

namespace App\Services\Products;

use App\Repositories\Products\Contracts\ProductRepositoryInterface;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {
    }

    public function getShopData(array $filters = [])
    {
        return [
            'products' => $this->productRepository->getPaginated($filters),
            'categories' => $this->productRepository->getActiveCategories(),
            'brands' => $this->productRepository->getActiveBrands(),
            'filterStats' => $this->productRepository->getFilterStats(),
            'filters' => $filters,
        ];
    }

    public function getProductShowData(string $slug)
    {
        $product = $this->productRepository->getBySlugWithDetails($slug);
        $recommendations = $this->productRepository->getRecommendations($product);

        // Format product for Show.vue
        $formattedProduct = [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'brand' => $product->brand,
            'shortDescription' => $product->short_desc,
            'description' => $product->long_desc,
            'priceFrom' => $product->base_price,
            'rating' => $product->avg_rating ?? 0,
            'reviewsCount' => $product->reviews_count ?? 0,
            // Assuming no specific highlights field, could be extracted or left empty
            'highlights' => [],
            'specs' => [
                ['label' => 'Merek', 'value' => $product->brand ?? '-'],
                ['label' => 'Berat', 'value' => $product->weight_gram ? $product->weight_gram . 'g' : '-'],
                ['label' => 'Garansi', 'value' => $product->warranty_months ? $product->warranty_months . ' Bulan' : 'Tidak ada'],
                ['label' => 'Dimensi', 'value' => $product->length_mm && $product->width_mm && $product->height_mm ? "{$product->length_mm}x{$product->width_mm}x{$product->height_mm} mm" : '-']
            ],
            'media' => $product->media->map(fn($m) => [
                'url' => str_starts_with($m->url, 'http') ? $m->url : asset('storage/' . $m->url),
                'alt' => $m->alt_text ?? $product->name,
            ])->toArray(),
            'variants' => [
                [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => 'Default',
                    'price' => $product->base_price,
                    // 'compareAtPrice' can be base_price if there's a discount logic, keeping empty for now
                    'inStock' => $product->stock > 0,
                    'stock' => $product->stock,
                    'options' => [],
                    'media' => $product->primaryMedia->map(fn($m) => [
                        'url' => str_starts_with($m->url, 'http') ? $m->url : asset('storage/' . $m->url),
                        'alt' => $m->alt_text ?? $product->name,
                    ])->toArray()
                ]
            ]
        ];

        // Format reviews
        $formattedReviews = $product->reviews->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->customer->name ?? 'User',
            'rating' => $r->rating,
            'title' => $r->title,
            'body' => $r->comment,
            'date' => $r->created_at->format('Y-m-d'),
            'verified' => true // assuming verified for now
        ])->toArray();

        // Format recommendations
        $formattedRecommendations = $recommendations->map(fn($r) => [
            'id' => $r->id,
            'slug' => $r->slug,
            'name' => $r->name,
            'price' => $r->base_price,
            'image' => $r->primaryMedia->first()
                ? (str_starts_with($r->primaryMedia->first()->url, 'http') ? $r->primaryMedia->first()->url : asset('storage/' . $r->primaryMedia->first()->url))
                : null,
            'rating' => $r->avg_rating ?? 0,
            'reviewsCount' => $r->reviews_count ?? 0,
            'badge' => null
        ])->toArray();

        return [
            'product' => $formattedProduct,
            'reviews' => $formattedReviews,
            'recommendations' => $formattedRecommendations,
        ];
    }
}
