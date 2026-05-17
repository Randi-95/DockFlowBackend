@extends('warehouse.layout')

@section('title', 'Antrean Pesanan')

@section('content')
<div class="p-6 max-w-7xl mx-auto">

    <div class="flex items-center justify-between mb-6 fade-in">
        <div>
            <h2 class="text-2xl font-bold text-textDark">Antrean Picking</h2>
            <p class="text-textMuted text-sm mt-0.5">Pesanan yang sudah dikonfirmasi dan siap diproses dari rak</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="glass bg-white border border-blue-100 rounded-2xl px-5 py-3 flex items-center gap-3 shadow-card">
                <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg width="16" height="16" fill="none" stroke="#1565C0" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-textMuted font-medium leading-none">Total Pesanan</p>
                    <p class="text-xl font-bold text-primary leading-tight">{{ $bookings->count() }}</p>
                </div>
            </div>
            <button onclick="location.reload()"
                class="flex items-center gap-2 px-4 py-2.5 bg-white border border-blue-100 rounded-xl text-sm font-medium text-primary hover:bg-blue-50 transition-all shadow-card">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 .49-4.17"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    @if($bookings->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 fade-in">
        <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mb-4">
            <svg width="40" height="40" fill="none" stroke="#22c55e" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-textDark mb-1">Semua Beres!</h3>
        <p class="text-textMuted text-sm">Tidak ada pesanan yang perlu diproses saat ini.</p>
    </div>
    @else

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($bookings as $index => $booking)
        <div class="glass bg-white border border-blue-100/60 rounded-2xl shadow-card hover:shadow-soft hover:-translate-y-0.5 transition-all duration-200 overflow-hidden fade-in"
             style="animation-delay: {{ $index * 0.05 }}s">

            <div class="bg-gradient-to-r from-primary to-blue-500 px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-xs font-medium uppercase tracking-wider">Nomor Pesanan</p>
                    <p class="text-white font-bold text-base font-mono leading-tight">{{ $booking->booking_number }}</p>
                </div>
                <div class="bg-white/20 rounded-xl px-3 py-1.5 flex items-center gap-1.5">
                    <div class="w-2 h-2 bg-blue-200 rounded-full animate-pulse"></div>
                    <span class="text-white text-xs font-semibold">Dikonfirmasi</span>
                </div>
            </div>

            <div class="p-5">
                <div class="space-y-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg width="15" height="15" fill="none" stroke="#1565C0" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 11l19-9-9 19-2-8-8-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-textMuted">Kapal</p>
                            <p class="text-sm font-semibold text-textDark">{{ $booking->vessel?->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg width="15" height="15" fill="none" stroke="#1565C0" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-textMuted">Lokasi Dermaga</p>
                            <p class="text-sm font-semibold text-textDark">{{ $booking->dock_location ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg width="15" height="15" fill="none" stroke="#1565C0" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-textMuted">Jumlah Item</p>
                            <p class="text-sm font-semibold text-textDark">{{ $booking->bookingDetails->count() }} produk</p>
                        </div>
                    </div>
                </div>

                <div class="bg-surface rounded-xl p-3 mb-4 space-y-1.5">
                    @foreach($booking->bookingDetails->take(3) as $detail)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                            <span class="text-textDark font-medium truncate max-w-[160px]">{{ $detail->product?->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-textMuted bg-blue-50 px-2 py-0.5 rounded-md font-mono">
                                {{ $detail->product?->rack_location ?? '—' }}
                            </span>
                            <span class="text-primary font-bold text-xs">×{{ $detail->qty }}</span>
                        </div>
                    </div>
                    @endforeach
                    @if($booking->bookingDetails->count() > 3)
                    <p class="text-xs text-textMuted text-center pt-1">+{{ $booking->bookingDetails->count() - 3 }} item lainnya</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 text-xs text-textMuted mb-4">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Est. Pengiriman: <span class="font-medium text-textDark">{{ \Carbon\Carbon::parse($booking->estimated_delivery_date)->format('d M Y') }}</span>
                </div>

                <a href="{{ route('warehouse.packing', $booking->id) }}"
                   class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primaryDark text-white font-semibold py-3 rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="9 11 12 14 22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Mulai Packing
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('scripts')
<script>
    setTimeout(() => location.reload(), 30000);
</script>
@endpush
@endsection
