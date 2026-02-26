<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('shipping_targets')) {
            return;
        }

        Schema::table('shipping_targets', function (Blueprint $table) {
            if (! Schema::hasColumn('shipping_targets', 'province_id')) {
                $table->unsignedBigInteger('province_id')->nullable()->after('country')->index();
            }

            if (! Schema::hasColumn('shipping_targets', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable()->after('province')->index();
            }
        });

        $this->backfillRegionIds();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('shipping_targets')) {
            return;
        }

        Schema::table('shipping_targets', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_targets', 'city_id')) {
                $table->dropColumn('city_id');
            }

            if (Schema::hasColumn('shipping_targets', 'province_id')) {
                $table->dropColumn('province_id');
            }
        });
    }

    private function backfillRegionIds(): void
    {
        if (! DB::table('shipping_targets')->whereNull('province_id')->orWhereNull('city_id')->exists()) {
            return;
        }

        $existingProvinceRows = DB::table('shipping_targets')
            ->selectRaw('province, MIN(province_id) as province_id')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->whereNotNull('province_id')
            ->groupBy('province')
            ->get();

        /** @var array<string, int> $provinceIdByName */
        $provinceIdByName = [];

        foreach ($existingProvinceRows as $row) {
            $provinceIdByName[(string) $row->province] = (int) $row->province_id;
        }

        $nextProvinceId = (int) (DB::table('shipping_targets')->max('province_id') ?? 0) + 1;

        $provinceValues = DB::table('shipping_targets')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->groupBy('province')
            ->orderByRaw('MIN(id)')
            ->pluck('province');

        foreach ($provinceValues as $province) {
            $provinceName = (string) $province;

            if ($provinceName === '') {
                continue;
            }

            if (! array_key_exists($provinceName, $provinceIdByName)) {
                $provinceIdByName[$provinceName] = $nextProvinceId;
                $nextProvinceId++;
            }

            DB::table('shipping_targets')
                ->where('province', $provinceName)
                ->whereNull('province_id')
                ->update(['province_id' => $provinceIdByName[$provinceName]]);
        }

        $existingCityRows = DB::table('shipping_targets')
            ->selectRaw('province, city, MIN(city_id) as city_id')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->whereNotNull('city_id')
            ->groupBy('province', 'city')
            ->get();

        /** @var array<string, int> $cityIdByProvinceAndName */
        $cityIdByProvinceAndName = [];

        foreach ($existingCityRows as $row) {
            $cityKey = (string) $row->province . '|' . (string) $row->city;
            $cityIdByProvinceAndName[$cityKey] = (int) $row->city_id;
        }

        $nextCityId = (int) (DB::table('shipping_targets')->max('city_id') ?? 0) + 1;

        $cityRows = DB::table('shipping_targets')
            ->select('province', 'city')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('province', 'city')
            ->orderByRaw('MIN(id)')
            ->get();

        foreach ($cityRows as $cityRow) {
            $provinceName = (string) $cityRow->province;
            $cityName     = (string) $cityRow->city;

            if ($provinceName === '' || $cityName === '') {
                continue;
            }

            $cityKey = $provinceName . '|' . $cityName;

            if (! array_key_exists($cityKey, $cityIdByProvinceAndName)) {
                $cityIdByProvinceAndName[$cityKey] = $nextCityId;
                $nextCityId++;
            }

            $provinceId = $provinceIdByName[$provinceName] ?? null;

            if ($provinceId === null) {
                continue;
            }

            DB::table('shipping_targets')
                ->where('province', $provinceName)
                ->where('city', $cityName)
                ->update([
                    'province_id' => DB::raw("COALESCE(province_id, {$provinceId})"),
                    'city_id'     => DB::raw("COALESCE(city_id, {$cityIdByProvinceAndName[$cityKey]})"),
                ]);
        }
    }
};
