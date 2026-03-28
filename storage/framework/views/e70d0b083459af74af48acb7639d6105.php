<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Polije</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        <!-- LOGO -->
        <div class="flex justify-center mb-6">
            <div class="bg-white p-4 rounded-full border-2 border-blue-500 shadow-md">
                <img src="<?php echo e(asset('img/polije.png')); ?>"
                     alt="Logo Polije"
                     class="w-20 h-20 object-contain transition duration-300 hover:scale-110">
            </div>
        </div>

        <!-- TITLE -->
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Login</h1>

        <!-- Link ke jadwal publik -->
<div class="text-center mb-4">
    <a href="<?php echo e(route('public.schedule')); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
        📅 Lihat Jadwal Laboratorium (Tanpa Login)
    </a>
</div>

        <!-- FORM -->
        <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-6 bg-white p-8 rounded-2xl shadow-xl">
            <?php echo csrf_field(); ?>

            <!-- EMAIL -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email"
                       name="email"
                       placeholder="email@polije.ac.id"
                       required
                       class="w-full px-4 py-3 bg-gray-100 border-2 border-transparent rounded-full focus:outline-none focus:border-blue-500 focus:bg-white">
            </div>

            <!-- PASSWORD + TOGGLE -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>

                <input type="password"
                       id="password"
                       name="password"
                       required
                       class="w-full px-4 py-3 pr-12 bg-gray-100 border-2 border-transparent rounded-full focus:outline-none focus:border-blue-500 focus:bg-white">

                <!-- BUTTON TOGGLE -->
                <button type="button"
                        onclick="togglePassword()"
                        class="absolute right-4 top-[42px] text-gray-500 hover:text-blue-600">

                    <!-- ICON EYE -->
                    <svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>

                    <!-- ICON EYE OFF -->
                    <svg id="eyeClose" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-5 0-9-4-9-7 0-1.02.374-2.1 1.05-3.175M6.223 6.223A9.953 9.953 0 0112 5c5 0 9 4 9 7 0 1.657-.993 3.343-2.64 4.732M15 12a3 3 0 00-3-3m0 6a3 3 0 003-3"/>
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3l18 18"/>
                    </svg>
                </button>
            </div>

            <!-- REMEMBER -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" class="mr-2">
                    <span class="text-sm text-gray-600">Ingat saya</span>
                </label>

                <a href="<?php echo e(route('password.request')); ?>" class="text-sm text-blue-600 hover:underline">
                    Lupa Password?
                </a>
            </div>

            <!-- BUTTON -->
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-full transition hover:scale-[1.02]">
                Login
            </button>
        </form>

        <!-- FOOTER -->
        <div class="mt-6 text-center text-sm text-gray-500">
            © <?php echo e(date('Y')); ?> Politeknik Negeri Jember
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const eyeOpen = document.getElementById("eyeOpen");
            const eyeClose = document.getElementById("eyeClose");

            if (password.type === "password") {
                password.type = "text";
                eyeOpen.classList.add("hidden");
                eyeClose.classList.remove("hidden");
            } else {
                password.type = "password";
                eyeOpen.classList.remove("hidden");
                eyeClose.classList.add("hidden");
            }
        }
    </script>

</body>
</html>
<?php /**PATH D:\project\laravel_project\sipinlab\resources\views/auth/login.blade.php ENDPATH**/ ?>