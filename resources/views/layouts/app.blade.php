<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Stegano-6SIC3') — Laravel LSB Steganography</title>

    {{-- Tailwind CSS via CDN (ganti dengan Vite di produksi) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Animasi fade-in untuk flash messages */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .flash-msg { animation: slideDown 0.3s ease-out; }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-800">

    {{-- ── Navigation ─────────────────────────────────────────────────────── --}}
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">

                {{-- Logo --}}
                <a href="{{ route('barang.index') }}" class="flex items-center gap-2 font-bold text-xl text-brand-700">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Stegano-6SIC3
                </a>

                {{-- Nav links --}}
                <div class="flex items-center gap-6">
                    @auth
                        <a href="{{ route('barang.index') }}"
                           class="text-sm font-medium text-gray-600 hover:text-brand-600 transition">
                            📦 Barang
                        </a>
                        <a href="{{ route('barang.create') }}"
                           class="text-sm font-medium text-gray-600 hover:text-brand-600 transition">
                            ➕ Tambah
                        </a>
                        <span class="text-gray-300">|</span>
                        <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="text-sm font-medium text-red-500 hover:text-red-700 transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-sm font-medium text-gray-600 hover:text-brand-600 transition">Login</a>
                        <a href="{{ route('register') }}"
                           class="text-sm bg-brand-600 hover:bg-brand-700 text-white font-medium px-4 py-2 rounded-lg transition">
                            Register
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- ── Flash Messages ──────────────────────────────────────────────────── --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-3">

        @if (session('success'))
            <div class="flash-msg flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3">
                <span class="text-lg">✅</span>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flash-msg flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
                <span class="text-lg">❌</span>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('decoded_message'))
            <div class="flash-msg bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-lg">🔓</span>
                    <p class="text-sm font-bold text-indigo-800">Pesan Tersembunyi Berhasil Didekode!</p>
                </div>
                <div class="bg-white border border-indigo-100 rounded-md px-3 py-2">
                    <p class="text-indigo-900 font-mono text-sm break-words">{{ session('decoded_message') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="flash-msg bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
                <p class="text-sm font-bold mb-1">❌ Terdapat kesalahan:</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- ── Main Content ─────────────────────────────────────────────────────── --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    {{-- ── Footer ───────────────────────────────────────────────────────────── --}}
    <footer class="border-t border-gray-100 mt-16 py-6 text-center text-xs text-gray-400">
        Stegano-6SIC3 &copy; {{ date('Y') }} — Laravel 11 + Python LSB Steganography
    </footer>

</body>
</html>
