<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        if ($record instanceof Customer) {
            self::runRegistrationProcedureIfNeeded($record);
        }

        return $record;
    }

    private static function runRegistrationProcedureIfNeeded(Customer $customer): void
    {
        if (! self::supportsRegistrationProcedure()) {
            return;
        }

        if (! self::shouldExecuteRegistrationProcedure($customer)) {
            return;
        }

        self::callRegistrationProcedure((int) $customer->id);
    }

    private static function supportsRegistrationProcedure(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }

    private static function shouldExecuteRegistrationProcedure(Customer $customer): bool
    {
        $customerId = (int) $customer->id;

        if ($customerId <= 0) {
            return false;
        }

        if ((int) ($customer->upline_id ?? 0) <= 0 || blank($customer->position)) {
            return false;
        }

        if (! Schema::hasTable('customer_networks') || ! Schema::hasTable('customer_network_matrixes')) {
            return true;
        }

        return ! DB::table('customer_networks')->where('member_id', $customerId)->exists()
            && ! DB::table('customer_network_matrixes')->where('member_id', $customerId)->exists();
    }

    private static function callRegistrationProcedure(int $customerId): void
    {
        $statement = DB::connection()->getPdo()->prepare('CALL sp_registration(?)');
        $statement->execute([$customerId]);

        while ($statement->nextRowset()) {
        }
    }
}
