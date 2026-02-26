<?php

namespace App\Filament\Resources\ProductReviews\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.name')
                    ->label('Customer'),
                TextEntry::make('product.name')
                    ->label('Product'),
                TextEntry::make('orderItem.name')
                    ->label('Order item')
                    ->placeholder('-'),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('comment')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_approved')
                    ->boolean(),
                IconEntry::make('is_verified_purchase')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
