<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'space-cadet': '#102B53',
                        'ucla-blue': '#50698D',
                        'pink-lavender': '#CEB5D4',
                        'cyan-azure': '#4E7AB1',
                        'air-blue': '#7D9FC0',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes flowAnimation {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .bg-animasi-smartcash {
            background: linear-gradient(-45deg, #FFFFFF, #CEB5D4, #4E7AB1, #FFFFFF);
            background-size: 400% 400%;
            animation: flowAnimation 15s ease infinite;
        }

        .glass-form {
            background: rgba(255, 255, 255, 0.6);
            /* Lebih putih dikit biar teks makin pop out */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .input-focus:focus {
            border-color: #4E7AB1;
            box-shadow: 0 0 0 4px rgba(78, 122, 177, 0.1);
        }

        .glass-modal {
            background: rgba(255, 255, 255, 0.4);
            /* Lebih transparan */
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body class="bg-slate-200 flex items-center justify-center min-h-screen">

    <div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 relative overflow-hidden flex flex-col bg-animasi-smartcash p-8">

        <div class="flex flex-col items-center mt-8 mb-8">
            <div class="w-16 h-16 bg-space-cadet rounded-[20px] mb-3 flex items-center justify-center text-white font-black text-3xl shadow-2xl rotate-3">
                S
            </div>
            <h2 class="text-3xl font-black tracking-tighter text-space-cadet italic">SMARTCASH</h2>
            <p class="text-[10px] font-bold text-ucla-blue/80 uppercase tracking-[0.2em]">Finance Assistant</p>
        </div>

        <div class="glass-form rounded-[35px] p-7 shadow-2xl relative z-10">
            <h3 class="text-xl font-black text-space-cadet mb-6">Selamat Datang!</h3>

            <form action="LoginController.php" method="POST" class="space-y-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
                        <i class="fa-solid fa-phone text-sm"></i>
                    </span>
                    <input
                        type="tel"
                        name="phone"
                        placeholder="Nomor Telepon"
                        required
                        class="w-full pl-11 pr-4 py-4 bg-white border-2 border-transparent rounded-2xl outline-none input-focus transition-all font-bold text-space-cadet placeholder:text-ucla-blue/30 text-sm shadow-sm" />
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                        class="w-full pl-11 pr-4 py-4 bg-white border-2 border-transparent rounded-2xl outline-none input-focus transition-all font-bold text-space-cadet placeholder:text-ucla-blue/30 text-sm shadow-sm" />
                </div>

                <div class="flex justify-end">
                    <a href="#" class="text-[11px] font-bold text-cyan-azure hover:text-space-cadet underline decoration-2 underline-offset-4 transition">Lupa Password?</a>
                </div>

                <button type="submit" name="login" class="w-full bg-space-cadet text-white py-4 rounded-2xl font-black hover:bg-slate-800 transition active:scale-95 shadow-xl shadow-space-cadet/30 mt-2 tracking-widest text-sm">
                    MASUK SEKARANG
                </button>
            </form>
        </div>

        <div class="mt-auto mb-12 text-center relative z-20">
            <div class="bg-white/40 backdrop-blur-md py-3 px-6 rounded-full inline-block border border-white/60 shadow-sm">
                <p class="text-[12px] font-bold text-space-cadet">
                    Belum punya akun?
                    <a href="register.php" class="text-cyan-azure hover:text-space-cadet font-black underline decoration-2 underline-offset-2 ml-1 transition-all">DAFTAR</a>
                </p>
            </div>
        </div>

        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-pink-lavender/40 rounded-full blur-3xl"></div>
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-cyan-azure/30 rounded-full blur-3xl"></div>

        <div id="errorModal" class="hidden absolute inset-0 z-50 flex items-center justify-center p-6 rounded-[50px]">

            <div class="absolute inset-0 bg-space-cadet/20 rounded-[50px]"></div>

            <div class="glass-modal rounded-[35px] p-8 shadow-2xl relative z-10 w-full text-center border border-white/40">

                <div id="modalIconContainer" class="w-20 h-20 rounded-full mx-auto flex items-center justify-center border-4">
                    <i id="modalIcon" class="fa-solid fa-xmark text-4xl"></i>
                </div>

                <h3 id="modalTitle" class="text-2xl font-black tracking-tighter text-space-cadet mt-6"></h3>
                <p id="modalText" class="text-sm font-bold text-ucla-blue mt-2 leading-relaxed"></p>

                <div id="modalFooter" class="mt-2 text-xs font-bold text-cyan-azure hidden"></div>

                <button onclick="closeModal()" class="mt-8 w-full bg-space-cadet text-white py-3.5 rounded-2xl font-black hover:bg-slate-800 transition active:scale-95 shadow-lg shadow-space-cadet/20 tracking-widest text-[11px]">
                    OKE
                </button>
            </div>
        </div>
    </div>

    <script>
        function closeModal() {
            // Sembunyikan modal
            document.getElementById('errorModal').classList.add('hidden');
            // Bersihkan URL parameter agar modal tidak muncul lagi saat di-refresh
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Cek parameter error saat halaman dimuat
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const errorType = urlParams.get('error');

            if (errorType) {
                const modal = document.getElementById('errorModal');
                const iconContainer = document.getElementById('modalIconContainer');
                const icon = document.getElementById('modalIcon');
                const title = document.getElementById('modalTitle');
                const text = document.getElementById('modalText');
                const footer = document.getElementById('modalFooter');

                // Set konten berdasarkan tipe error
                if (errorType === 'password') {
                    iconContainer.classList.add('border-red-500', 'bg-red-100/50');
                    icon.classList.add('text-red-600');
                    icon.className = 'fa-solid fa-key text-4xl text-red-600'; // Icon Kunci
                    title.innerText = 'PASSWORD SALAH';
                    text.innerText = 'Waduh, password yang kamu masukkan sepertinya tidak cocok. Coba cek kembali ya!';
                } else if (errorType === 'notfound') {
                    iconContainer.classList.add('border-amber-500', 'bg-amber-100/50');
                    icon.className = 'fa-solid fa-user-slash text-4xl text-amber-600'; // Icon User Hilang
                    title.innerText = 'AKUN TIDAK ADA';
                    text.innerText = 'Nomor HP ini belum terdaftar di Smartcash. Pastikan nomornya benar atau daftar dulu.';
                    footer.innerHTML = 'Belum punya akun? <a href="register.php" class="underline decoration-2 font-black">DAFTAR DISINI</a>';
                    footer.classList.remove('hidden');
                }

                // Tampilkan modal
                modal.classList.remove('hidden');
            }
        };
    </script>

</body>

</html>