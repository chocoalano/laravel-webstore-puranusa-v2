<?php

use App\Models\Customer;
use App\Services\Auth\CustomerAuthService;
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

    Sanctum::actingAs($customer, ['customer:api']);

    $this->getJson(route('api.auth.me'))
        ->assertOk()
        ->assertJsonPath('id', 601)
        ->assertJsonPath('username', 'member601')
        ->assertJsonPath('email', 'member601@example.test');
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
