<?php

use App\Models\Customer;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    config()->set('app.timezone', 'Asia/Jakarta');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Carbon::setTestNow(CarbonImmutable::parse('2026-03-07 10:30:00', 'Asia/Jakarta'));

    Schema::dropIfExists('customers');
    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('ref_code')->nullable()->unique();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('ewallet_id')->nullable()->unique();
        $table->timestamps();
    });
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('generates ewallet id on customer create with register pattern', function (): void {
    $customer = Customer::query()->create([
        'name' => 'Pattern Customer',
        'email' => 'pattern.customer@example.test',
        'password' => 'secret123',
    ]);

    expect($customer->ewallet_id)
        ->not->toBeNull()
        ->toMatch('/^EW-\d{8}-[A-Z0-9]{5}$/')
        ->toStartWith('EW-20260307-');
});

it('keeps provided ewallet id when value is explicitly set', function (): void {
    $customer = Customer::query()->create([
        'name' => 'Manual Ewallet Customer',
        'email' => 'manual.ewallet@example.test',
        'password' => 'secret123',
        'ewallet_id' => 'EW-20260307-MANU1',
    ]);

    expect($customer->ewallet_id)->toBe('EW-20260307-MANU1');
});

it('generates referral code on customer create with register pattern', function (): void {
    $customer = Customer::query()->create([
        'name' => 'Pattern Referral Customer',
        'email' => 'pattern.referral@example.test',
        'password' => 'secret123',
    ]);

    expect($customer->ref_code)
        ->not->toBeNull()
        ->toMatch('/^[A-Z0-9]{8}$/');
});

it('keeps provided referral code when value is explicitly set', function (): void {
    $customer = Customer::query()->create([
        'name' => 'Manual Referral Customer',
        'email' => 'manual.referral@example.test',
        'password' => 'secret123',
        'ref_code' => 'ABCD1234',
    ]);

    expect($customer->ref_code)->toBe('ABCD1234');
});
