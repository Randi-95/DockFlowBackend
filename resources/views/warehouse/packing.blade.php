@extends('warehouse.layout')

@section('title', 'Validasi Packing — ' . $booking->booking_number)

@section('content')
<div class="p-6 max-w-6xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-textMuted mb-5 fade-in">
        <a href="{{ route('warehouse.queue') }}" class="hover:text-primary transition-colors flex items-center gap-1">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Antrean
        </a>
        <span>/</span>
        <span class="text-textDark font-medium font-mono">{{ $booking->booking_number }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        <div class="lg:col-span-3 space-y-4 fade-in">
            <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-textDark">Daftar Barang</h2>
                        <p class="text-sm text-textMuted">Scan semua barcode barang di bawah ini</p>
                    </div>

                    <div class="text-right">
                        <p class="text-3xl font-extrabold text-primary" id="countDisplay">
                            {{ count($scannedBarcodes) }}<span class="text-lg text-textMuted font-medium">/{{ $booking->bookingDetails->count() }}</span>
                        </p>
                        <p class="text-xs text-textMuted">item di-scan</p>
                    </div>
                </div>

                <div class="w-full bg-gray-100 rounded-full h-2.5 mb-5 overflow-hidden">
                    <div id="progressBar"
                         class="bg-gradient-to-r from-primary to-blue-400 h-2.5 rounded-full transition-all duration-500"
                         style="width: {{ $booking->bookingDetails->count() > 0 ? (count($scannedBarcodes) / $booking->bookingDetails->count() * 100) : 0 }}%">
                    </div>
                </div>

                <div class="space-y-3" id="itemList">
                    @foreach($booking->bookingDetails as $detail)
                    @php
                        $productId = (string) $detail->product_id;
                        $isDone = isset($scannedBarcodes[$productId]);
                    @endphp
                    <div id="item-{{ $productId }}"
                         class="flex items-center gap-4 p-4 rounded-xl border-2 transition-all duration-300
                                {{ $isDone ? 'border-green-200 bg-green-50' : 'border-gray-100 bg-gray-50/50' }}">

                        <div id="icon-{{ $productId }}"
                             class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-300
                                    {{ $isDone ? 'bg-green-500' : 'bg-gray-200' }}">
                            @if($isDone)
                            <svg width="18" height="18" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            @else
                            <svg width="18" height="18" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                            </svg>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-textDark truncate" id="name-{{ $productId }}">
                                {{ $detail->product?->name ?? 'Produk tidak ditemukan' }}
                            </p>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-xs text-textMuted">SKU: <span class="font-mono font-medium">{{ $detail->product?->sku_code ?? '-' }}</span></span>
                                <span class="text-xs bg-blue-50 text-primary font-mono px-2 py-0.5 rounded-md">
                                    Rak: {{ $detail->product?->rack_location ?? '—' }}
                                </span>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <span class="text-2xl font-extrabold text-primary">×{{ $detail->qty }}</span>
                            <p class="text-xs text-textMuted">{{ $detail->product?->unit ?? 'unit' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4 fade-in" style="animation-delay:0.1s">

            <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-card">
                <h3 class="text-sm font-semibold text-textMuted uppercase tracking-wider mb-3">Info Pesanan</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-textMuted">No. Pesanan</span>
                        <span class="font-mono font-bold text-primary">{{ $booking->booking_number }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-textMuted">Kapal</span>
                        <span class="font-semibold">{{ $booking->vessel?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-textMuted">Dermaga</span>
                        <span class="font-semibold">{{ $booking->dock_location ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-textMuted">Pemesan</span>
                        <span class="font-semibold">{{ $booking->user?->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-card">
                <h3 class="text-sm font-semibold text-textMuted uppercase tracking-wider mb-3">Scanner Barcode</h3>

                <div class="relative w-full h-28 bg-gray-900 rounded-xl overflow-hidden mb-4 flex items-center justify-center" id="scannerBox">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-blue-500/10 to-transparent h-1/3 scan-line"></div>
                    <div class="w-full px-6">
                        <div class="border-t-2 border-l-2 border-blue-400 w-6 h-6 absolute top-3 left-4"></div>
                        <div class="border-t-2 border-r-2 border-blue-400 w-6 h-6 absolute top-3 right-4"></div>
                        <div class="border-b-2 border-l-2 border-blue-400 w-6 h-6 absolute bottom-3 left-4"></div>
                        <div class="border-b-2 border-r-2 border-blue-400 w-6 h-6 absolute bottom-3 right-4"></div>
                        <div class="flex gap-0.5 justify-center" id="scannerBars">
                            @for($i = 0; $i < 28; $i++)
                            <div class="bg-white/80 rounded-sm" style="width:2px; height:{{ rand(20, 56) }}px"></div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="relative mb-4">
                    <input type="text" id="barcodeInput" autofocus autocomplete="off"
                           placeholder="Arahkan scanner ke barcode..."
                           class="w-full border-2 border-blue-200 focus:border-primary rounded-xl px-4 py-3 text-sm font-mono
                                  placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all bg-blue-50/30">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-300">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                        </svg>
                    </div>
                </div>

                <div id="scanFeedback" class="hidden rounded-xl px-4 py-3 text-sm font-medium mb-4 flex items-center gap-2"></div>

                <button id="completeBtn"
                        class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl font-bold text-sm transition-all duration-300
                               {{ count($scannedBarcodes) >= $booking->bookingDetails->count() && $booking->bookingDetails->count() > 0
                                  ? 'bg-green-500 hover:bg-green-600 text-white shadow-md cursor-pointer'
                                  : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}"
                        {{ count($scannedBarcodes) < $booking->bookingDetails->count() || $booking->bookingDetails->count() == 0 ? 'disabled' : '' }}>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Selesai Packing
                </button>
            </div>

            <div class="mt-4 border-t border-dashed border-amber-200 pt-4" data-sim>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-4 h-4 bg-amber-400 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg width="8" height="8" fill="white" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Simulasi Scanner (Testing)</span>
                </div>
                <div class="space-y-2">
                    @foreach($booking->bookingDetails as $detail)
                    @php $pid = (string)$detail->product_id; $sku = $detail->product?->sku_code ?? ''; @endphp
                    <button onclick="simulateScan('{{ $sku }}')" id="simBtn-{{ $pid }}" data-sim
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl border-2 border-amber-100
                                   bg-amber-50/50 hover:bg-amber-100 transition-all text-left group
                                   {{ isset($scannedBarcodes[$pid]) ? 'opacity-40 cursor-default' : '' }}"
                            {{ isset($scannedBarcodes[$pid]) ? 'disabled' : '' }}>
                        <div>
                            <p class="text-xs font-semibold text-textDark truncate max-w-[140px]">{{ $detail->product?->name }}</p>
                            <p class="text-xs font-mono text-amber-600">{{ $sku }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @if(isset($scannedBarcodes[$pid]))
                            <span class="text-green-500">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            @else
                            <span class="text-xs font-bold text-amber-600 group-hover:text-amber-700">Scan</span>
                            <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                            @endif
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const barcodeInput = document.getElementById('barcodeInput');
    const completeBtn  = document.getElementById('completeBtn');
    const progressBar  = document.getElementById('progressBar');
    const countDisplay = document.getElementById('countDisplay');
    const scanFeedback = document.getElementById('scanFeedback');
    const totalItems   = {{ $booking->bookingDetails->count() }};
    let scannedCount   = {{ count($scannedBarcodes) }};

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-sim]')) return;
        barcodeInput.focus();
    });
    barcodeInput.focus();

    barcodeInput.addEventListener('keypress', async function(e) {
        if (e.key !== 'Enter') return;
        const barcode = this.value.trim();
        if (!barcode) return;
        this.value = '';

        try {
            const res = await fetch('{{ route("warehouse.packing.scan", $booking->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ barcode })
            });
            const data = await res.json();

            if (data.success) {
                const productId = data.product_id;
                const itemEl    = document.getElementById('item-' + productId);
                const iconEl    = document.getElementById('icon-' + productId);

                if (itemEl) {
                    itemEl.classList.remove('border-gray-100', 'bg-gray-50/50');
                    itemEl.classList.add('border-green-200', 'bg-green-50');
                    iconEl.classList.remove('bg-gray-200');
                    iconEl.classList.add('bg-green-500');
                    iconEl.innerHTML = `<svg width="18" height="18" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;
                }

                scannedCount = data.scanned_count;
                const pct = (scannedCount / totalItems * 100);
                progressBar.style.width = pct + '%';
                countDisplay.innerHTML = `${scannedCount}<span class="text-lg text-textMuted font-medium">/${totalItems}</span>`;

                showFeedback('success', `✓ ${data.product_name} — berhasil di-scan!`);

                if (data.all_done) {
                    completeBtn.disabled = false;
                    completeBtn.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                    completeBtn.classList.add('bg-green-500', 'hover:bg-green-600', 'text-white', 'shadow-md', 'cursor-pointer');
                    showFeedback('success', '🎉 Semua barang sudah di-scan! Klik Selesai Packing.');
                }

            } else {
                showFeedback('error', '✗ ' + (data.message || 'Barcode tidak cocok.'));
            }
        } catch (err) {
            showFeedback('error', 'Kesalahan jaringan. Coba lagi.');
        }
    });

    function showFeedback(type, msg) {
        scanFeedback.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border', 'border-green-200',
                                                    'bg-red-50', 'text-red-700', 'border-red-200');
        scanFeedback.classList.add('flex');

        if (type === 'success') {
            scanFeedback.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
        } else {
            scanFeedback.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
        }
        scanFeedback.textContent = msg;

        clearTimeout(window._feedbackTimer);
        window._feedbackTimer = setTimeout(() => {
            scanFeedback.classList.add('hidden');
            scanFeedback.classList.remove('flex');
        }, 3500);
    }

    completeBtn.addEventListener('click', async function() {
        if (this.disabled) return;

        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Selesai Packing?',
            text: 'Pastikan semua barang sudah dikemas dengan benar. Status pesanan akan berubah ke Diproses.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesai!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#22c55e',
        });

        if (!confirm.isConfirmed) {
            barcodeInput.focus();
            return;
        }

        try {
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const res = await fetch('{{ route("warehouse.packing.complete", $booking->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Packing Selesai!',
                    text: 'Status pesanan telah berubah ke Diproses. Silakan cetak Surat Jalan untuk crew.',
                    showCancelButton: true,
                    confirmButtonText: '🖨️ Cetak Surat Jalan',
                    cancelButtonText: 'Kembali ke Antrean',
                    confirmButtonColor: '#1565c0',
                    cancelButtonColor: '#64748b',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open('{{ route("warehouse.packing.print-thermal", $booking->id) }}', '_blank');
                    }
                    window.location.href = data.redirect;
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message }).then(() => {
                    barcodeInput.focus();
                });
            }
        } catch (err) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Kesalahan Jaringan', 
                text: 'Tidak dapat menghubungi server.' 
            }).then(() => {
                barcodeInput.focus();
            });
        }
    });

    function simulateScan(sku) {
        if (!sku) return;
        barcodeInput.value = sku;
        barcodeInput.dispatchEvent(new KeyboardEvent('keypress', { key: 'Enter', bubbles: true }));
    }

</script>
@endpush
@endsection
