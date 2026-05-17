<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\ManageBookings;
use App\Models\Booking;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static \UnitEnum|string|null $navigationGroup = 'Transcation';

    const TERMINAL_STATUSES = ['completed', 'cancelled'];

    const CANCELLABLE_STATUSES = ['waiting', 'confirmed', 'processing', 'on_delivery', 'pending_completion'];

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_number')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('vessel_id')
                    ->relationship('vessel', 'name')
                    ->required(),
                TextInput::make('dock_location'),
                DateTimePicker::make('estimated_delivery_date')
                    ->required(),
                TextInput::make('total_estimated_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Select::make('status')
                    ->options(function ($record) {
                        if ($record === null) {
                            return [
                                'waiting'            => 'Waiting',
                                'confirmed'          => 'Confirmed',
                                'processing'         => 'Processing',
                                'on_delivery'        => 'On Delivery',
                                'pending_completion' => 'Pending Completion',
                                'completed'          => 'Completed',
                                'cancelled'          => 'Cancelled',
                            ];
                        }

                        $currentStatus = $record->status;

                        if (in_array($currentStatus, self::TERMINAL_STATUSES)) {
                            return [$currentStatus => ucfirst(str_replace('_', ' ', $currentStatus))];
                        }

                        $nextStep = [
                            'waiting'            => ['confirmed'          => 'Confirmed'],
                            'confirmed'          => ['processing'         => 'Processing'],
                            'processing'         => ['on_delivery'        => 'On Delivery'],
                            'on_delivery'        => ['pending_completion' => 'Pending Completion'],
                            'pending_completion' => ['completed'          => 'Completed'],
                        ];

                        $currentLabels = [
                            'waiting'            => 'Waiting',
                            'confirmed'          => 'Confirmed',
                            'processing'         => 'Processing',
                            'on_delivery'        => 'On Delivery',
                            'pending_completion' => 'Pending Completion',
                        ];

                        return array_merge(
                            [$currentStatus => $currentLabels[$currentStatus] ?? ucfirst(str_replace('_', ' ', $currentStatus))],
                            $nextStep[$currentStatus] ?? []
                        );
                    })
                    ->disabled(fn ($record) => $record !== null && in_array($record->status, self::TERMINAL_STATUSES))
                    ->required(),

                // Tampilkan foto bukti pengiriman jika ada
                \Filament\Forms\Components\Placeholder::make('proof_of_delivery_preview')
                    ->label('Bukti Pengiriman')
                    ->content(fn ($record) => $record && $record->proof_of_delivery
                        ? new \Illuminate\Support\HtmlString(
                            '<a href="' . asset('storage/' . $record->proof_of_delivery) . '" target="_blank">' .
                            '<img src="' . asset('storage/' . $record->proof_of_delivery) . '" ' .
                            'style="max-width:320px;max-height:240px;border-radius:8px;border:1px solid #e5e7eb;cursor:zoom-in;" ' .
                            'title="Klik untuk memperbesar" /></a>'
                        )
                        : 'Belum ada bukti pengiriman.')
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_number')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('vessel.name')
                    ->label('Vessel')
                    ->searchable(),
                TextColumn::make('dock_location')
                    ->searchable(),
                TextColumn::make('estimated_delivery_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('total_estimated_price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting'            => 'gray',
                        'confirmed'          => 'info',
                        'processing'         => 'warning',
                        'on_delivery'        => 'primary',
                        'pending_completion' => 'warning',
                        'completed'          => 'success',
                        'cancelled'          => 'danger',
                        default              => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting'            => 'Menunggu',
                        'confirmed'          => 'Dikonfirmasi',
                        'processing'         => 'Diproses',
                        'on_delivery'        => 'Dalam Pengiriman',
                        'pending_completion' => 'Menunggu Persetujuan',
                        'completed'          => 'Selesai',
                        'cancelled'          => 'Dibatalkan',
                        default              => ucfirst($state),
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('complete')
                    ->label('Selesaikan')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Pesanan')
                    ->modalDescription('Admin menyetujui bukti pengiriman. Pesanan akan ditandai sebagai Selesai (completed). Tindakan ini tidak dapat dibatalkan.')
                    ->visible(fn (Booking $record) => $record->status === 'pending_completion')
                    ->action(function (Booking $record) {
                        try {
                            $record->update(['status' => 'completed']);
                            Notification::make()
                                ->title('Pesanan Diselesaikan')
                                ->body('Pesanan ' . $record->booking_number . ' telah diselesaikan.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal menyelesaikan pesanan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('confirm')
                    ->label('Confirm')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pesanan')
                    ->modalDescription('Pesanan akan dikonfirmasi dan stok produk akan otomatis dikurangi. Tindakan ini tidak dapat dikembalikan ke Menunggu.')
                    ->visible(fn (Booking $record) => $record->status === 'waiting')
                    ->action(function (Booking $record) {
                        try {
                            $record->update(['status' => 'confirmed']);
                            Notification::make()
                                ->title('Pesanan dikonfirmasi')
                                ->body('Stok produk telah dikurangi secara otomatis.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal mengkonfirmasi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pesanan')
                    ->modalDescription('Pesanan akan dibatalkan. Jika sudah dikonfirmasi, stok produk akan dikembalikan secara otomatis.')
                    ->visible(fn (Booking $record) => in_array($record->status, self::CANCELLABLE_STATUSES))
                    ->action(function (Booking $record) {
                        try {
                            $record->update(['status' => 'cancelled']);
                            Notification::make()
                                ->title('Pesanan dibatalkan')
                                ->body('Stok produk telah dikembalikan (jika sebelumnya sudah dikonfirmasi).')
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal membatalkan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBookings::route('/'),
        ];
    }
}
