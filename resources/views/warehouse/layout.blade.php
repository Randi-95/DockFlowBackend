<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Warehouse Admin') — DockFlow</title>
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
                        primaryDark: '#0d47a1',
                        accent: '#00acc1',
                        surface: '#f0f6ff',
                        textDark: '#0f172a',
                        textMuted: '#64748b',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        'soft': '0 25px 50px -12px rgba(21, 101, 192, 0.08)',
                        'glow': '0 0 40px rgba(21, 101, 192, 0.15)',
                        'card': '0 4px 24px rgba(21, 101, 192, 0.07)',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f6ff; }
        .glass { background: rgba(255,255,255,0.92); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeInUp 0.4s ease both; }
        @keyframes scan-line {
            0%   { top: 0; }
            100% { top: 100%; }
        }
        .scan-line { animation: scan-line 1.8s linear infinite; }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(21,101,192,0.3); }
            70%  { box-shadow: 0 0 0 16px rgba(21,101,192,0); }
            100% { box-shadow: 0 0 0 0 rgba(21,101,192,0); }
        }
        .pulse-ring { animation: pulse-ring 2s infinite; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen text-textDark antialiased">

    <header class="glass border-b border-blue-100/60 sticky top-0 z-50 px-6 py-3.5 flex items-center justify-between shadow-soft">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center shadow-sm">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div>
                <span class="text-primary font-bold text-lg leading-none">DockFlow</span>
                <p class="text-xs text-textMuted leading-none mt-0.5">Warehouse System</p>
            </div>
        </div>

        <nav class="flex items-center gap-1">
            <a href="{{ route('warehouse.queue') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('warehouse.queue') ? 'bg-primary text-white shadow-sm' : 'text-textMuted hover:bg-blue-50 hover:text-primary' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                    <line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="12" y2="16"/>
                </svg>
                Antrean
            </a>
            <a href="{{ route('warehouse.handover') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('warehouse.handover') ? 'bg-primary text-white shadow-sm' : 'text-textMuted hover:bg-blue-50 hover:text-primary' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
                Serah Terima
            </a>
        </nav>

        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-semibold text-textDark">{{ $user->name }}</p>
                <p class="text-xs text-textMuted">Warehouse Admin</p>
            </div>
            <div class="w-9 h-9 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold text-sm">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <form method="POST" action="{{ route('warehouse.logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition-all border border-red-100">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </header>

    <main class="min-h-[calc(100vh-65px)]">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
