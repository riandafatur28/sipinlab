<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Polije</title>
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
        <p class="text-center text-gray-600 mb-6">Masukkan kode OTP yang telah dikirim</p>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <p class="text-sm text-blue-800 text-center">
                <svg class="inline w-4 h-4 mr-1 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Kode OTP telah dikirim ke email Anda<br>
                <span class="font-semibold text-blue-600">Kode berlaku selama 5 menit</span>
            </p>
        </div>

        <!-- Success Message -->
        @if (session('status'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-green-800 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('status') }}
                </p>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('password.verify.post') }}" id="otp-form" class="bg-white p-8 rounded-2xl shadow-xl">
            @csrf

            <!-- OTP Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4 text-center">
                    Kode OTP
                </label>
                <div class="flex justify-center gap-2 mb-2">
                    <input type="text" name="otp_1" maxlength="1" class="otp-input w-12 h-14 text-center text-2xl font-bold bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all" autocomplete="off">
                    <input type="text" name="otp_2" maxlength="1" class="otp-input w-12 h-14 text-center text-2xl font-bold bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all" autocomplete="off">
                    <input type="text" name="otp_3" maxlength="1" class="otp-input w-12 h-14 text-center text-2xl font-bold bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all" autocomplete="off">
                    <input type="text" name="otp_4" maxlength="1" class="otp-input w-12 h-14 text-center text-2xl font-bold bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all" autocomplete="off">
                    <input type="text" name="otp_5" maxlength="1" class="otp-input w-12 h-14 text-center text-2xl font-bold bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all" autocomplete="off">
                    <input type="text" name="otp_6" maxlength="1" class="otp-input w-12 h-14 text-center text-2xl font-bold bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all" autocomplete="off">
                </div>
                <!-- Hidden input untuk menyimpan OTP lengkap -->
                <input type="hidden" name="otp" id="otp-full" value="">

                @error('otp')
                    <p class="mt-2 text-sm text-red-600 text-center flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Timer & Resend -->
            <div class="text-center mb-6">
                <span id="timer" class="inline-flex items-center text-sm text-gray-500 mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="timer-text">01:00</span>
                </span>
                <div class="mt-2">
                    <button type="button"
                            id="resend-button"
                            onclick="resendOTP()"
                            disabled
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:text-blue-600 transition-colors">
                        Kirim Ulang Kode
                    </button>
                </div>
            </div>

            <!-- Confirm Button -->
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all duration-200 ease-in-out transform hover:scale-[1.02] hover:shadow-lg flex items-center justify-center group">
                <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Konfirmasi
            </button>
        </form>

        <!-- Back -->
        <div class="mt-6 text-center">
            <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-gray-800 hover:underline inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                © {{ date('Y') }} Politeknik Negeri Jember
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-input');

            // Auto-focus dan navigation
            inputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Hanya angka
                    this.value = this.value.replace(/[^0-9]/g, '');

                    if (this.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        inputs[index - 1].focus();
                    }
                });

                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = e.clipboardData.getData('text').slice(0, 6);

                    for (let i = 0; i < pasteData.length; i++) {
                        if (inputs[i] && /^[0-9]$/.test(pasteData[i])) {
                            inputs[i].value = pasteData[i];
                        }
                    }

                    if (pasteData.length === 6) {
                        inputs[5].focus();
                        combineAndSubmit();
                    }
                });

                // Auto-submit jika semua terisi
                input.addEventListener('input', function() {
                    let allFilled = true;
                    inputs.forEach(inp => {
                        if (inp.value === '') allFilled = false;
                    });

                    if (allFilled) {
                        setTimeout(() => {
                            combineAndSubmit();
                        }, 300);
                    }
                });
            });

            // Focus input pertama
            if (inputs.length > 0) {
                inputs[0].focus();
            }
        });

        // Gabungkan OTP dan submit
        function combineAndSubmit() {
            const inputs = document.querySelectorAll('.otp-input');
            let otp = '';
            inputs.forEach(input => {
                otp += input.value;
            });
            document.getElementById('otp-full').value = otp;

            if (otp.length === 6) {
                document.getElementById('otp-form').submit();
            }
        }

        // Timer dan resend
        let timeLeft = 60;
        const timerElement = document.getElementById('timer-text');
        const resendButton = document.getElementById('resend-button');
        let timerInterval;

        function startTimer() {
            timeLeft = 60;
            resendButton.disabled = true;
            timerElement.textContent = '01:00';

            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    resendButton.disabled = false;
                }
            }, 1000);
        }

        // Resend OTP
        async function resendOTP() {
            if (resendButton.disabled) return;

            resendButton.disabled = true;
            const originalText = resendButton.textContent;
            resendButton.textContent = 'Mengirim...';

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch('{{ route('password.resend') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.status) {
                    // Reset timer
                    startTimer();

                    // Clear inputs
                    document.querySelectorAll('.otp-input').forEach(input => {
                        input.value = '';
                    });
                    document.querySelector('.otp-input').focus();

                    // Show success message
                    alert(result.message || 'Kode OTP baru telah dikirim ke email Anda');
                } else {
                    alert(result.message || 'Gagal mengirim ulang OTP. Silakan coba lagi.');
                    resendButton.disabled = false;
                }

            } catch (error) {
                console.error('Resend error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                resendButton.disabled = false;
            }

            resendButton.textContent = originalText;
        }

        // Start timer on page load
        startTimer();
    </script>
</body>
</html>
