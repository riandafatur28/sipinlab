<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — SipinLab</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-md px-4">

    <!-- LOGO -->
    <div class="flex justify-center mb-6">
        <div class="bg-white p-4 rounded-full border-2 border-blue-500 shadow-md">
            <img src="{{ asset('img/polije.png') }}" class="w-20 h-20 object-contain"
                 onerror="this.style.display='none'">
        </div>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-xl">

        <h2 class="text-xl font-bold text-gray-800 mb-1">Buat Password Baru</h2>
        <p class="text-sm text-gray-500 mb-6">Password minimal 8 karakter.</p>

        {{-- Pesan error --}}
        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Pesan sukses/info --}}
        @if(session('status'))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            {{-- PASSWORD BARU --}}
            <div class="mb-5">
                <label class="block mb-1.5 text-sm font-semibold text-gray-700">Password Baru</label>
                <div class="relative">
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           autocomplete="new-password"
                           placeholder="Minimal 8 karakter"
                           class="w-full px-4 py-3 pr-12 bg-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border border-red-400 @enderror">
                    <button type="button" onclick="togglePassword('password','eye1','eye1off')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="eye1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eye1off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- KONFIRMASI PASSWORD --}}
            <div class="mb-6">
                <label class="block mb-1.5 text-sm font-semibold text-gray-700">Konfirmasi Password</label>
                <div class="relative">
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="Ulangi password baru"
                           class="w-full px-4 py-3 pr-12 bg-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="togglePassword('password_confirmation','eye2','eye2off')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="eye2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eye2off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 font-semibold transition-colors">
                🔐 Reset Password
            </button>

            <p class="text-center text-sm text-gray-500 mt-4">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">← Kembali ke Login</a>
            </p>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId, eyeId, eyeOffId) {
    const field = document.getElementById(fieldId);
    const eye   = document.getElementById(eyeId);
    const eyeOff = document.getElementById(eyeOffId);
    if (field.type === 'password') {
        field.type = 'text';
        eye.classList.add('hidden');
        eyeOff.classList.remove('hidden');
    } else {
        field.type = 'password';
        eye.classList.remove('hidden');
        eyeOff.classList.add('hidden');
    }
}
</script>

</body>
</html>
