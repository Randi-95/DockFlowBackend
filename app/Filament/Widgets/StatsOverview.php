<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Booking;
use App\Models\Vessel;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', Product::count())
                ->description('All registered items')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make('Total Bookings', Booking::count())
                ->description('All bookings')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),
            Stat::make('Available Vessels', Vessel::count())
                ->description('Total vessels')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('info'),
            Stat::make('Total Employees', User::count())
                ->description('Registered crew & admins')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}
