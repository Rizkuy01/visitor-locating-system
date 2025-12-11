<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>@yield('title', 'Visitor Locating System')</title>
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-100 to-slate-200 overflow-x-hidden">
    <div class="min-h-screen flex">

        {{-- SIDEBAR (desktop) --}}
        <aside class="hidden md:flex md:flex-col md:w-56 shrink-0 bg-white text-slate-900 border-r border-slate-200">
            <div class="h-16 flex items-center px-4 border-b border-slate-200">
                {{-- Logo tanpa background & border --}}
                <div class="h-10 flex items-center">
                    <img src="/logo-kyb.png" alt="Logo" class="h-8 w-auto object-contain">
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 text-sm font-semibold">
                {{-- Security Desk --}}
                <a href="{{ route('desk') }}" class="flex items-center gap-2 rounded-xl px-3 py-2
               {{ request()->routeIs('desk') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span class="w-5 h-5">
                        {{-- Icon chart (inline SVG, offline) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" class="w-5 h-5">
                            <path d="M3 13a9 9 0 0 1 9-9v9H3z" />
                            <path d="M12 3a9 9 0 1 1-6.364 15.364L12 12V3z" />
                        </svg>
                    </span>
                    <span>Security Desk</span>
                </a>

                {{-- Daftar Visitor --}}
                <a href="{{ route('visitors.index') }}"
                    class="flex items-center gap-2 rounded-xl px-3 py-2
               {{ request()->routeIs('visitors.index') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span class="w-5 h-5">
                        {{-- Icon list --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" class="w-5 h-5">
                            <path d="M4 6h3M4 12h3M4 18h3" />
                            <rect x="9" y="5" width="11" height="2" rx="1" />
                            <rect x="9" y="11" width="11" height="2" rx="1" />
                            <rect x="9" y="17" width="11" height="2" rx="1" />
                        </svg>
                    </span>
                    <span>Daftar Visitor</span>
                </a>

                {{-- Scan / Check-in --}}
                <a href="{{ route('visitors.scan.page') }}"
                    class="flex items-center gap-2 rounded-xl px-3 py-2
               {{ request()->routeIs('visitors.scan.page') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span class="w-5 h-5">
                        {{-- Icon scan / QR --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" class="w-5 h-5">
                            <path d="M4 4h4v4H4zM16 4h4v4h-4zM4 16h4v4H4zM16 16h4v4h-4z" />
                            <path d="M9 12h6" />
                        </svg>
                    </span>
                    <span>Scan / Check-in</span>
                </a>
            </nav>

            <div class="px-4 py-3 text-[11px] text-slate-400 border-t border-slate-200">
                Visitor Locating System
            </div>
        </aside>

        {{-- WRAPPER KONTEN (kanan) --}}
        <div class="flex-1 flex flex-col">

            {{-- TOPBAR (mobile) --}}
            <header class="md:hidden h-14 flex items-center justify-between px-4 bg-slate-900 text-white">
                <button id="sidebarToggle" class="p-2 rounded-lg bg-slate-800/80 font-semibold">
                    ☰
                </button>
                <div class="flex items-center gap-2">
                    <div class="h-8 flex items-center">
                        <img src="/logo-kyb.png" alt="Logo" class="h-6 w-auto object-contain">
                    </div>
                </div>
            </header>

            {{-- SIDEBAR MOBILE (slide) --}}
            <div id="mobileSidebar" class="fixed inset-0 z-40 md:hidden hidden">
                <div class="absolute inset-0 bg-black/40"></div>
                <div class="relative h-full w-64 bg-white text-slate-900 shadow-xl">
                    <div class="h-14 flex items-center justify-between px-4 border-b border-slate-200">
                        <span class="text-sm font-semibold">Menu</span>
                        <button id="sidebarClose" class="text-lg">&times;</button>
                    </div>

                    <nav class="px-3 py-4 space-y-1 text-sm font-semibold">
                        <a href="{{ route('desk') }}"
                            class="flex items-center gap-2 rounded-xl px-3 py-2
                       {{ request()->routeIs('desk') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            <span class="w-5 h-5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                                    <path d="M3 13a9 9 0 0 1 9-9v9H3z" />
                                    <path d="M12 3a9 9 0 1 1-6.364 15.364L12 12V3z" />
                                </svg>
                            </span>
                            <span>Security Desk</span>
                        </a>

                        <a href="{{ route('visitors.index') }}"
                            class="flex items-center gap-2 rounded-xl px-3 py-2
                       {{ request()->routeIs('visitors.index') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            <span class="w-5 h-5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                                    <path d="M4 6h3M4 12h3M4 18h3" />
                                    <rect x="9" y="5" width="11" height="2" rx="1" />
                                    <rect x="9" y="11" width="11" height="2" rx="1" />
                                    <rect x="9" y="17" width="11" height="2" rx="1" />
                                </svg>
                            </span>
                            <span>Daftar Visitor</span>
                        </a>

                        <a href="{{ route('visitors.scan.page') }}"
                            class="flex items-center gap-2 rounded-xl px-3 py-2
                       {{ request()->routeIs('visitors.scan.page') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            <span class="w-5 h-5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                                    <path d="M4 4h4v4H4zM16 4h4v4h-4zM4 16h4v4H4zM16 16h4v4h-4z" />
                                    <path d="M9 12h6" />
                                </svg>
                            </span>
                            <span>Scan / Check-in</span>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- MAIN CONTENT --}}
            <main class="flex-1 min-w-0 px-4 py-6 md:px-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const mobileSidebar = document.getElementById('mobileSidebar');

        if (sidebarToggle && mobileSidebar) {
            sidebarToggle.addEventListener('click', () => {
                mobileSidebar.classList.remove('hidden');
            });
        }

        if (sidebarClose && mobileSidebar) {
            sidebarClose.addEventListener('click', () => {
                mobileSidebar.classList.add('hidden');
            });
            mobileSidebar.addEventListener('click', (e) => {
                if (e.target === mobileSidebar) {
                    mobileSidebar.classList.add('hidden');
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>