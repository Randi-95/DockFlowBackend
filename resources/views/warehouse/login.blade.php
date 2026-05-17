<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Warehouse — DockFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#1565C0', primaryLight: '#42a5f5', textDark: '#0f172a', textMuted: '#64748b' },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    boxShadow: { 'soft': '0 25px 50px -12px rgba(21,101,192,0.08)', 'glow': '0 0 40px rgba(21,101,192,0.15)' }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f6ff; min-height: 100vh;
               background-image: radial-gradient(circle at 50% 0%, rgba(21,101,192,0.04) 0%, transparent 70%); }
        .dots-bg { background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px; }
        .glass-panel { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        @keyframes ripple {
            0%   { box-shadow: 0 0 0 0 rgba(21,101,192,0.2), 0 0 0 20px rgba(21,101,192,0.1), 0 0 0 40px rgba(21,101,192,0.05); }
            100% { box-shadow: 0 0 0 20px rgba(21,101,192,0), 0 0 0 40px rgba(21,101,192,0), 0 0 0 60px rgba(21,101,192,0); }
        }
        .ripple-effect { animation: ripple 2.5s infinite cubic-bezier(0.65,0,0.34,1); border-radius: 50%; }
        @keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .spin-slow { animation: spin-slow 40s linear infinite; }
    </style>
</head>
<body class="antialiased font-sans text-textDark flex flex-col justify-between min-h-screen overflow-hidden relative">

    <svg class="absolute bottom-0 left-0 w-full h-3/5 -z-10 opacity-20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
        <path fill="none" stroke="#1565C0" stroke-width="0.8" d="M0,128L48,138.7C96,149,192,171,288,181.3C384,192,480,192,576,176C672,160,768,128,864,122.7C960,117,1056,139,1152,154.7C1248,171,1344,181,1392,186.7L1440,192"/>
        <path fill="none" stroke="#1565C0" stroke-width="0.5" d="M0,96L48,117.3C96,139,192,181,288,197.3C384,213,480,203,576,176C672,149,768,107,864,96C960,85,1056,107,1152,117.3C1248,128,1344,128,1392,128L1440,128"/>
    </svg>
    <div class="absolute left-16 top-1/3 w-24 h-48 dots-bg opacity-30 -z-10"></div>
    <div class="absolute right-16 top-1/3 w-24 h-48 dots-bg opacity-30 -z-10"></div>

    <header class="w-full flex justify-between items-center px-10 py-8 z-10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-primary rounded-lg flex items-center justify-center shadow-sm">
                <svg width="20" height="20" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-primary tracking-tight">DockFlow</h1>
                <p class="text-xs text-textMuted font-medium">Warehouse Admin System</p>
            </div>
        </div>
        <div class="glass-panel border border-blue-50/60 rounded-full px-6 py-3 flex items-center gap-4 shadow-soft">
            <div class="flex items-center gap-2 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span id="currentTime" class="text-lg font-bold tracking-wide">00:00:00</span>
            </div>
            <div class="w-px h-5 bg-gray-200"></div>
            <div id="currentDate" class="text-sm text-primaryLight font-medium">Memuat tanggal...</div>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center px-4 z-10 -mt-8">
        <div class="text-center mb-10">
            <h2 class="text-5xl font-extrabold text-primary mb-4 tracking-tight">Warehouse Admin</h2>
            <p class="text-lg text-textMuted font-medium">Tempelkan kartu RFID Anda untuk masuk ke sistem gudang</p>
        </div>

        <div class="glass-panel border border-white/60 rounded-[2.5rem] p-10 md:px-16 md:py-14 shadow-soft flex flex-col items-center max-w-2xl w-full mx-auto">

            <div class="relative w-[280px] h-[280px] flex items-center justify-center mb-10 mt-2">
                <div class="absolute inset-0 rounded-full border border-blue-50/80"></div>
                <div class="absolute inset-[20px] rounded-full border border-blue-100/60"></div>
                <div class="absolute inset-[40px] rounded-full border border-blue-300/40 border-dashed spin-slow"></div>
                <div class="absolute inset-[60px] rounded-full border-[1.5px] border-blue-100"></div>
                <div class="absolute w-[140px] h-[140px] bg-white rounded-full flex items-center justify-center border border-blue-50 shadow-glow z-10">
                    <div class="absolute inset-0 rounded-full ripple-effect -z-10 bg-blue-50/40"></div>
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="7" height="7" rx="1" stroke="#1565C0" stroke-width="1.5"/>
                        <rect x="14" y="3" width="7" height="7" rx="1" stroke="#1565C0" stroke-width="1.5"/>
                        <rect x="3" y="14" width="7" height="7" rx="1" stroke="#1565C0" stroke-width="1.5"/>
                        <rect x="5" y="5" width="3" height="3" fill="#1565C0" rx="0.5"/>
                        <rect x="16" y="5" width="3" height="3" fill="#1565C0" rx="0.5"/>
                        <rect x="5" y="16" width="3" height="3" fill="#1565C0" rx="0.5"/>
                        <path d="M14 14h2v2h-2zM16 16h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2z" fill="#1565C0"/>
                    </svg>
                </div>
            </div>

            <h3 class="text-2xl font-bold text-primary mb-2">Siap untuk Login</h3>
            <p class="text-center text-textMuted mb-8 font-medium">Tempelkan kartu RFID Anda<br>pada area reader</p>

            <div class="bg-[#f0f7ff] border border-blue-100/50 rounded-2xl py-3 px-5 flex items-center gap-3 w-full justify-center">
                <div class="bg-amber-400 text-white rounded-full p-1 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-textMuted">Khusus untuk <span class="text-primary font-semibold">Warehouse Admin</span> — bukan untuk Field Crew</span>
            </div>
        </div>
    </main>

    <footer class="w-full py-6 flex justify-center z-10">
        <p class="text-sm text-textMuted font-medium">DockFlow Warehouse System &copy; {{ date('Y') }}</p>
    </footer>

    <input type="text" id="rfidInput" autofocus autocomplete="off"
           class="fixed -bottom-full opacity-0 pointer-events-none w-1"
           placeholder="rfid">

    <div id="simPanel" class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 w-full max-w-sm px-4">
        <div class="bg-white/95 backdrop-blur-md border border-amber-200 rounded-2xl shadow-xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-5 h-5 bg-amber-400 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg width="10" height="10" fill="white" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Simulasi RFID (Testing)</span>
            </div>
            <div class="flex gap-2">
                <input type="text" id="rfidSimInput" autocomplete="off" placeholder="Ketik RFID UID..."
                       class="flex-1 border-2 border-amber-200 focus:border-amber-400 rounded-xl px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-amber-200 transition-all bg-amber-50/30"
                       onkeypress="if(event.key==='Enter'){ simulateRfid(); }">
                <button onclick="simulateRfid()"
                        class="px-4 py-2 bg-amber-400 hover:bg-amber-500 text-white font-bold text-sm rounded-xl transition-all active:scale-95 shadow-sm">
                    Tap
                </button>
            </div>
            <p class="text-xs text-amber-600/70 mt-2 text-center">Masukkan nilai <code class="font-mono bg-amber-50 px-1 rounded">rfid_uid</code> dari tabel users</p>
        </div>
    </div>

    <script>
        function updateTime() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2,'0');
            const m = String(now.getMinutes()).padStart(2,'0');
            const s = String(now.getSeconds()).padStart(2,'0');
            document.getElementById('currentTime').innerText = `${h}:${m}:${s}`;
            document.getElementById('currentDate').innerText = now.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
        }
        setInterval(updateTime, 1000);
        updateTime();

        const rfidInput = document.getElementById('rfidInput');
        const rfidSimInput = document.getElementById('rfidSimInput');
        const refocus = (e) => {
            if (e && (e.target === rfidSimInput || e.target.closest('#simPanel'))) return;
            rfidInput.focus();
        };
        document.addEventListener('click', refocus);
        document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') refocus(); });
        refocus();

        let rfidBuffer = '';
        let rfidTimer = null;

        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                if (rfidBuffer.trim().length > 0) {
                    handleRfid(rfidBuffer.trim());
                    rfidBuffer = '';
                }
            } else {
                rfidBuffer += e.key;
                clearTimeout(rfidTimer);
                rfidTimer = setTimeout(() => { rfidBuffer = ''; }, 500);
            }
        });

        async function handleRfid(rfidValue) {
            Swal.fire({ title: 'Memverifikasi...', text: 'Mohon tunggu', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const res = await fetch('{{ route("warehouse.login.submit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ rfid: rfidValue })
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: `Selamat datang, ${data.user_name}!`,
                        text: 'Mengalihkan ke halaman antrean...',
                        showConfirmButton: false,
                        timer: 1500,
                    }).then(() => { window.location.href = data.redirect; });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: data.message || 'Kartu tidak dikenali.',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true,
                    });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Tidak dapat menghubungi server.' });
            }
        }

        function simulateRfid() {
            const val = document.getElementById('rfidSimInput').value.trim();
            if (!val) return;
            document.getElementById('rfidSimInput').value = '';
            handleRfid(val);
        }
    </script>
</body>
</html>
