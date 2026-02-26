<?php

namespace App\Repositories\Shipping;

use App\Models\ShippingTarget;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;

class EloquentShippingTargetRepository implements ShippingTargetRepositoryInterface
{
    public function provinceOptions(): array
    {
        return ShippingTarget::query()
            ->selectRaw('MIN(province_id) as id, province as label')
            ->whereNotNull('province_id')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->groupBy('province')
            ->orderBy('label')
            ->get()
            ->map(fn (ShippingTarget $province): array => [
                'id'    => (int) $province->id,
                'label' => (string) $province->label,
            ])
            ->toArray();
    }

    public function cityOptions(): array
    {
        return ShippingTarget::query()
            ->selectRaw('MIN(city_id) as id, MIN(province_id) as province_id, city as label')
            ->whereNotNull('city_id')
            ->whereNotNull('province_id')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('province', 'city')
            ->orderBy('label')
            ->get()
            ->map(fn (ShippingTarget $city): array => [
                'id'          => (int) $city->id,
                'province_id' => (int) $city->province_id,
                'label'       => (string) $city->label,
            ])
            ->toArray();
    }

    public function districtOptions(): array
    {
        return ShippingTarget::query()
            ->selectRaw('MIN(province_id) as province_id, MIN(city_id) as city_id, district as label, MIN(district_lion) as district_lion')
            ->whereNotNull('province_id')
            ->whereNotNull('city_id')
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->whereNotNull('district_lion')
            ->where('district_lion', '!=', '')
            ->groupBy('province', 'city', 'district')
            ->orderBy('label')
            ->get()
            ->map(fn (ShippingTarget $district): array => [
                'province_id'   => (int) $district->province_id,
                'city_id'       => (int) $district->city_id,
                'label'         => (string) $district->label,
                'district_lion' => (string) $district->district_lion,
            ])
            ->toArray();
    }

    public function provinces(): array
    {
        return ShippingTarget::query()
            ->distinct()
            ->orderBy('province')
            ->pluck('province')
            ->toArray();
    }

    public function citiesByProvince(string $province): array
    {
        return ShippingTarget::query()
            ->where('province', $province)
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->toArray();
    }

    public function districtsByProvinceAndCity(string $province, string $city): array
    {
        return ShippingTarget::query()
            ->where('province', $province)
            ->where('city', $city)
            ->distinct()
            ->orderBy('district')
            ->pluck('district')
            ->toArray();
    }

    public function findDistrictLion(string $province, string $city, ?string $district = null): ?string
    {
        $query = ShippingTarget::query()
            ->where('province', $province)
            ->where('city', $city)
            ->whereNotNull('district_lion');

        $normalizedDistrict = $district !== null ? trim($district) : null;

        if ($normalizedDistrict !== null && $normalizedDistrict !== '') {
            $query->where('district', $normalizedDistrict);
        }

        return $query->value('district_lion');
    }

    public function findCityByIds(int $provinceId, int $cityId): ?array
    {
        if ($provinceId < 1 || $cityId < 1) {
            return null;
        }

        $city = ShippingTarget::query()
            ->selectRaw('MIN(province_id) as province_id, MIN(city_id) as city_id, province as province_label, city as city_label')
            ->where('province_id', $provinceId)
            ->where('city_id', $cityId)
            ->whereNotNull('province')
            ->whereNotNull('city')
            ->groupBy('province', 'city')
            ->first();

        if (! $city) {
            return null;
        }

        return [
            'province_id'    => (int) $city->province_id,
            'province_label' => (string) $city->province_label,
            'city_id'        => (int) $city->city_id,
            'city_label'     => (string) $city->city_label,
        ];
    }

    public function findDistrictByRegionIds(int $provinceId, int $cityId, ?string $district = null): ?array
    {
        if ($provinceId < 1 || $cityId < 1) {
            return null;
        }

        $normalizedDistrict = $district !== null ? trim($district) : null;

        $query = ShippingTarget::query()
            ->selectRaw('district, district_lion')
            ->where('province_id', $provinceId)
            ->where('city_id', $cityId)
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->whereNotNull('district_lion')
            ->where('district_lion', '!=', '');

        if ($normalizedDistrict !== null && $normalizedDistrict !== '') {
            $query->where('district', $normalizedDistrict);
        }

        $districtRow = $query
            ->orderBy('district')
            ->first();

        if (! $districtRow) {
            return null;
        }

        return [
            'district'      => (string) $districtRow->district,
            'district_lion' => (string) $districtRow->district_lion,
        ];
    }
}
