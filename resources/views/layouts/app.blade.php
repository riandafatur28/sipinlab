<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - Polije')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        /* --- SIDEBAR BASE STYLES --- */
        .sidebar {
            position: fixed;
            top: 0; left: 0; height: 100vh; width: 16rem;
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
            z-index: 50;
            transition: transform 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar Collapsed State (Desktop Only) */
        @media (min-width: 1024px) {
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            .sidebar.collapsed > * {
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.15s;
            }
        }

        /* Sidebar Edge Handle */
        .sidebar-edge {
            position: fixed;
            top: 0;
            left: 16rem;
            width: 12px;
            height: 100vh;
            cursor: col-resize;
            z-index: 60;
            transition: all 0.3s ease-in-out;
            opacity: 0;
            display: none;
        }

        @media (min-width: 1024px) {
            .sidebar-edge { display: block; }
            .sidebar-edge:hover, .sidebar-edge.active {
                background: linear-gradient(to right, rgba(59, 130, 246, 0.4), transparent);
                opacity: 1;
            }
            .sidebar.collapsed + .sidebar-edge { left: 0; }
        }

        /* Hide scrollbar for sidebar */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.1); }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.3); border-radius: 4px; }

        /* Main Content - FIXED */
        .main-content {
            margin-left: 16rem;
            transition: margin-left 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            z-index: 10;
        }

        .main-content.full-width {
            margin-left: 0;
        }

        /* Mobile Sidebar Hidden by default */
        .sidebar.sidebar-hidden {
            transform: translateX(-100%);
        }
        .sidebar.sidebar-visible {
            transform: translateX(0);
        }

        /* ======================================== */
        /* ✨ PERBAIKAN MOBILE RESPONSIVE ✨ */
        /* ======================================== */
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 4px 0 15px rgba(0,0,0,0.2);
            }

            .sidebar.sidebar-visible {
                transform: translateX(0);
            }

            .mobile-menu-toggle {
                display: flex !important;
            }

            .overlay-mobile {
                display: none;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .overlay-mobile.active {
                display: block;
                opacity: 1;
            }

            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        @media (min-width: 1024px) {
            .mobile-menu-toggle {
                display: none !important;
            }
        }

        /* Keyboard Hint */
        .keyboard-hint {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(30, 41, 59, 0.95);
            color: white;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .keyboard-hint.show {
            opacity: 1;
            transform: translateY(0);
        }
        .keyboard-hint kbd {
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 8px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
        }

        /* Text Hiding */
        .sidebar-text {
            transition: opacity 0.2s;
        }
        @media (min-width: 1024px) {
            .sidebar.collapsed .sidebar-text {
                opacity: 0;
            }
        }

        /* Kalab Badge Animation */
        @keyframes pulse-kalab {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(99, 102, 241, 0); }
        }
        .badge-kalab {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            animation: pulse-kalab 2s infinite;
        }

        /* Role Badges */
        .badge-role {
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
        }
        .badge-role-mahasiswa { background: #fef3c7; color: #92400e; }
        .badge-role-dosen { background: #dcfce7; color: #166534; }
        .badge-role-teknisi { background: #dbeafe; color: #1e40af; }
        .badge-role-admin { background: #fee2e2; color: #991b1b; }
        .badge-role-kalab { background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; }
    </style>
</head>
<body class="bg-yellow-50">

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay"
         class="fixed inset-0 bg-black/50 z-40 lg:hidden overlay-mobile"
         onclick="handleMobileClose()"></div>

    <!-- ✅ Sidebar Edge Handle -->
    <div id="sidebar-edge" class="sidebar-edge hidden lg:block" title="Klik atau hover untuk toggle sidebar"></div>

    <div class="flex">
        <!-- ✅ SIDEBAR (Collapsible) - ✅ FIX: HAPUS sidebar-hidden DARI CLASS DEFAULT -->
        <aside id="sidebar" class="sidebar shadow-xl">

            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-blue-400 flex-shrink-0">
                <div class="flex items-center gap-2 text-white">
                    <img src="{{ asset('img/polije.png') }}"
                     alt="Logo Polije"
                     class="w-12 h-12 object-contain transition duration-300 hover:scale-110">
                    <span class="text-xl font-bold sidebar-text whitespace-nowrap">SiPinLab</span>
                </div>
                <!-- Tombol close mobile -->
                <button onclick="closeSidebarMobile()" class="lg:hidden ml-auto text-white text-xl">✕</button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-blue-600 transition-colors {{ request()->routeIs('dashboard*') ? 'bg-blue-600' : '' }}">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="font-medium sidebar-text whitespace-nowrap">Dashboard</span>
                </a>

                <!-- ✅ Booking Menu dengan Logic Kalab -->
                @php
                    $user = auth()->user();
                    $pendingCount = 0;

                    if ($user->isKalab() || $user->role === 'ketua_lab') {
                        $pendingCount = \App\Models\Booking::where('status', 'approved_teknisi')->count();
                    } elseif ($user->isDosen()) {
                        $pendingCount = \App\Models\Booking::whereHas('user', fn($q) => $q->where('role', 'mahasiswa'))
                            ->where('status', 'pending')
                            ->count();
                    } elseif ($user->isTeknisi()) {
                        $pendingCount = \App\Models\Booking::where('status', 'approved_dosen')->count();
                    }
                @endphp

                <a href="{{ route('booking.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-blue-600 transition-colors {{ request()->routeIs('booking.*') ? 'bg-blue-600' : '' }}">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium sidebar-text whitespace-nowrap">Booking</span>
                    @if($pendingCount > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full animate-pulse sidebar-text">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>


                <!-- ✅ MANAGEMENT SECTION: Admin, Kalab, Teknisi -->
                @if($user->isAdmin() || $user->isKalab() || $user->isTeknisi() || $user->role === 'ketua_lab')
                    <div class="pt-6 pb-2 mt-4 border-t border-blue-400 sidebar-text">
                        <p class="px-4 text-xs font-semibold text-blue-200 uppercase tracking-wider whitespace-nowrap">
                            @if($user->isAdmin())
                                ⚙️ Management
                            @elseif($user->isKalab() || $user->role === 'ketua_lab')
                                👔 Management Kalab
                            @else
                                🔧 Management Teknisi
                            @endif
                        </p>
                    </div>

                    {{-- ✅ Kelola User - HANYA ADMIN --}}
                    @if($user->isAdmin())
                        <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-blue-600 transition-colors">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <span class="font-medium sidebar-text whitespace-nowrap">Tambah User</span>
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-blue-600 transition-colors">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="font-medium sidebar-text whitespace-nowrap">Kelola User</span>
                        </a>
                    @endif

                    {{-- ✅ Kelola Lab - Admin, Kalab, Teknisi --}}
                    <a href="{{ route('admin.labs.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-blue-600 transition-colors">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="font-medium sidebar-text whitespace-nowrap">Kelola Lab</span>
                    </a>

                    {{-- ✅ Kelola Booking - Admin, Kalab, Teknisi --}}
                    <a href="{{ route('admin.schedule.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-blue-600 transition-colors relative">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium sidebar-text whitespace-nowrap">Kelola Booking</span>
                        @if($user->isKalab())
                            @php
                                $kalabPendingCount = \App\Models\Booking::where('status', 'approved_teknisi')->count();
                            @endphp
                            @if($kalabPendingCount > 0)
                                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full sidebar-text animate-pulse">
                                    {{ $kalabPendingCount }}
                                </span>
                            @endif
                        @endif
                    </a>

                    {{-- ✅ Jadwal Kuliah - HANYA ADMIN --}}
                    @if($user->isAdmin())
                        <a href="{{ route('admin.class-schedules.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-blue-600 transition-colors">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <span class="font-medium sidebar-text whitespace-nowrap">Jadwal Kuliah</span>
                        </a>
                    @endif
                @endif

            </nav>

            <!-- ✅ Profile & Logout dengan Badge Role -->
            <div class="p-4 border-t border-blue-400 mt-auto flex-shrink-0">
                <!-- Profile -->
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-10 h-10 rounded-full bg-blue-400 flex items-center justify-center font-bold text-white flex-shrink-0">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0 sidebar-text">
                        <p class="font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                        <div class="flex items-center gap-2">
                            <p class="text-xs text-blue-200 truncate">
                                {{ ucfirst(Auth::user()->role) }}
                            </p>
                            @if(Auth::user()->isKalab())
                                <span class="badge-kalab">👔 Kalab</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="w-full mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-600 transition-colors text-white text-left text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="sidebar-text whitespace-nowrap">Logout</span>
                    </button>
                </form>
            </div>

        </aside>

        <!-- ✅ MAIN CONTENT -->
        <div id="main-content" class="main-content flex-1 min-w-0">

            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30 shadow-sm flex-shrink-0">

                <!-- Mobile Menu Toggle Button -->
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg mobile-menu-toggle">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Spacer untuk desktop -->
                <div class="hidden lg:block w-10"></div>

                <div class="flex items-center gap-4 ml-auto">
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        @if($pendingCount > 0)
                            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white animate-ping"></span>
                            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                        @endif
                    </button>

                    <!-- ✅ User Dropdown dengan Badge Role -->
                    <div class="relative">
                        <button onclick="toggleUserDropdown()" class="flex items-center gap-2 p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="hidden md:flex flex-col items-start">
                                <span class="text-sm font-medium text-gray-700 sidebar-text">{{ Auth::user()->name }}</span>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-gray-500 sidebar-text">{{ ucfirst(Auth::user()->role) }}</span>
                                    @if(Auth::user()->isKalab())
                                        <span class="badge-kalab text-[10px] px-1.5 py-0.5">👔</span>
                                    @endif
                                </div>
                            </div>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <!-- Profile Info in Dropdown -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</span>
                                    @if(Auth::user()->isKalab())
                                        <span class="badge-kalab text-[10px] px-1.5 py-0.5">👔 Kalab</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-1 truncate">{{ Auth::user()->email }}</p>
                            </div>

                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">👤 Profil Saya</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">⚙️ Pengaturan</a>

                            @if(Auth::user()->isKalab())
                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">👔 Kelola User (Kalab)</a>
                            @endif

                            <hr class="my-1">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">🚪 Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-8 overflow-y-auto">
                {{-- ✅ Flash Messages --}}
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {!! session('success') !!}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {!! session('error') !!}
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 px-6 py-4 mt-auto flex-shrink-0">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <p>&copy; {{ date('Y') }} Politeknik Negeri Jember - SiPinLab</p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="hover:text-blue-600 transition-colors">Bantuan</a>
                        <a href="#" class="hover:text-blue-600 transition-colors">Privasi</a>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    <!-- Keyboard Shortcut Hint -->
    <div id="keyboard-hint" class="keyboard-hint">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>Toggle sidebar:</span>
        <kbd>Ctrl</kbd> + <kbd>B</kbd>
    </div>

<script>
    // Toggle sidebar (works on mobile AND desktop)
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const isMobile = window.innerWidth < 1024;

        if (isMobile) {
            // Mode Mobile: Toggle visibility
            if (sidebar.classList.contains('sidebar-hidden')) {
                sidebar.classList.remove('sidebar-hidden');
                sidebar.classList.add('sidebar-visible');
                overlay?.classList.add('active');
            } else {
                closeSidebarMobile();
            }
        } else {
            // Mode Desktop: Toggle collapsed
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        showKeyboardHint();
    }

    // Fungsi untuk close sidebar mobile
    function closeSidebarMobile() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        sidebar.classList.remove('sidebar-visible');
        sidebar.classList.add('sidebar-hidden');
        overlay?.classList.remove('active');
    }

    // Handle mobile close via overlay click
    function handleMobileClose() {
        closeSidebarMobile();
    }

    function showKeyboardHint() {
        const hint = document.getElementById('keyboard-hint');
        hint.classList.add('show');
        setTimeout(() => hint.classList.remove('show'), 2500);
    }

    function toggleUserDropdown() {
        document.getElementById('user-dropdown').classList.toggle('hidden');
    }

    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('user-dropdown');
        const button = event.target.closest('button');
        if (!button || !button.onclick?.toString().includes('toggleUserDropdown')) {
            dropdown?.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') {
            const target = event.target;
            if (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA' && !target.isContentEditable) {
                event.preventDefault();
                toggleSidebar();
            }
        }
    });

    document.getElementById('sidebar-edge')?.addEventListener('click', toggleSidebar);

    const sidebarEdge = document.getElementById('sidebar-edge');
    sidebarEdge?.addEventListener('mouseenter', function() {
        showKeyboardHint();
    });

    // ✅ Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const isMobile = window.innerWidth < 1024;

        // Set initial state berdasarkan screen size
        if (isMobile) {
            // Mobile: sidebar hidden by default
            sidebar.classList.add('sidebar-hidden');
            sidebar.classList.remove('collapsed');
            overlay?.classList.remove('active');
        } else {
            // Desktop: sidebar visible by default (unless collapsed)
            sidebar.classList.remove('sidebar-hidden');
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
            overlay?.classList.remove('active');
        }

        // Show hint on first visit
        if (!sessionStorage.getItem('sidebarHintShown')) {
            setTimeout(() => {
                showKeyboardHint();
                sessionStorage.setItem('sidebarHintShown', 'true');
            }, 1500);
        }
    });

    // ✅ Handle window resize - FIX: Auto-show sidebar saat kembali ke desktop
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const isMobile = window.innerWidth < 1024;

        if (isMobile) {
            // Switching to mobile - hide sidebar dan reset collapsed state
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('sidebar-hidden');
            overlay?.classList.remove('active');
            // Reset localStorage saat masuk mobile
            localStorage.removeItem('sidebarCollapsed');
        } else {
            // Switching to desktop - ALWAYS show sidebar (reset collapsed state)
            sidebar.classList.remove('sidebar-hidden', 'sidebar-visible');
            sidebar.classList.remove('collapsed');
            overlay?.classList.remove('active');
            // Reset localStorage agar default visible
            localStorage.removeItem('sidebarCollapsed');
        }
    });
</script>

    @stack('scripts')
</body>
</html>
