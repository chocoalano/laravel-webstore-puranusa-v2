<?php

namespace App\Services\CustomerAddress;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerAddressService
{
    public function __construct(
        private readonly CustomerAddressRepositoryInterface $customerAddressRepository,
        private readonly ShippingTargetRepositoryInterface $shippingTargetRepository,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function create(Customer $customer, array $payload): CustomerAddress
    {
        $payload = $this->normalizeRegionPayload($payload);

        return DB::transaction(function () use ($customer, $payload): CustomerAddress {
            $isFirstAddress = $this->customerAddressRepository->countByCustomerId($customer->id) === 0;
            $shouldBeDefault = $isFirstAddress || (bool) ($payload['is_default'] ?? false);

            if ($shouldBeDefault) {
                $this->customerAddressRepository->clearDefaultAddress($customer->id);
            }

            $address = $this->customerAddressRepository->createForCustomer($customer->id, [
                ...$payload,
                'is_default' => $shouldBeDefault,
            ]);

            if (! $this->customerAddressRepository->hasDefaultAddress($customer->id)) {
                $this->customerAddressRepository->setDefault($address);
                $address->refresh();
            }

            return $address;
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Customer $customer, int $addressId, array $payload): CustomerAddress
    {
        $payload = $this->normalizeRegionPayload($payload);

        return DB::transaction(function () use ($customer, $addressId, $payload): CustomerAddress {
            $address = $this->resolveOwnedAddress($customer->id, $addressId);

            $requestedDefault = (bool) ($payload['is_default'] ?? false);

            if (! $requestedDefault && $address->is_default) {
                throw ValidationException::withMessages([
                    'is_default' => 'Alamat default tidak dapat dinonaktifkan. Pilih alamat lain sebagai default terlebih dahulu.',
                ]);
            }

            if ($requestedDefault) {
                $this->customerAddressRepository->clearDefaultAddress($customer->id);
            }

            $this->customerAddressRepository->update($address, [
                ...$payload,
                'is_default' => $requestedDefault,
            ]);

            $address->refresh();

            if (! $this->customerAddressRepository->hasDefaultAddress($customer->id)) {
                $this->customerAddressRepository->setDefault($address);
                $address->refresh();
            }

            return $address;
        });
    }

    public function setDefault(Customer $customer, int $addressId): void
    {
        DB::transaction(function () use ($customer, $addressId): void {
            $address = $this->resolveOwnedAddress($customer->id, $addressId);

            $this->customerAddressRepository->clearDefaultAddress($customer->id);
            $this->customerAddressRepository->setDefault($address);
        });
    }

    public function delete(Customer $customer, int $addressId): void
    {
        DB::transaction(function () use ($customer, $addressId): void {
            $address = $this->resolveOwnedAddress($customer->id, $addressId);

            if ($address->is_default) {
                throw ValidationException::withMessages([
                    'address' => 'Alamat default tidak dapat dihapus. Jadikan alamat lain sebagai default terlebih dahulu.',
                ]);
            }

            $this->customerAddressRepository->delete($address);

            if (! $this->customerAddressRepository->hasDefaultAddress($customer->id)) {
                $fallbackAddress = $this->customerAddressRepository->getLatestByCustomerId($customer->id);

                if ($fallbackAddress) {
                    $this->customerAddressRepository->setDefault($fallbackAddress);
                }
            }
        });
    }

    private function resolveOwnedAddress(int $customerId, int $addressId): CustomerAddress
    {
        $address = $this->customerAddressRepository->findByCustomerIdAndAddressId($customerId, $addressId);

        if (! $address) {
            throw (new ModelNotFoundException())->setModel(CustomerAddress::class, [$addressId]);
        }

        return $address;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizeRegionPayload(array $payload): array
    {
        $provinceId = (int) ($payload['province_id'] ?? 0);
        $cityId     = (int) ($payload['city_id'] ?? 0);
        $district   = isset($payload['district']) ? trim((string) $payload['district']) : null;

        $region = $this->shippingTargetRepository->findCityByIds($provinceId, $cityId);

        if (! $region) {
            throw ValidationException::withMessages([
                'city_id' => 'Kota/kabupaten tidak ditemukan pada data target pengiriman.',
            ]);
        }

        $districtRegion = $this->shippingTargetRepository->findDistrictByRegionIds($provinceId, $cityId, $district);

        if (! $districtRegion) {
            throw ValidationException::withMessages([
                'district' => 'Kecamatan tidak ditemukan pada data target pengiriman.',
            ]);
        }

        return [
            ...$payload,
            'province_id'    => $region['province_id'],
            'province_label' => $region['province_label'],
            'city_id'        => $region['city_id'],
            'city_label'     => $region['city_label'],
            'district'       => $districtRegion['district'],
            'district_lion'  => $districtRegion['district_lion'],
        ];
    }
}
