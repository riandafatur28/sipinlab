<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Polije</title>
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
        <p class="text-center text-gray-600 mb-8">Buat password baru</p>

        <!-- Form -->
        <form method="POST" action="{{ route('password.update') }}" class="space-y-6 bg-white p-8 rounded-2xl shadow-xl">
            @csrf

            <!-- New Password Input -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Password Baru
                </label>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       autofocus
                       autocomplete="new-password"
                       class="w-full px-4 py-3 bg-gray-100 border-2 border-transparent rounded-full focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all duration-200 @error('password') border-red-500 bg-red-50 @enderror">
                @error('password')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">Minimal 8 karakter, huruf besar, huruf kecil, angka, dan simbol</p>
            </div>

            <!-- Confirm Password Input -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Konfirmasi Password
                </label>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       class="w-full px-4 py-3 bg-gray-100 border-2 border-transparent rounded-full focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all duration-200">
            </div>

            <!-- Confirm Button -->
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 px-4 rounded-full transition-all duration-200 ease-in-out transform hover:scale-[1.02] hover:shadow-lg flex items-center justify-center group">
                <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Konfirmasi
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                © {{ date('Y') }} Politeknik Negeri Jember
            </p>
        </div>
    </div>
</body>
</html>
