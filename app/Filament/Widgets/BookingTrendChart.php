<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Grafik Tren Booking (Tahun Ini)';

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        // Menghitung jumlah booking per bulan dari Januari - Desember tahun ini
        for ($i = 1; $i <= 12; $i++) {
            $count = Booking::whereMonth('created_at', $i)
                ->whereYear('created_at', date('Y'))
                ->count();
            $data[] = $count;
            $labels[] = date('M', mktime(0, 0, 0, $i, 1));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Booking',
                    'data' => $data,
                    'borderColor' => '#1565C0',
                    'backgroundColor' => 'rgba(21, 101, 192, 0.2)',
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
