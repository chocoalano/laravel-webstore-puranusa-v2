<?php

namespace App\Repositories\Products\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Get paginated products with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    /**
     * Get all active categories.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveCategories();

    /**
     * Get all active brands.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveBrands();

    /**
     * Get statistics for filters (min/max price, etc.)
     *
     * @return array
     */
    public function getFilterStats();

    /**
     * Get a product by slug with its details (media, reviews, categories, etc.)
     *
     * @param string $slug
     * @return \App\Models\Product
     */
    public function getBySlugWithDetails(string $slug);

    /**
     * Get related/recommended products.
     *
     * @param \App\Models\Product $product
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecommendations(\App\Models\Product $product, int $limit = 4);
}
