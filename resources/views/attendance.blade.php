<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi RFID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1565C0',
                        primaryLight: '#42a5f5',
                        textDark: '#0f172a',
                        textMuted: '#64748b',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 25px 50px -12px rgba(21, 101, 192, 0.08)',
                        'inner-soft': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.02)',
                        'glow': '0 0 40px rgba(21, 101, 192, 0.15)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            background-image: radial-gradient(circle at 50% 0%, rgba(21, 101, 192, 0.03) 0%, transparent 70%);
        }
        
        .dots-bg {
            background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        @keyframes ripple {
            0% {
                box-shadow: 0 0 0 0 rgba(21, 101, 192, 0.2),
                            0 0 0 20px rgba(21, 101, 192, 0.1),
                            0 0 0 40px rgba(21, 101, 192, 0.05);
            }
            100% {
                box-shadow: 0 0 0 20px rgba(21, 101, 192, 0),
                            0 0 0 40px rgba(21, 101, 192, 0),
                            0 0 0 60px rgba(21, 101, 192, 0);
            }
        }
        
        .ripple-effect {
            animation: ripple 2.5s infinite cubic-bezier(0.65, 0, 0.34, 1);
            border-radius: 50%;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="antialiased relative overflow-hidden font-sans text-textDark flex flex-col justify-between min-h-screen">
    
    <svg class="absolute bottom-0 left-0 w-full h-3/5 object-cover -z-10 opacity-[0.25]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
        <path fill="none" stroke="#1565C0" stroke-width="0.8" d="M0,128L48,138.7C96,149,192,171,288,181.3C384,192,480,192,576,176C672,160,768,128,864,122.7C960,117,1056,139,1152,154.7C1248,171,1344,181,1392,186.7L1440,192"></path>
        <path fill="none" stroke="#1565C0" stroke-width="0.6" d="M0,160L48,149.3C96,139,192,117,288,122.7C384,128,480,160,576,176C672,192,768,192,864,170.7C960,149,1056,107,1152,90.7C1248,75,1344,85,1392,90.7L1440,96"></path>
        <path fill="none" stroke="#1565C0" stroke-width="0.4" d="M0,224L48,213.3C96,203,192,181,288,160C384,139,480,117,576,122.7C672,128,768,160,864,165.3C960,171,1056,149,1152,133.3C1248,117,1344,107,1392,101.3L1440,96"></path>
        <path fill="none" stroke="#1565C0" stroke-width="0.5" d="M0,96L48,117.3C96,139,192,181,288,197.3C384,213,480,203,576,176C672,149,768,107,864,96C960,85,1056,107,1152,117.3C1248,128,1344,128,1392,128L1440,128"></path>
        <path fill="none" stroke="#1565C0" stroke-width="0.7" d="M0,64L48,85.3C96,107,192,149,288,165.3C384,181,480,171,576,144C672,117,768,75,864,64C960,53,1056,75,1152,85.3C1248,96,1344,96,1392,96L1440,96"></path>
    </svg>

    <div class="absolute left-16 top-1/3 w-24 h-48 dots-bg opacity-30 -z-10"></div>
    <div class="absolute right-16 top-1/3 w-24 h-48 dots-bg opacity-30 -z-10"></div>

    <header class="w-full flex justify-between items-center px-10 py-8 z-10">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-primary tracking-tight">DockFlow</h1>
                <p class="text-sm text-textMuted font-medium">Absensi Pekerja</p>
            </div>
        </div>

        <div class="glass-panel border border-blue-50/60 rounded-full px-6 py-3 flex items-center gap-5 shadow-soft">
            <div class="flex items-center gap-2.5 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span id="currentTime" class="text-xl font-bold tracking-wide">00:00:00</span>
            </div>
            <div class="w-px h-6 bg-gray-200"></div>
            <div id="currentDate" class="text-sm text-primaryLight font-medium">
                Memuat tanggal...
            </div>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center px-4 z-10 -mt-8">
        <div class="text-center mb-10">
            <h2 class="text-5xl font-extrabold text-primary mb-4 tracking-tight">Selamat Datang!</h2>
            <p class="text-lg text-textMuted font-medium">Tempelkan kartu RFID Anda pada reader untuk absen</p>
        </div>

        <div class="glass-panel border border-white/60 rounded-[2.5rem] p-10 md:px-16 md:py-14 shadow-soft flex flex-col items-center max-w-2xl w-full mx-auto relative transition-transform duration-500 hover:shadow-[0_30px_60px_-15px_rgba(21,101,192,0.12)] hover:-translate-y-1">
            
            <div class="relative w-[320px] h-[320px] flex items-center justify-center mb-10 mt-2">
                <div class="absolute inset-0 rounded-full border border-blue-50/80"></div>
                <div class="absolute inset-[20px] rounded-full border border-blue-100/60"></div>
                <div class="absolute inset-[40px] rounded-full border border-blue-300/40 border-dashed animate-[spin_40s_linear_infinite]"></div>
                <div class="absolute inset-[60px] rounded-full border-[1.5px] border-blue-100"></div>
                <div class="absolute inset-[75px] rounded-full border border-blue-50"></div>
                
                <div class="absolute w-[160px] h-[160px] bg-white rounded-full flex items-center justify-center border border-blue-50 shadow-glow z-10">
                    <div class="absolute inset-0 rounded-full ripple-effect -z-10 bg-blue-50/40"></div>
                    
                    <svg width="76" height="76" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="ml-2">
                        <path d="M7 4H12C13.1046 4 14 4.89543 14 6V18C14 19.1046 13.1046 20 12 20H7C5.89543 20 5 19.1046 5 18V6C5 4.89543 5.89543 4 7 4Z" stroke="#1565C0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.5 7H9.51" stroke="#1565C0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 11C8 11 8.66667 10.3333 9.5 10.3333C10.3333 10.3333 11 11 11 11" stroke="#1565C0" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.75 13C8.75 13 9 12.6667 9.5 12.6667C10 12.6667 10.25 13 10.25 13" stroke="#1565C0" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.5 17H11.5" stroke="#1565C0" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        
                        <path d="M16 10L12 13" stroke="#1565C0" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="13" y="10.5" width="9" height="5.5" rx="1" transform="rotate(-35 13 10.5)" fill="white" stroke="#1565C0" stroke-width="1.5" stroke-linejoin="round"/>
                        
                        <path d="M18 15.5C18.5 16 19.5 17 19.5 18.5V21" stroke="#1565C0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 15C16 16.5 17 18 17 18.5" stroke="#1565C0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <h3 class="text-3xl font-bold text-primary mb-3">Siap untuk Absen</h3>
            <p class="text-center text-textMuted mb-10 font-medium text-lg">Tempelkan kartu RFID Anda<br>pada area reader</p>

            <div class="bg-[#f0f7ff] border border-blue-100/50 rounded-2xl py-3.5 px-6 flex items-center gap-3.5 w-full justify-center transition-colors hover:bg-blue-50/80">
                <div class="bg-primary text-white rounded-full p-1 flex-shrink-0 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </div>
                <span class="text-sm font-medium text-primary">Pastikan kartu RFID menempel dengan benar</span>
            </div>
        </div>
    </main>

    <footer class="w-full py-8 flex justify-center z-10 mt-auto">
        <div class="flex items-center gap-8 text-sm text-primary font-medium opacity-90">
            <div class="flex items-center gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <polyline points="9 12 11 14 15 10"></polyline>
                </svg>
                Absensi Anda Aman
            </div>
            <div class="w-[1px] h-4 bg-blue-200"></div>
            <div class="flex items-center gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Data terlindungi dengan aman
            </div>
        </div>
    </footer>

    <form id="rfidForm" method="POST" action="/attendance/scan" class="absolute bottom-24 left-1/2 transform -translate-x-1/2 z-50 bg-white/90 backdrop-blur-md p-4 rounded-xl shadow-lg border border-blue-200 flex flex-col items-center gap-2 transition-all">
        @csrf
        <label for="rfidInput" class="text-xs font-bold text-primary uppercase tracking-wider">Simulasi Input RFID (Testing)</label>
        <input type="text" id="rfidInput" name="rfid" autofocus autocomplete="off" class="w-64 px-4 py-2 border border-gray-300 rounded-lg text-center font-mono focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary placeholder-gray-400" placeholder="Ketik UID & tekan Enter...">
    </form>

    <script>
        function updateTime() {
            const now = new Date();
            
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('currentTime').innerText = `${h}:${m}:${s}`;

            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            const dateStr = now.toLocaleDateString('id-ID', options);
            document.getElementById('currentDate').innerText = dateStr;
        }

        setInterval(updateTime, 1000);
        updateTime(); 

        const rfidInput = document.getElementById('rfidInput');
        const rfidForm = document.getElementById('rfidForm');

        document.addEventListener('click', () => {
            rfidInput.focus();
        });
        
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                rfidInput.focus();
            }
        });

        rfidForm.addEventListener('submit', async function(e) {
            e.preventDefault(); 
            
            const rfidValue = rfidInput.value;
            if (!rfidValue) return;
            
            rfidInput.value = '';
            rfidInput.focus();

            try {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const response = await fetch('/attendance/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ rfid: rfidValue })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (data.action === 'check_in') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Check-In',
                            html: `Selamat datang, <b>${data.user_name}</b>!<br>Waktu: ${document.getElementById('currentTime').innerText}`,
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true,
                        });
                    } else if (data.action === 'check_out') {
                        Swal.fire({
                            icon: 'success',
                            iconColor: '#1e88e5', 
                            title: 'Berhasil Check-Out',
                            html: `Hati-hati di jalan, <b>${data.user_name}</b>!<br>Waktu: ${document.getElementById('currentTime').innerText}`,
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true,
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Kartu tidak dikenali atau terjadi kesalahan.',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true,
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat menghubungi server database.',
                    showConfirmButton: true,
                });
            }
        });
    </script>
</body>
</html>
