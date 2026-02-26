<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PromotionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code'),
                TextEntry::make('name'),
                TextEntry::make('type'),
                TextEntry::make('landing_slug')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                ImageEntry::make('image')
                    ->placeholder('-'),
                TextEntry::make('start_at')
                    ->dateTime(),
                TextEntry::make('end_at')
                    ->dateTime(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('priority')
                    ->numeric(),
                TextEntry::make('max_redemption')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('per_user_limit')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('conditions_json')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('show_on')
                    ->placeholder('-'),
                TextEntry::make('custom_html')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('page')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
