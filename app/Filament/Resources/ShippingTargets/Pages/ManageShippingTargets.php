<?php

namespace App\Filament\Resources\ShippingTargets\Pages;

use App\Filament\Resources\ShippingTargets\ShippingTargetResource;
use App\Filament\Resources\ShippingTargets\Exports\ShippingTargetExporter;
use App\Models\ShippingTarget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Throwable;

class ManageShippingTargets extends ManageRecords
{
    protected static string $resource = ShippingTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('importSpreadsheet')
                ->label('Import CSV/XLSX')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Spreadsheet')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/x-csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->required()
                        ->maxSize(10240)
                        ->storeFiles(false)
                        ->helperText('Format yang didukung: CSV atau XLSX. Header wajib: three_lc_code, province, city, district, district_lion.'),
                ])
                ->action(function (array $data): void {
                    $file = $data['file'] ?? null;

                    if (! $file instanceof TemporaryUploadedFile) {
                        Notification::make()
                            ->title('File tidak valid')
                            ->body('Pilih file CSV atau XLSX yang valid.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        [$processedRows, $failedRows] = $this->importShippingTargetsFile(
                            $file->getRealPath(),
                            $file->getClientOriginalName(),
                        );

                        Notification::make()
                            ->title('Import selesai')
                            ->body("Berhasil memproses {$processedRows} baris. Gagal: {$failedRows} baris.")
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Import gagal')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            ExportAction::make()
                ->label('Export')
                ->exporter(ShippingTargetExporter::class),
        ];
    }

    /**
     * @return array{0:int,1:int}
     */
    private function importShippingTargetsFile(string $filePath, string $originalName): array
    {
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $reader = $this->makeReaderByExtension($extension);
        $reader->open($filePath);

        $processedRows = 0;
        $failedRows = 0;

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $headerMap = null;

                foreach ($sheet->getRowIterator() as $row) {
                    if ($row->isEmpty()) {
                        continue;
                    }

                    $rowValues = $row->toArray();

                    if ($headerMap === null) {
                        $headerMap = $this->buildHeaderMap($rowValues);
                        continue;
                    }

                    $threeLcCode = strtoupper(trim((string) ($rowValues[$headerMap['three_lc_code']] ?? '')));
                    $country = trim((string) ($rowValues[$headerMap['country'] ?? -1] ?? 'Indonesia'));
                    $province = trim((string) ($rowValues[$headerMap['province']] ?? ''));
                    $city = trim((string) ($rowValues[$headerMap['city']] ?? ''));
                    $district = trim((string) ($rowValues[$headerMap['district']] ?? ''));
                    $districtLion = trim((string) ($rowValues[$headerMap['district_lion']] ?? ''));
                    $provinceId = $this->toNullableInteger(
                        $headerMap['province_id'] !== null ? ($rowValues[$headerMap['province_id']] ?? null) : null
                    );
                    $cityId = $this->toNullableInteger(
                        $headerMap['city_id'] !== null ? ($rowValues[$headerMap['city_id']] ?? null) : null
                    );

                    if ($threeLcCode === '' || $province === '' || $city === '' || $district === '' || $districtLion === '') {
                        $failedRows++;

                        continue;
                    }

                    ShippingTarget::query()->updateOrCreate(
                        ['three_lc_code' => $threeLcCode],
                        [
                            'country' => $country !== '' ? $country : 'Indonesia',
                            'province_id' => $provinceId,
                            'province' => $province,
                            'city_id' => $cityId,
                            'city' => $city,
                            'district' => $district,
                            'district_lion' => $districtLion,
                        ]
                    );

                    $processedRows++;
                }
            }
        } finally {
            $reader->close();
        }

        return [$processedRows, $failedRows];
    }

    private function makeReaderByExtension(string $extension): CsvReader|XlsxReader
    {
        return match ($extension) {
            'csv' => new CsvReader(),
            'xlsx' => new XlsxReader(),
            default => throw new \RuntimeException('Format file tidak didukung. Gunakan CSV atau XLSX.'),
        };
    }

    /**
     * @param  array<int, mixed>  $headerRow
     * @return array{
     *     three_lc_code:int,
     *     country:int|null,
     *     province_id:int|null,
     *     province:int,
     *     city_id:int|null,
     *     city:int,
     *     district:int,
     *     district_lion:int
     * }
     */
    private function buildHeaderMap(array $headerRow): array
    {
        $normalizedHeaders = [];

        foreach ($headerRow as $index => $value) {
            $normalizedHeaders[$index] = $this->normalizeHeader($value);
        }

        $map = [
            'three_lc_code' => null,
            'country' => null,
            'province_id' => null,
            'province' => null,
            'city_id' => null,
            'city' => null,
            'district' => null,
            'district_lion' => null,
        ];

        foreach ($normalizedHeaders as $index => $header) {
            if (in_array($header, ['three_lc_code', 'three_lc', 'lc_code', '3lc_code', 'code', 'kode'], true)) {
                $map['three_lc_code'] = $index;
                continue;
            }

            if (in_array($header, ['country', 'negara'], true)) {
                $map['country'] = $index;
                continue;
            }

            if (in_array($header, ['province_id', 'provinsi_id', 'id_province', 'id_provinsi'], true)) {
                $map['province_id'] = $index;
                continue;
            }

            if (in_array($header, ['province', 'provinsi'], true)) {
                $map['province'] = $index;
                continue;
            }

            if (in_array($header, ['city_id', 'kota_id', 'id_city', 'id_kota'], true)) {
                $map['city_id'] = $index;
                continue;
            }

            if (in_array($header, ['city', 'kota', 'kabupaten', 'kota_kabupaten'], true)) {
                $map['city'] = $index;
                continue;
            }

            if (in_array($header, ['district', 'kecamatan'], true)) {
                $map['district'] = $index;
                continue;
            }

            if (in_array($header, ['district_lion', 'kecamatan_lion', 'district_lionparcel'], true)) {
                $map['district_lion'] = $index;
            }
        }

        if (
            $map['three_lc_code'] === null ||
            $map['province'] === null ||
            $map['city'] === null ||
            $map['district'] === null ||
            $map['district_lion'] === null
        ) {
            throw new \RuntimeException('Header wajib tidak lengkap. Wajib ada: three_lc_code, province, city, district, district_lion.');
        }

        return $map;
    }

    private function normalizeHeader(mixed $value): string
    {
        $header = strtolower(trim((string) $value));
        $header = str_replace([' ', '-'], '_', $header);

        return preg_replace('/[^a-z0-9_]/', '', $header) ?: '';
    }

    private function toNullableInteger(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        return $intValue > 0 ? $intValue : null;
    }
}
