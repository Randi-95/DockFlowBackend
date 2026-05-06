<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProducts extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->where('stock_qty', '<', 10)->orderBy('stock_qty', 'asc')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('sku_code')
                    ->label('SKU'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name'),
                Tables\Columns\TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->numeric()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('rack_location')
                    ->label('Location'),
            ])
            ->paginated(false)
            ->heading('Low Stock Alert');
    }
}
