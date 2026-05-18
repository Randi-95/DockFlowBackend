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
                                $darkStyle = '
                                    <style>
                                        .pod-empty-box { padding:24px;text-align:center;border-radius:8px;border:1px solid; color: rgb(239 68 68); background: rgb(254 242 242); border-color: rgb(254 202 202); }
                                        .pod-hint { margin-top:10px;font-size:0.8rem;opacity:0.6; }
                                        .pod-info-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px;border-radius:8px;font-size:0.875rem;border:1px solid; background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.08); }
                                        .pod-info-label { font-size:0.75rem;opacity:0.55;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px; }
                                        /* Filament uses Tailwind class-based dark mode: .dark on <html> */
                                        .dark .pod-empty-box { color: rgb(252 165 165); background: rgba(69,10,10,0.5); border-color: rgba(127,29,29,0.5); }
                                        .dark .pod-info-grid { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.08); }
                                    </style>
                                ';

                                if (!$record->proof_of_delivery) {
                                    return new HtmlString(
                                        $darkStyle .
                                        '<div class="pod-empty-box">' .
                                        '<svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;margin:0 auto 8px;display:block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>' .
                                        '<strong>Belum ada bukti pengiriman yang diupload.</strong><br><small>Crew belum mengirimkan foto.</small>' .
                                        '</div>'
                                    );
                                }

                                $url = asset('storage/' . $record->proof_of_delivery);
                                return new HtmlString(
                                    $darkStyle .
                                    '<div style="text-align:center;">' .
                                    '<a href="' . $url . '" target="_blank" title="Klik untuk memperbesar">' .
                                    '<img src="' . $url . '" ' .
                                    'style="max-width:100%;max-height:420px;border-radius:10px;border:1px solid rgba(128,128,128,0.25);box-shadow:0 4px 20px rgba(0,0,0,0.2);cursor:zoom-in;" />' .
                                    '</a>' .
                                    '<p class="pod-hint">Klik gambar untuk membuka di tab baru</p>' .
                                    '</div>'
                                );
                            }),

                        Placeholder::make('booking_info')
                            ->label('Informasi Booking')
                            ->content(fn () => new HtmlString(
                                '<div class="pod-info-grid">' .
                                '<div><div class="pod-info-label">Nomor Booking</div>' . e($record->booking_number) . '</div>' .
                                '<div><div class="pod-info-label">Customer</div>' . e($record->user?->name ?? '-') . '</div>' .
                                '<div><div class="pod-info-label">Kapal</div>' . e($record->vessel?->name ?? '-') . '</div>' .
                                '<div><div class="pod-info-label">Lokasi Dok</div>' . e($record->dock_location ?? '-') . '</div>' .
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

                Action::make('review_items')
                    ->label('Review Barang')
                    ->color('primary')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->modalHeading('Review Detail Barang')
                    ->modalDescription(fn (Booking $record) => 'Booking #' . $record->booking_number . ' — Tinjau detail barang di bawah ini sebelum mengonfirmasi pesanan.')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('✔ Konfirmasi & Kurangi Stok')
                    ->modalCancelActionLabel('Batal')
                    ->visible(fn (Booking $record) => $record->status === 'waiting')
                    ->form(fn (Booking $record) => [
                        Placeholder::make('items_review')
                            ->label('Daftar Barang')
                            ->content(function () use ($record) {
                                $styles = '
                                    <style>
                                        .review-table { width:100%; border-collapse:collapse; font-size:0.875rem; border-radius:8px; overflow:hidden; }
                                        .review-table th { text-align:left; padding:10px; font-weight:600; background: rgba(0,0,0,0.04); }
                                        .review-table td { padding:10px; border-bottom:1px solid rgba(0,0,0,0.05); }
                                        .review-table tr:last-child td { border-bottom:none; }
                                        .review-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:14px; border-radius:8px; font-size:0.875rem; border:1px solid rgba(0,0,0,0.08); background: rgba(0,0,0,0.04); margin-bottom:15px; }
                                        .review-info-label { font-size:0.75rem; opacity:0.55; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px; }
                                        .review-total { text-align:right; font-weight:bold; font-size:1rem; color: #1565C0; padding-top:10px; }
                                        
                                        .dark .review-table th { background: rgba(255,255,255,0.05); }
                                        .dark .review-table td { border-bottom-color: rgba(255,255,255,0.05); }
                                        .dark .review-info-grid { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.08); }
                                        .dark .review-total { color: #42a5f5; }
                                    </style>
                                ';

                                $infoGrid = '
                                    <div class="review-info-grid">
                                        <div><div class="review-info-label">Customer</div>' . e($record->user?->name ?? '-') . '</div>
                                        <div><div class="review-info-label">Kapal</div>' . e($record->vessel?->name ?? '-') . '</div>
                                        <div><div class="review-info-label">Lokasi Dok</div>' . e($record->dock_location ?? '-') . '</div>
                                        <div><div class="review-info-label">Tgl Estimasi Pengiriman</div>' . e(\Carbon\Carbon::parse($record->estimated_delivery_date)->format('d M Y H:i')) . '</div>
                                    </div>
                                ';

                                if ($record->bookingDetails->isEmpty()) {
                                    return new HtmlString($styles . $infoGrid . '<div style="padding:15px; text-align:center; color:red;">Tidak ada barang dalam pesanan ini.</div>');
                                }

                                $rows = '';
                                foreach ($record->bookingDetails as $detail) {
                                    $product = $detail->product;
                                    $name = $product ? $product->name : 'Produk tidak ditemukan';
                                    $sku = $product ? $product->sku_code : '—';
                                    $rack = $product ? $product->rack_location : '—';
                                    $qty = $detail->qty;
                                    $unit = $product ? $product->unit : 'unit';
                                    $price = number_format($detail->price_at_booking, 0, ',', '.');
                                    $subtotal = number_format($detail->price_at_booking * $qty, 0, ',', '.');

                                    $rows .= "
                                        <tr>
                                            <td>
                                                <div style='font-weight:600;'>{$name}</div>
                                                <div style='font-size:0.75rem; opacity:0.6;'>SKU: {$sku} | Rak: {$rack}</div>
                                            </td>
                                            <td>{$qty} {$unit}</td>
                                            <td style='text-align:right;'>Rp {$price}</td>
                                            <td style='text-align:right; font-weight:600;'>Rp {$subtotal}</td>
                                        </tr>
                                    ";
                                }

                                $grandTotal = number_format($record->total_estimated_price, 0, ',', '.');

                                $table = "
                                    <table class='review-table'>
                                        <thead>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <th>Qty</th>
                                                <th style='text-align:right;'>Harga</th>
                                                <th style='text-align:right;'>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {$rows}
                                        </tbody>
                                    </table>
                                    <div class='review-total'>
                                        Total Estimasi: Rp {$grandTotal}
                                    </div>
                                ";

                                return new HtmlString($styles . $infoGrid . $table);
                            })
                    ])
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
