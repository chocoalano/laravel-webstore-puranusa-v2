<?php

use App\Models\Customer;
use App\Services\Auth\CustomerAuthService;
use App\Services\Auth\CustomerProfileService;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

it('registers customer auth api routes', function (): void {
    expect(route('api.auth.login', [], false))->toBe('/api/auth/login')
        ->and(route('api.auth.logout', [], false))->toBe('/api/auth/logout')
        ->and(route('api.auth.me', [], false))->toBe('/api/auth/me');
});

it('returns access token payload when customer api login succeeds', function (): void {
    $customer = makeCustomerForApi(501);

    $this->mock(CustomerAuthService::class, function (MockInterface $mock) use ($customer): void {
        $mock->shouldReceive('attemptApiLogin')
            ->once()
            ->with('member501', 'secret123', 'android-app')
            ->andReturn([
                'customer' => $customer,
                'access_token' => '1|test-token',
                'token_type' => 'Bearer',
            ]);
    });

    $this->postJson(route('api.auth.login'), [
        'username' => 'member501',
        'password' => 'secret123',
        'device_name' => 'android-app',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Login API berhasil.')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.access_token', '1|test-token')
        ->assertJsonPath('data.customer.id', 501)
        ->assertJsonPath('data.customer.username', 'member501');
});

it('returns error response when customer api credentials are invalid', function (): void {
    $this->mock(CustomerAuthService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('attemptApiLogin')
            ->once()
            ->with('member501', 'wrong-password', null)
            ->andReturnNull();
    });

    $this->postJson(route('api.auth.login'), [
        'username' => 'member501',
        'password' => 'wrong-password',
    ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Username atau kata sandi salah.')
        ->assertJsonPath('errors.username.0', 'Username atau kata sandi salah.');
});

it('validates required payload for customer api login', function (): void {
    $this->postJson(route('api.auth.login'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'password']);
});

it('returns authenticated customer profile from me endpoint', function (): void {
    $customer = makeCustomerForApi(601);

    $this->mock(CustomerProfileService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getApiProfile')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer): bool {
                return (int) $authenticatedCustomer->id === 601;
            })
            ->andReturn([
                'id' => 601,
                'name' => 'Customer 601',
                'username' => 'member601',
                'email' => 'member601@example.test',
                'phone' => '08123456789',
                'status' => 3,
                'member_package' => 'Gold',
                'summary' => [
                    'total_bonus' => 0,
                    'network_count' => 0,
                    'sponsor_count' => 0,
                ],
                'orders' => [
                    'total' => 0,
                    'processing' => 0,
                    'completed' => 0,
                ],
                'mitra' => [
                    'prospek' => 0,
                    'aktif' => 0,
                    'pasif' => 0,
                ],
                'network_binary' => [
                    'bonus' => 0,
                    'sponsor' => 0,
                    'matching' => 0,
                    'pairing' => 0,
                    'cashback' => 0,
                    'rewards' => 0,
                    'retail' => 0,
                    'lifetime_cash' => 0,
                ],
                'promo' => [
                    'active_count' => 0,
                ],
                'wallet' => [
                    'balance' => 0,
                    'reward_points' => 0,
                    'active' => true,
                ],
            ]);
    });

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.auth.me'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Profile loaded')
        ->assertJsonPath('data.id', 601)
        ->assertJsonPath('data.username', 'member601')
        ->assertJsonPath('data.email', 'member601@example.test')
        ->assertJsonPath('data.member_package', 'Gold')
        ->assertJsonPath('data.summary.total_bonus', 0)
        ->assertJsonPath('data.orders.processing', 0)
        ->assertJsonPath('data.wallet.active', true);
});

it('revokes customer api current token on logout', function (): void {
    $customer = makeCustomerForApi(701);

    Sanctum::actingAs($customer, ['customer:api']);

    $this->mock(CustomerAuthService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('logoutApi')
            ->once()
            ->withArgs(function (Customer $authenticatedCustomer): bool {
                return (int) $authenticatedCustomer->id === 701;
            });
    });

    $this->postJson(route('api.auth.logout'))
        ->assertOk()
        ->assertJsonPath('message', 'Logout API berhasil.');
});

function makeCustomerForApi(int $id): Customer
{
    $customer = new Customer;
    $customer->forceFill([
        'name' => 'Customer '.$id,
        'username' => 'member'.$id,
        'email' => 'member'.$id.'@example.test',
        'phone' => '08123456789',
        'password' => bcrypt('secret123'),
        'status' => 3,
    ]);
    $customer->setAttribute('id', $id);
    $customer->exists = true;

    return $customer;
}
