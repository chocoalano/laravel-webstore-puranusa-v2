<?php

use App\Services\Products\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery\MockInterface;

beforeEach(function (): void {
    config()->set('session.driver', 'array');
    config()->set('cache.default', 'array');
    $this->withoutMiddleware();
});

it('redirects to shop index when product slug is not found', function (): void {
    $this->mock(ProductService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getProductShowData')
            ->once()
            ->with('slug-tidak-ditemukan')
            ->andThrow(new ModelNotFoundException);
    });

    $this->get(route('shop.show', ['slug' => 'slug-tidak-ditemukan']))
        ->assertRedirect(route('shop.index'))
        ->assertSessionHas('error', 'Produk tidak ditemukan.');
});

it('redirects to shop index when product payload is invalid', function (): void {
    $this->mock(ProductService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getProductShowData')
            ->once()
            ->with('payload-kosong')
            ->andReturn([]);
    });

    $this->get(route('shop.show', ['slug' => 'payload-kosong']))
        ->assertRedirect(route('shop.index'))
        ->assertSessionHas('error', 'Produk tidak ditemukan.');
});
