<?php

namespace App\Filament\Resources\CustomerBonusMatchings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerBonusMatchingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('CustomerBonusMatching')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'member:id,name,ref_code,email',
                'fromMember:id,name,ref_code,email',
            ]))
            ->columns([
                TextColumn::make('member.name')
                    ->label('Member Penerima')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fromMember.name')
                    ->label('Sumber Omzet')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level')
                    ->label('Level')
                    ->numeric()
                    ->badge()
                    ->sortable()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state <= 2 => 'success',
                        $state <= 4 => 'info',
                        default => 'warning',
                    }),

                TextColumn::make('amount')
                    ->label('Nominal Bonus')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Bonus')
                            ->money('IDR')
                    ),

                TextColumn::make('index_value')
                    ->label('Nilai Index')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->summarize(
                        Average::make()
                            ->label('Rata-rata Index')
                            ->numeric(decimalPlaces: 2)
                    ),

                TextColumn::make('status')
                    ->label('Status Bonus')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                    ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'warning'),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->placeholder('-')
                    ->limit(60)
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('member_id')
                    ->label('Member Penerima')
                    ->relationship('member', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua member'),

                SelectFilter::make('from_member_id')
                    ->label('Sumber Omzet')
                    ->relationship('fromMember', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua sumber'),

                SelectFilter::make('status')
                    ->label('Status Bonus')
                    ->options(self::statusOptions())
                    ->placeholder('Semua status'),

                Filter::make('level_range')
                    ->label('Rentang Level')
                    ->schema([
                        TextInput::make('min')
                            ->label('Min Level')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('1'),
                        TextInput::make('max')
                            ->label('Max Level')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('∞'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('level', '>=', $data['min']))
                        ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('level', '<=', $data['max']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['min'] ?? null)) {
                            $indicators[] = Indicator::make('Level dari ' . $data['min'])->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('Level sampai ' . $data['max'])->removeField('max');
                        }

                        return $indicators;
                    }),

                Filter::make('amount_range')
                    ->label('Rentang Nominal Bonus')
                    ->schema([
                        TextInput::make('min')
                            ->label('Minimum (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->placeholder('0'),
                        TextInput::make('max')
                            ->label('Maksimum (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->placeholder('∞'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('amount', '>=', $data['min']))
                        ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('amount', '<=', $data['max']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['min'] ?? null)) {
                            $indicators[] = Indicator::make('Bonus dari Rp' . number_format((float) $data['min'], 0, ',', '.'))->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('Bonus sampai Rp' . number_format((float) $data['max'], 0, ',', '.'))->removeField('max');
                        }

                        return $indicators;
                    }),

                self::betweenDateFilter('created_between', 'Periode Dibuat', 'created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(12)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['member_id']->columnSpan(3),
                $filters['from_member_id']->columnSpan(3),
                $filters['status']->columnSpan(2),
                $filters['level_range']->columnSpan(4),
                $filters['amount_range']->columnSpan(4),
                $filters['created_between']->columnSpan(4),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function betweenDateFilter(string $name, string $label, string $column): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                DateTimePicker::make('from')
                    ->label('Dari Tanggal')
                    ->seconds(false)
                    ->placeholder('Awal periode'),
                DateTimePicker::make('until')
                    ->label('Sampai Tanggal')
                    ->seconds(false)
                    ->placeholder('Akhir periode'),
            ])
            ->columns(2)
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(filled($data['from'] ?? null), fn (Builder $builder): Builder => $builder->where($column, '>=', $data['from']))
                ->when(filled($data['until'] ?? null), fn (Builder $builder): Builder => $builder->where($column, '<=', $data['until']))
            )
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if (filled($data['from'] ?? null)) {
                    $indicators[] = Indicator::make($label . ' dari ' . $data['from'])->removeField('from');
                }

                if (filled($data['until'] ?? null)) {
                    $indicators[] = Indicator::make($label . ' sampai ' . $data['until'])->removeField('until');
                }

                return $indicators;
            });
    }

    private static function statusOptions(): array
    {
        return [
            0 => 'Menunggu Pencairan',
            1 => 'Sudah Dirilis',
        ];
    }
}
