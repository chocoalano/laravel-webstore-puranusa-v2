<?php

namespace App\Filament\Resources\CommodityCodes\Pages;

use App\Filament\Resources\CommodityCodes\CommodityCodeResource;
use App\Filament\Resources\CommodityCodes\Exports\CommodityCodeExporter;
use App\Models\CommodityCode;
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

class ManageCommodityCodes extends ManageRecords
{
    protected static string $resource = CommodityCodeResource::class;

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
                        ->helperText('Format yang didukung: CSV atau XLSX. Header: code, name, dangerous_good, is_quarantine.'),
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
                        [$processedRows, $failedRows] = $this->importCommodityCodesFile(
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
                ->exporter(CommodityCodeExporter::class),
        ];
    }

    /**
     * @return array{0:int,1:int}
     */
    private function importCommodityCodesFile(string $filePath, string $originalName): array
    {
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $reader = $this->makeReaderByExtension($extension);
        $reader->open($filePath);

        $processedRows = 0;
        $failedRows = 0;
        $headerMap = null;

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($row->isEmpty()) {
                        continue;
                    }

                    $rowValues = $row->toArray();

                    if ($headerMap === null) {
                        $headerMap = $this->buildHeaderMap($rowValues);
                        continue;
                    }

                    $codeIndex = $headerMap['code'];
                    $nameIndex = $headerMap['name'];
                    $dangerousGoodIndex = $headerMap['dangerous_good'] ?? null;
                    $isQuarantineIndex = $headerMap['is_quarantine'] ?? null;

                    $code = trim((string) ($rowValues[$codeIndex] ?? ''));
                    $name = trim((string) ($rowValues[$nameIndex] ?? ''));

                    if ($code === '' || $name === '') {
                        $failedRows++;

                        continue;
                    }

                    $dangerousGood = $this->toBoolean(
                        $dangerousGoodIndex !== null ? ($rowValues[$dangerousGoodIndex] ?? null) : null,
                        true
                    );
                    $isQuarantine = $this->toBoolean(
                        $isQuarantineIndex !== null ? ($rowValues[$isQuarantineIndex] ?? null) : null,
                        true
                    );

                    CommodityCode::query()->updateOrCreate(
                        ['code' => $code],
                        [
                            'name' => $name,
                            'dangerous_good' => $dangerousGood,
                            'is_quarantine' => $isQuarantine,
                        ]
                    );

                    $processedRows++;
                }

                break;
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
     * @return array{code:int,name:int,dangerous_good?:int,is_quarantine?:int}
     */
    private function buildHeaderMap(array $headerRow): array
    {
        $normalizedHeaders = [];

        foreach ($headerRow as $index => $value) {
            $normalizedHeaders[$index] = $this->normalizeHeader($value);
        }

        $map = [];

        foreach ($normalizedHeaders as $index => $header) {
            if (in_array($header, ['code', 'kode', 'commodity_code', 'kode_komoditas'], true)) {
                $map['code'] = $index;
                continue;
            }

            if (in_array($header, ['name', 'nama', 'commodity_name', 'nama_komoditas'], true)) {
                $map['name'] = $index;
                continue;
            }

            if (in_array($header, ['dangerous_good', 'barang_berbahaya', 'dangerous'], true)) {
                $map['dangerous_good'] = $index;
                continue;
            }

            if (in_array($header, ['is_quarantine', 'quarantine', 'wajib_karantina', 'karantina'], true)) {
                $map['is_quarantine'] = $index;
            }
        }

        if (! array_key_exists('code', $map) || ! array_key_exists('name', $map)) {
            throw new \RuntimeException('Header wajib tidak ditemukan. Pastikan ada kolom code dan name.');
        }

        return $map;
    }

    private function normalizeHeader(mixed $value): string
    {
        $header = strtolower(trim((string) $value));
        $header = str_replace([' ', '-'], '_', $header);

        return preg_replace('/[^a-z0-9_]/', '', $header) ?: '';
    }

    private function toBoolean(mixed $value, bool $default = false): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'ya', 'y'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'tidak', 'n'], true)) {
            return false;
        }

        return $default;
    }
}
