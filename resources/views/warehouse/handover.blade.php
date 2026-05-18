@extends('warehouse.layout')

@section('title', 'Serah Terima')

@push('styles')
<style>
    #reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }
    .scanner-mask {
        box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.65);
    }
</style>
@endpush

@section('content')
<div class="min-h-[calc(100vh-65px)] flex items-center justify-center p-6 relative overflow-hidden">

    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 left-0 w-full h-full"
             style="background: radial-gradient(circle at 60% 40%, rgba(21,101,192,0.04) 0%, transparent 60%)"></div>
        <div class="absolute bottom-8 left-8 w-32 h-64 opacity-20"
             style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>
        <div class="absolute top-8 right-8 w-32 h-64 opacity-20"
             style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>
    </div>

    <div class="w-full max-w-2xl fade-in">

        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 bg-blue-50 border border-blue-100 text-primary rounded-full px-4 py-1.5 text-sm font-medium mb-4">
                <div class="w-2 h-2 bg-primary rounded-full animate-pulse"></div>
                Loket Serah Terima
            </div>
            <h2 class="text-3xl font-extrabold text-textDark mb-2">Scan QR Code Resi</h2>
            <p class="text-textMuted">Arahkan kamera ke QR Code pesanan untuk memindahkan tanggung jawab ke kru lapangan</p>
        </div>

        <div class="bg-white border border-blue-100 rounded-3xl shadow-soft overflow-hidden">

            <div class="bg-gray-900 relative h-80 flex items-center justify-center overflow-hidden rounded-t-3xl" id="scannerArea">
                <div id="reader" class="absolute inset-0 w-full h-full hidden"></div>
                
                <div id="scannerBox" class="relative w-60 h-60 flex items-center justify-center z-10 scanner-mask rounded-3xl transition-all duration-300">
                    <div class="absolute -top-1 -left-1 w-8 h-8 border-t-4 border-l-4 border-primary rounded-tl-xl z-20"></div>
                    <div class="absolute -top-1 -right-1 w-8 h-8 border-t-4 border-r-4 border-primary rounded-tr-xl z-20"></div>
                    <div class="absolute -bottom-1 -left-1 w-8 h-8 border-b-4 border-l-4 border-primary rounded-bl-xl z-20"></div>
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 border-b-4 border-r-4 border-primary rounded-br-xl z-20"></div>

                    <div id="scanLaser" class="absolute inset-x-3 h-0.5 bg-gradient-to-r from-transparent via-primary to-transparent scan-line opacity-90 z-20 hidden"></div>

                    <div id="placeholderQR" class="flex flex-col items-center gap-3 text-white/40 select-none transition-all duration-300">
                        <svg width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="animate-pulse">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h6v6H3V3zm0 12h6v6H3v-6zm12-12h6v6h-6V3zm3 12h3v3h-3v-3zm-3 3h3v3h-3v-3zm0-3h-3v-3h3v3zm-3 3h-3v-3h3v3zm-3-3H9v-3h3v3zm3-3h-3V9h3v3zm3-3h3v3h-3V9z"/>
                            <rect x="5" y="5" width="2" height="2" fill="currentColor"/>
                            <rect x="5" y="17" width="2" height="2" fill="currentColor"/>
                            <rect x="17" y="5" width="2" height="2" fill="currentColor"/>
                        </svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-white/50">Kamera Nonaktif</span>
                    </div>
                </div>

                <div id="scannerOverlay" class="absolute inset-0 flex items-center justify-center hidden z-30">
                    <div id="overlayContent"></div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex justify-center mb-6">
                    <button id="toggleCameraBtn" onclick="toggleCamera()"
                            class="px-5 py-2.5 bg-primary hover:bg-primaryDark text-white font-bold text-sm rounded-xl transition-all active:scale-95 shadow-md flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2 2v-11a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        <span>Aktifkan Kamera</span>
                    </button>
                </div>

                <div class="relative mb-5">
                    <label class="block text-xs font-semibold text-textMuted uppercase tracking-wider mb-2">
                        Input QR Code / No. Pesanan
                    </label>
                    <input type="text" id="bookingBarcodeInput" autofocus autocomplete="off"
                           placeholder="Scan atau ketik nomor pesanan (BK-...)..."
                           class="w-full border-2 border-blue-200 focus:border-primary rounded-xl px-4 py-3.5 text-base font-mono
                                  placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all bg-blue-50/20">
                    <div class="absolute right-4 top-1/2 translate-y-1/4 text-blue-200">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                        </svg>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex items-start gap-2.5 text-sm text-amber-700">
                    <svg width="16" height="16" class="flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span>Hanya pesanan berstatus <strong>Diproses</strong> yang bisa diserahterimakan. Status akan berubah otomatis ke <strong>Dalam Pengiriman</strong>.</span>
                </div>

                <div class="mt-4 pt-4 border-t border-dashed border-amber-200" data-sim>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-4 h-4 bg-amber-400 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg width="8" height="8" fill="white" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Simulasi Scanner (Testing)</span>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" id="simBookingInput" autocomplete="off" data-sim
                               placeholder="Ketik nomor pesanan (BK-...)..."
                               class="flex-1 border-2 border-amber-200 focus:border-amber-400 rounded-xl px-3 py-2 text-sm font-mono
                                      focus:outline-none focus:ring-2 focus:ring-amber-200 transition-all bg-amber-50/30"
                               onkeypress="if(event.key==='Enter'){ simulateHandover(); }">
                        <button onclick="simulateHandover()" data-sim
                                class="px-4 py-2 bg-amber-400 hover:bg-amber-500 text-white font-bold text-sm rounded-xl transition-all active:scale-95 shadow-sm flex items-center gap-1.5">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                            </svg>
                            Scan
                        </button>
                    </div>
                    <p class="text-xs text-amber-600/70 mt-2 text-center">Masukkan <code class="font-mono bg-amber-50 px-1 rounded">booking_number</code> dari pesanan berstatus Diproses</p>
                </div>
            </div>
        </div>

        <div id="resultCard" class="hidden mt-5 bg-white border border-green-200 rounded-2xl p-6 shadow-card fade-in">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg width="20" height="20" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-textDark">Serah Terima Berhasil!</p>
                    <p class="text-sm text-textMuted">Status pesanan telah berubah ke Dalam Pengiriman</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-textMuted">No. Pesanan</span>
                    <span class="font-mono font-bold text-primary" id="resBookingNumber">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-textMuted">Kapal</span>
                    <span class="font-semibold" id="resVessel">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-textMuted">Dermaga</span>
                    <span class="font-semibold" id="resDock">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-textMuted">Pemesan</span>
                    <span class="font-semibold" id="resCustomer">—</span>
                </div>
            </div>
            <div id="resItems" class="mt-3 space-y-1"></div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const barcodeInput = document.getElementById('bookingBarcodeInput');
    const resultCard   = document.getElementById('resultCard');
    const scannerArea  = document.getElementById('scannerArea');

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-sim]') || e.target.closest('#toggleCameraBtn')) return;
        barcodeInput.focus();
    });
    barcodeInput.focus();

    async function processBarcode(barcode) {
        if (!barcode) return;

        const flash = document.createElement('div');
        flash.className = 'absolute inset-0 bg-green-500/20 z-30 transition-opacity duration-300 animate-pulse';
        scannerArea.appendChild(flash);
        setTimeout(() => flash.remove(), 600);

        try {
            const res = await fetch('{{ route("warehouse.handover.scan") }}', {
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
                document.getElementById('resBookingNumber').textContent = data.booking_number;
                document.getElementById('resVessel').textContent        = data.vessel_name ?? '-';
                document.getElementById('resDock').textContent          = data.dock_location ?? '-';
                document.getElementById('resCustomer').textContent      = data.customer_name ?? '-';

                const itemsContainer = document.getElementById('resItems');
                itemsContainer.innerHTML = '';
                if (data.items && data.items.length) {
                    const label = document.createElement('p');
                    label.className = 'text-xs text-textMuted font-semibold uppercase tracking-wider mb-1';
                    label.textContent = 'Barang yang Diserahterimakan';
                    itemsContainer.appendChild(label);
                    data.items.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center justify-between text-sm py-1 border-b border-gray-100 last:border-0';
                        div.innerHTML = `<span class="text-textDark">${item.name}</span><span class="font-bold text-primary">×${item.qty}</span>`;
                        itemsContainer.appendChild(div);
                    });
                }

                resultCard.classList.remove('hidden');

                Swal.fire({
                    icon: 'success',
                    title: 'Serah Terima Berhasil!',
                    text: `Pesanan ${data.booking_number} sekarang berstatus Dalam Pengiriman.`,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                }).then(() => {
                    barcodeInput.focus();
                    lastScannedCode = '';
                });

            } else {
                resultCard.classList.add('hidden');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Barcode tidak valid.',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                }).then(() => {
                    barcodeInput.focus();
                    lastScannedCode = '';
                });
            }
        } catch(err) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Kesalahan Jaringan', 
                text: 'Tidak dapat menghubungi server.' 
            }).then(() => {
                barcodeInput.focus();
                lastScannedCode = '';
            });
        }
    }

    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key !== 'Enter') return;
        const barcode = this.value.trim();
        if (!barcode) return;
        this.value = '';
        processBarcode(barcode);
    });

    function simulateHandover() {
        const val = document.getElementById('simBookingInput').value.trim();
        if (!val) return;
        document.getElementById('simBookingInput').value = '';
        processBarcode(val);
    }

    let html5QrCode = null;
    let isCameraActive = false;
    let lastScannedCode = '';
    let scanCooldown = false;

    async function toggleCamera() {
        const btn = document.getElementById('toggleCameraBtn');
        const reader = document.getElementById('reader');
        const laser = document.getElementById('scanLaser');
        const placeholderQR = document.getElementById('placeholderQR');

        if (isCameraActive) {
            if (html5QrCode) {
                try {
                    await html5QrCode.stop();
                } catch (err) {
                    console.error("Gagal mematikan kamera", err);
                }
            }
            isCameraActive = false;
            reader.classList.add('hidden');
            laser.classList.add('hidden');
            placeholderQR.classList.remove('hidden');

            btn.className = "px-5 py-2.5 bg-primary hover:bg-primaryDark text-white font-bold text-sm rounded-xl transition-all active:scale-95 shadow-md flex items-center gap-2";
            btn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                <span>Aktifkan Kamera</span>
            `;
        } else {
            reader.classList.remove('hidden');
            laser.classList.remove('hidden');
            placeholderQR.classList.add('hidden');

            btn.className = "px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-bold text-sm rounded-xl transition-all active:scale-95 shadow-md flex items-center gap-2";
            btn.innerHTML = `
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10"/>
                </svg>
                <span>Matikan Kamera</span>
            `;

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            isCameraActive = true;
            lastScannedCode = '';
            scanCooldown = false;

            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 12,
                    qrbox: { width: 240, height: 240 },
                    aspectRatio: 1.0,
                    formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
                },
                (decodedText) => {
                    if (scanCooldown) return;
                    if (decodedText === lastScannedCode) {
                        return;
                    }

                    lastScannedCode = decodedText;
                    scanCooldown = true;
                    setTimeout(() => { scanCooldown = false; }, 3000);

                    playBeep();

                    if (navigator.vibrate) {
                        navigator.vibrate(150);
                    }

                    processBarcode(decodedText);
                },
                (errorMessage) => {
                }
            ).catch(err => {
                isCameraActive = false;
                reader.classList.add('hidden');
                laser.classList.add('hidden');
                placeholderQR.classList.remove('hidden');

                btn.className = "px-5 py-2.5 bg-primary hover:bg-primaryDark text-white font-bold text-sm rounded-xl transition-all active:scale-95 shadow-md flex items-center gap-2";
                btn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <span>Aktifkan Kamera</span>
                `;

                Swal.fire({
                    icon: 'error',
                    title: 'Kamera Gagal',
                    text: 'Tidak dapat mengakses kamera. Pastikan izin kamera telah diberikan.',
                }).then(() => {
                    barcodeInput.focus();
                });
                console.error("Gagal menjalankan scanner", err);
            });
        }
    }

    function playBeep() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const fontGain = audioCtx.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
            fontGain.gain.setValueAtTime(0.1, audioCtx.currentTime);

            oscillator.connect(fontGain);
            fontGain.connect(audioCtx.destination);

            oscillator.start();
            setTimeout(() => {
                oscillator.stop();
                audioCtx.close();
            }, 100);
        } catch (e) {
            console.warn("Audio Context not allowed or supported yet.", e);
        }
    }
</script>
@endpush
@endsection
