<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentBookings extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('booking_number')
                    ->label('Booking #'),
                Tables\Columns\TextColumn::make('vessel.name')
                    ->label('Vessel'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime(),
            ])
            ->paginated(false)
            ->heading('Recent Bookings');
    }
}
