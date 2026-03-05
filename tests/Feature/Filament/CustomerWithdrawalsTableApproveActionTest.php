<?php

use App\Filament\Resources\CustomerWithdrawals\Tables\CustomerWithdrawalsTable;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customer_wallet_transactions');
    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('password')->nullable();
        $table->decimal('ewallet_saldo', 16, 2)->default(0);
        $table->timestamps();
    });

    Schema::create('customer_wallet_transactions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id');
        $table->string('type')->nullable();
        $table->decimal('amount', 16, 2)->default(0);
        $table->decimal('balance_before', 16, 2)->default(0);
        $table->decimal('balance_after', 16, 2)->default(0);
        $table->string('status')->nullable();
        $table->string('payment_method')->nullable();
        $table->string('transaction_ref')->nullable();
        $table->string('midtrans_transaction_id')->nullable();
        $table->text('notes')->nullable();
        $table->dateTime('completed_at')->nullable();
        $table->boolean('is_system')->default(false);
        $table->string('midtrans_signature_key')->nullable();
        $table->timestamps();
    });
});

it('approves pending withdrawal and deducts admin fee when gross deduction already exists', function (): void {
    $customer = createCustomerWithBalance(7001, 3076250);
    $transaction = createPendingWithdrawal(
        transactionId: 9001,
        customerId: (int) $customer->id,
        amount: 17000,
        balanceBefore: 3093250,
        balanceAfter: 3076250,
    );

    $approvedTransactionId = invokePrivateStatic(CustomerWithdrawalsTable::class, 'approvePendingWithdrawal', [(int) $transaction->id]);

    $freshCustomer = Customer::query()->findOrFail($customer->id);
    $freshTransaction = CustomerWalletTransaction::query()->findOrFail($transaction->id);

    expect($approvedTransactionId)->toBe((int) $transaction->id)
        ->and((float) $freshCustomer->ewallet_saldo)->toBe(3069750.0)
        ->and((string) $freshTransaction->status)->toBe('completed')
        ->and($freshTransaction->completed_at)->not->toBeNull()
        ->and((float) $freshTransaction->balance_after)->toBe(3069750.0)
        ->and((string) ($freshTransaction->notes ?? ''))->toContain('Biaya admin approval: Rp 6.500')
        ->not->toContain('Potongan gross saat approve');
});

it('approves pending withdrawal and deducts missing gross plus admin fee when gross not deducted yet', function (): void {
    $customer = createCustomerWithBalance(7002, 3093250);
    $transaction = createPendingWithdrawal(
        transactionId: 9002,
        customerId: (int) $customer->id,
        amount: 17000,
        balanceBefore: 3093250,
        balanceAfter: 3093250,
    );

    invokePrivateStatic(CustomerWithdrawalsTable::class, 'approvePendingWithdrawal', [(int) $transaction->id]);

    $freshCustomer = Customer::query()->findOrFail($customer->id);
    $freshTransaction = CustomerWalletTransaction::query()->findOrFail($transaction->id);

    expect((float) $freshCustomer->ewallet_saldo)->toBe(3069750.0)
        ->and((string) $freshTransaction->status)->toBe('completed')
        ->and((float) $freshTransaction->balance_after)->toBe(3069750.0)
        ->and((string) ($freshTransaction->notes ?? ''))->toContain('Biaya admin approval: Rp 6.500')
        ->toContain('Potongan gross saat approve: Rp 17.000');
});

function createCustomerWithBalance(int $id, float $balance): Customer
{
    DB::table('customers')->insert([
        'id' => $id,
        'name' => 'Customer '.$id,
        'email' => "customer{$id}@example.test",
        'phone' => '081234567890',
        'password' => bcrypt('secret123'),
        'ewallet_saldo' => $balance,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Customer::query()->findOrFail($id);
}

function createPendingWithdrawal(
    int $transactionId,
    int $customerId,
    float $amount,
    float $balanceBefore,
    float $balanceAfter,
): CustomerWalletTransaction {
    DB::table('customer_wallet_transactions')->insert([
        'id' => $transactionId,
        'customer_id' => $customerId,
        'type' => 'withdrawal',
        'amount' => $amount,
        'balance_before' => $balanceBefore,
        'balance_after' => $balanceAfter,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
        'transaction_ref' => 'WD-'.$transactionId,
        'midtrans_transaction_id' => null,
        'notes' => 'Bank: BCA (1234567890)',
        'completed_at' => null,
        'is_system' => false,
        'midtrans_signature_key' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return CustomerWalletTransaction::query()->findOrFail($transactionId);
}

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
