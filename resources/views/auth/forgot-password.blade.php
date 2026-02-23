<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Polije</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Icon -->
        <div class="flex justify-center mb-6">
            <div class="bg-white p-5 rounded-full shadow-lg">
                <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-2">Lupa Password</h1>
        <p class="text-center text-gray-600 mb-6">Masukkan email Polije Anda</p>

        {{-- <!-- Info Domain -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <p class="text-sm text-blue-800">
                <svg class="inline w-4 h-4 mr-1 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <strong>Domain Email Polije:</strong><br>
                <span class="ml-6">• Mahasiswa: <code class="bg-blue-100 px-2 py-0.5 rounded text-xs font-mono">@student.polije.ac.id</code></span><br>
                <span class="ml-6">• Dosen/Staff: <code class="bg-blue-100 px-2 py-0.5 rounded text-xs font-mono">@polije.ac.id</code></span>
            </p>
        </div> --}}

        <!-- Success Message -->
        @if (session('status'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-green-800 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('status') }}
                </p>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6 bg-white p-8 rounded-2xl shadow-xl">
            @csrf

            <!-- Email Input -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email
                </label>
                <input type="email"
                       id="username"
                       name="username"
                       value="{{ old('username') }}"
                       placeholder="nama@student.polije.ac.id"
                       required
                       autofocus
                       autocomplete="email"
                       class="w-full px-4 py-3 bg-gray-100 border-2 border-transparent rounded-full focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all duration-200 @error('username') border-red-500 bg-red-50 @enderror">
                @error('username')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Kirim OTP Button -->
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 px-4 rounded-full transition-all duration-200 ease-in-out transform hover:scale-[1.02] hover:shadow-lg flex items-center justify-center group">
                <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Kirim OTP
            </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Login
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                © {{ date('Y') }} Politeknik Negeri Jember
            </p>
        </div>
    </div>
</body>
</html>
