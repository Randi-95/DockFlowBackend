<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

class ProductsByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Distribusi Kategori Produk';

    protected function getData(): array
    {
        $categories = Category::withCount('products')->get();
        
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Produk',
                    'data' => $categories->pluck('products_count')->toArray(),
                    'backgroundColor' => [
                        '#1565C0',
                        '#0ea5e9',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#64748b',
                        '#ec4899',
                    ],
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
