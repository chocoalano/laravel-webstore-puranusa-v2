<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Services\Home\HomeService;
use Mockery\MockInterface;

beforeEach(function (): void {
    $this->withoutMiddleware(HandleInertiaRequests::class);

    $this->mock(HomeService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getIndexPageData')
            ->once()
            ->andReturn([]);
    });
});

it('stores referral code from home query into session', function (): void {
    $this->get('/?referral_code=REF-MLM-001')
        ->assertSuccessful()
        ->assertSessionHas('referral_code', 'REF-MLM-001');
});

it('keeps existing referral code session when query is missing', function (): void {
    $this->withSession([
        'referral_code' => 'REF-SESSION-EXISTING',
    ])->get('/')
        ->assertSuccessful()
        ->assertSessionHas('referral_code', 'REF-SESSION-EXISTING');
});
