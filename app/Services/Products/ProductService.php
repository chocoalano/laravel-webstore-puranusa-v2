<?php

namespace App\Services\Products;

use App\Models\ProductReview;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Support\Media\PublicMediaUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /** @return array<string, mixed> */
    public function getShopData(array $filters = []): array
    {
        return [
            'products' => $this->productRepository->getPaginated($filters),
            'categories' => $this->productRepository->getActiveCategories(),
            'brands' => $this->productRepository->getActiveBrands(),
            'filterStats' => $this->productRepository->getFilterStats(),
            'filters' => $filters,
        ];
    }

    /**
     * @return array{
     *   product: array<string, mixed>,
     *   reviews: array<int, array<string, mixed>>,
     *   recommendations: array<int, array<string, mixed>>
     * }
     *
     * @throws ModelNotFoundException
     */
    public function getProductShowData(string $slug, bool $includeReviews = true): array
    {
        $product = $this->productRepository->getBySlugWithDetails($slug);
        $recommendations = $this->productRepository->getRecommendations($product);
        $reviews = $includeReviews
            ? $this->getApprovedReviewsForInfiniteScroll((int) $product->id, 10)->items()
            : [];

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
                ['label' => 'Berat', 'value' => $product->weight_gram ? $product->weight_gram.'g' : '-'],
                ['label' => 'Garansi', 'value' => $product->warranty_months ? $product->warranty_months.' Bulan' : 'Tidak ada'],
                ['label' => 'Dimensi', 'value' => $product->length_mm && $product->width_mm && $product->height_mm ? "{$product->length_mm}x{$product->width_mm}x{$product->height_mm} mm" : '-'],
            ],
            'media' => $product->media->map(fn ($m) => [
                'url' => PublicMediaUrl::resolve($m->url),
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
                    'media' => $product->primaryMedia->map(fn ($m) => [
                        'url' => PublicMediaUrl::resolve($m->url),
                        'alt' => $m->alt_text ?? $product->name,
                    ])->toArray(),
                ],
            ],
        ];

        // Format recommendations
        $formattedRecommendations = $recommendations->map(fn ($r) => [
            'id' => $r->id,
            'slug' => $r->slug,
            'name' => $r->name,
            'price' => $r->base_price,
            'image' => $r->primaryMedia->first()
                ? PublicMediaUrl::resolve($r->primaryMedia->first()->url)
                : null,
            'rating' => $r->avg_rating ?? 0,
            'reviewsCount' => $r->reviews_count ?? 0,
            'badge' => null,
        ])->toArray();

        return [
            'product' => $formattedProduct,
            'reviews' => $reviews,
            'recommendations' => $formattedRecommendations,
        ];
    }

    public function getApprovedReviewsForInfiniteScroll(int $productId, int $perPage = 8): LengthAwarePaginator
    {
        return $this->productRepository
            ->getApprovedReviewsPaginated($productId, $perPage)
            ->through(fn (ProductReview $review): array => $this->formatReview($review));
    }

    /** @return array{id:int,name:string,rating:int,title:?string,body:string,date:string,verified:bool} */
    private function formatReview(ProductReview $review): array
    {
        return [
            'id' => (int) $review->id,
            'name' => trim((string) ($review->customer?->name ?? 'User')),
            'rating' => (int) $review->rating,
            'title' => $review->title,
            'body' => trim((string) ($review->comment ?? '')),
            'date' => $review->created_at?->toDateString() ?? '',
            'verified' => (bool) ($review->is_verified_purchase ?? false),
        ];
    }
}
