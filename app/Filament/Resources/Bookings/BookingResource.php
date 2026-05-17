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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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
                Action::make('review_pod')
                    ->label('Review Bukti')
                    ->color('warning')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->modalHeading('Review Bukti Pengiriman')
                    ->modalDescription(fn (Booking $record) => 'Booking #' . $record->booking_number . ' — Periksa bukti pengiriman di bawah sebelum menyetujui atau menolak.')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('✔ Setujui & Selesaikan')
                    ->modalCancelActionLabel('Tutup')
                    ->visible(fn (Booking $record) => $record->status === 'pending_completion')
                    ->form(fn (Booking $record) => [
                        Placeholder::make('proof_preview')
                            ->label('Foto Bukti Pengiriman')
                            ->content(function () use ($record) {
                                if (!$record->proof_of_delivery) {
                                    return new HtmlString(
                                        '<div style="padding:24px;text-align:center;color:#ef4444;background:#fef2f2;border-radius:8px;border:1px solid #fecaca;">' .
                                        '<svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;margin:0 auto 8px;display:block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>' .
                                        '<strong>Belum ada bukti pengiriman yang diupload.</strong><br><small>Crew belum mengirimkan foto.</small></div>'
                                    );
                                }

                                $url = asset('storage/' . $record->proof_of_delivery);
                                return new HtmlString(
                                    '<div style="text-align:center;">' .
                                    '<a href="' . $url . '" target="_blank" title="Klik untuk memperbesar">' .
                                    '<img src="' . $url . '" ' .
                                    'style="max-width:100%;max-height:420px;border-radius:10px;border:2px solid #e5e7eb;box-shadow:0 4px 16px rgba(0,0,0,0.12);cursor:zoom-in;" />' .
                                    '</a>' .
                                    '<p style="margin-top:10px;font-size:0.8rem;color:#6b7280;">Klik gambar untuk membuka di tab baru</p>' .
                                    '</div>'
                                );
                            }),

                        Placeholder::make('booking_info')
                            ->label('Informasi Booking')
                            ->content(fn () => new HtmlString(
                                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px;background:#f9fafb;border-radius:8px;font-size:0.875rem;">' .
                                '<div><strong style="color:#6b7280;">Nomor Booking</strong><br>' . e($record->booking_number) . '</div>' .
                                '<div><strong style="color:#6b7280;">Customer</strong><br>' . e($record->user?->name ?? '-') . '</div>' .
                                '<div><strong style="color:#6b7280;">Kapal</strong><br>' . e($record->vessel?->name ?? '-') . '</div>' .
                                '<div><strong style="color:#6b7280;">Lokasi Dok</strong><br>' . e($record->dock_location ?? '-') . '</div>' .
                                '</div>'
                            )),
                    ])
                    ->action(function (Booking $record) {
                        if (!$record->proof_of_delivery) {
                            Notification::make()
                                ->title('Tidak dapat disetujui')
                                ->body('Crew belum mengupload bukti pengiriman.')
                                ->danger()
                                ->send();
                            return;
                        }
                        try {
                            $record->update(['status' => 'completed']);
                            Notification::make()
                                ->title('✅ Pesanan Diselesaikan')
                                ->body('Booking #' . $record->booking_number . ' telah disetujui dan diselesaikan.')
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

                Action::make('reject_pod')
                    ->label('Tolak Bukti')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Bukti Pengiriman')
                    ->modalDescription(fn (Booking $record) =>
                        'Booking #' . $record->booking_number . ' — Bukti pengiriman akan ditolak. Status akan dikembalikan ke "Dalam Pengiriman" agar crew dapat mengirim ulang bukti yang benar.'
                    )
                    ->modalSubmitActionLabel('Ya, Tolak Bukti')
                    ->visible(fn (Booking $record) => $record->status === 'pending_completion')
                    ->action(function (Booking $record) {
                        try {
                            DB::table('bookings')
                                ->where('id', $record->id)
                                ->update([
                                    'proof_of_delivery' => null,
                                    'status'            => 'on_delivery',
                                    'updated_at'        => now(),
                                ]);
                            Notification::make()
                                ->title('❌ Bukti Ditolak')
                                ->body('Booking #' . $record->booking_number . ' dikembalikan ke status "Dalam Pengiriman". Crew perlu upload ulang.')
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal menolak bukti')
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
