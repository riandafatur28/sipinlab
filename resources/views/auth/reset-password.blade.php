<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - SiPinLab Polije</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

    <!-- LOGO -->
    <div class="flex justify-center mb-6">
        <div class="bg-white p-4 rounded-full border-2 border-blue-500 shadow-md">
            <img src="{{ asset('img/polije.png') }}"
                 class="w-20 h-20 object-contain">
        </div>
    </div>

    <!-- TITLE -->
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reset Password</h1>
        <p class="text-gray-600 text-sm">Buat password baru</p>
    </div>

    <!-- FORM -->
    <form method="POST" action="{{ route('password.update') }}" class="bg-white p-6 rounded-2xl shadow-xl">
        @csrf

        <!-- PASSWORD -->
        <div class="mb-6 relative">
            <label class="text-sm font-semibold text-gray-700 block mb-2">Password Baru</label>

            <input type="password"
                   id="password"
                   name="password"
                   required
                   class="w-full px-4 py-3 pr-12 bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">

            <!-- TOGGLE -->
            <button type="button"
                    onclick="togglePassword('password','eye1','eye1off')"
                    class="absolute right-4 top-[42px] text-gray-500 hover:text-blue-600">

                <!-- EYE -->
                <svg id="eye1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>

                <!-- EYE OFF -->
                <svg id="eye1off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-5 0-9-4-9-7 0-1.02.374-2.1 1.05-3.175M6.223 6.223A9.953 9.953 0 0112 5c5 0 9 4 9 7 0 1.657-.993 3.343-2.64 4.732M15 12a3 3 0 00-3-3m0 6a3 3 0 003-3"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M3 3l18 18"/>
                </svg>
            </button>
        </div>

        <!-- CONFIRM PASSWORD -->
        <div class="mb-6 relative">
            <label class="text-sm font-semibold text-gray-700 block mb-2">Konfirmasi Password</label>

            <input type="password"
                   id="password_confirmation"
                   name="password_confirmation"
                   required
                   class="w-full px-4 py-3 pr-12 bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">

            <!-- TOGGLE -->
            <button type="button"
                    onclick="togglePassword('password_confirmation','eye2','eye2off')"
                    class="absolute right-4 top-[42px] text-gray-500 hover:text-blue-600">

                <!-- EYE -->
                <svg id="eye2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7"/>
                </svg>

                <!-- EYE OFF -->
                <svg id="eye2off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-5 0-9-4-9-7"/>
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M3 3l18 18"/>
                </svg>
            </button>
        </div>

        <!-- BUTTON -->
        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl">
            Reset Password
        </button>
    </form>

    <!-- FOOTER -->
    <div class="mt-6 text-center text-sm text-gray-500">
        © {{ date('Y') }} Polije
    </div>

</div>

<!-- SCRIPT -->
<script>
function togglePassword(fieldId, eyeId, eyeOffId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(eyeId);
    const eyeOff = document.getElementById(eyeOffId);

    if (field.type === "password") {
        field.type = "text";
        eye.classList.add("hidden");
        eyeOff.classList.remove("hidden");
    } else {
        field.type = "password";
        eye.classList.remove("hidden");
        eyeOff.classList.add("hidden");
    }
}
</script>

</body>
</html>
