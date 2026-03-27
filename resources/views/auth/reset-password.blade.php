<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-md">

    <!-- LOGO -->
    <div class="flex justify-center mb-6">
        <div class="bg-white p-4 rounded-full border-2 border-blue-500 shadow-md">
            <img src="{{ asset('img/polije.png') }}" class="w-20 h-20 object-contain">
        </div>
    </div>

    <!-- FORM -->
    <form class="bg-white p-6 rounded-2xl shadow-xl">

        <!-- PASSWORD -->
        <div class="mb-6 relative">
            <label class="block mb-2 text-sm font-semibold">Password Baru</label>

            <input type="password"
                   id="password"
                   class="w-full px-4 py-3 pr-12 bg-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="button"
                    onclick="togglePassword('password','eye1','eye1off')"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">

                <svg id="eye1" class="w-5 h-5" fill="none" stroke="currentColor">
                    <path stroke-width="2" d="M15 12a3 3 0 11-6 0"/>
                    <path stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5"/>
                </svg>

                <svg id="eye1off" class="w-5 h-5 hidden" fill="none" stroke="currentColor">
                    <path stroke-width="2" d="M3 3l18 18"/>
                </svg>
            </button>
        </div>

        <!-- CONFIRM -->
        <div class="mb-6 relative">
            <label class="block mb-2 text-sm font-semibold">Konfirmasi Password</label>

            <input type="password"
                   id="password_confirmation"
                   class="w-full px-4 py-3 pr-12 bg-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="button"
                    onclick="togglePassword('password_confirmation','eye2','eye2off')"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">

                <svg id="eye2" class="w-5 h-5" fill="none" stroke="currentColor">
                    <path stroke-width="2" d="M15 12a3 3 0 11-6 0"/>
                    <path stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5"/>
                </svg>

                <svg id="eye2off" class="w-5 h-5 hidden" fill="none" stroke="currentColor">
                    <path stroke-width="2" d="M3 3l18 18"/>
                </svg>
            </button>
        </div>

        <!-- BUTTON -->
        <button class="w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700">
            Reset Password
        </button>

    </form>

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
