<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700;800&family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                        serif: ['Quicksand', 'serif'],
                    },
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
        * { font-family: 'Nunito', sans-serif; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* DREAMY FLUID BACKGROUND */
        .dreamy-bg {
            background-color: #fdfdfd;
            background-image: 
                radial-gradient(at 0% 0%, rgba(206,181,212,0.35) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(125,159,192,0.25) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(78,122,177,0.2) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(206,181,212,0.3) 0px, transparent 50%);
        }

        /* FLOATING ORBS ANIMATION */
        @keyframes float {
            0% { transform: translateY(0px) translateX(0px) scale(1); }
            33% { transform: translateY(-20px) translateX(15px) scale(1.05); }
            66% { transform: translateY(15px) translateX(-15px) scale(0.95); }
            100% { transform: translateY(0px) translateX(0px) scale(1); }
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(40px); opacity: 0.5; z-index: 0; }
        .orb-1 { width: 160px; height: 160px; background: #CEB5D4; top: -5%; left: -20%; animation: float 8s ease-in-out infinite; }
        .orb-2 { width: 180px; height: 180px; background: #7D9FC0; top: 40%; right: -20%; animation: float 10s ease-in-out infinite reverse; }
        .orb-3 { width: 140px; height: 140px; background: #4E7AB1; bottom: 5%; left: 10%; animation: float 7s ease-in-out infinite 1s; opacity: 0.3; }

        /* PHONE SHELL BORDER */
        .phone-shell {
            border: 12px solid #102B53;
            border-radius: 56px;
            box-shadow: 0 40px 100px rgba(16,43,83,0.25);
        }

        /* SUPER GLASSY CARDS */
        .glass-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 40px rgba(80, 105, 141, 0.1);
            border-radius: 36px;
        }

        /* TEXT GRADIENT */
        .text-gradient {
            background: linear-gradient(135deg, #102B53 0%, #4E7AB1 40%, #CEB5D4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* VIBRANT BUTTONS */
        .btn-vibrant-gradient {
            background: linear-gradient(135deg, #4E7AB1 0%, #CEB5D4 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(206,181,212,0.5);
            transition: all 0.3s ease;
        }
        .btn-vibrant-gradient:active { transform: scale(0.96); box-shadow: 0 4px 15px rgba(206,181,212,0.4); }

        /* INPUTS */
        .glass-input {
            background: rgba(255, 255, 255, 0.7); 
            border: 1.5px solid rgba(255, 255, 255, 0.9);
            border-radius: 20px; 
            outline: none; 
            transition: all 0.3s ease; 
            box-shadow: inset 0 2px 5px rgba(80, 105, 141, 0.02);
        }
        .glass-input:focus { 
            border-color: #CEB5D4; 
            background: #ffffff; 
            box-shadow: 0 4px 20px rgba(206, 181, 212, 0.2); 
        }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen py-5">

    <div class="w-[360px] h-[740px] phone-shell dreamy-bg relative overflow-hidden flex flex-col p-6">

        <!-- FLOATING ORBS -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <!-- HEADER & LOGO -->
        <div class="flex flex-col items-center mt-6 mb-6 relative z-10">
            <!-- LOGO DIPERBESAR JADI 140px -->
            <div class="w-[140px] h-[140px] flex items-center justify-center rotate-3 mb-4 group hover:rotate-0 transition-all duration-300">
                <img src="uploads/logos/sc_logo.png" class="w-full h-full object-contain drop-shadow-[0_15px_25px_rgba(78,122,177,0.3)] group-hover:scale-105 transition-transform duration-300 rounded-[35px]">
            </div>
            
            <h2 class="text-3xl font-serif font-black tracking-tight text-gradient mb-1">SIMPLYCASH</h2>
            <p class="text-[11px] font-extrabold text-ucla-blue uppercase tracking-[0.25em]">Finance Assistant</p>
        </div>

        <!-- FORM CARD -->
        <div class="glass-card w-full p-7 relative z-10">
            <h3 class="text-[20px] font-serif font-black text-space-cadet mb-6 text-center tracking-tight">Selamat Datang!</h3>

            <form action="LoginController.php" method="POST" class="space-y-4">
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                        <i class="fa-solid fa-phone text-sm"></i>
                    </span>
                    <input
                        type="tel"
                        name="phone"
                        placeholder="Nomor Telepon"
                        required
                        class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]" />
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                        class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]" />
                </div>

                <div class="flex justify-end pb-2">
                    <a href="#" class="text-[11px] font-bold text-cyan-azure hover:text-pink-lavender underline decoration-2 underline-offset-4 transition-colors">Lupa Password?</a>
                </div>

                <button type="submit" name="login" class="w-full btn-vibrant-gradient py-[18px] rounded-[20px] font-black uppercase tracking-widest text-[12px] flex items-center justify-center gap-2">
                    Masuk Sekarang <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>
        </div>

        <!-- FOOTER LINK -->
        <div class="mt-auto mb-2 text-center relative z-20">
            <div class="bg-white/60 backdrop-blur-xl py-3.5 px-6 rounded-full inline-block border border-white shadow-sm">
                <p class="text-[11px] font-bold text-space-cadet">
                    Belum punya akun?
                    <a href="register.php" class="text-cyan-azure hover:text-pink-lavender font-black underline decoration-2 underline-offset-2 ml-1 transition-colors">DAFTAR DISINI</a>
                </p>
            </div>
        </div>

        <!-- ERROR MODAL -->
        <div id="errorModal" class="hidden absolute inset-0 z-50 flex items-center justify-center p-6 bg-space-cadet/40 backdrop-blur-md">

            <!-- Menggunakan style popup glassy seperti di kasir/main_page -->
            <div class="relative bg-white/95 backdrop-blur-xl w-full rounded-[40px] p-8 text-center shadow-[0_20px_60px_rgba(16,43,83,0.2)] border border-white overflow-hidden">
                
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-pink-lavender/20 rounded-full blur-3xl pointer-events-none"></div>

                <div id="modalIconContainer" class="w-20 h-20 rounded-[22px] mx-auto flex items-center justify-center border-[3px] shadow-sm relative z-10 bg-white">
                    <i id="modalIcon" class="fa-solid fa-xmark text-4xl"></i>
                </div>

                <h3 id="modalTitle" class="text-[22px] font-serif font-black tracking-tight text-space-cadet mt-5 relative z-10"></h3>
                <p id="modalText" class="text-[13px] font-semibold text-ucla-blue mt-2 leading-relaxed relative z-10"></p>

                <div id="modalFooter" class="mt-4 text-[11px] font-bold text-cyan-azure hidden relative z-10"></div>

                <button onclick="closeModal()" class="mt-6 w-full btn-vibrant-gradient py-[16px] rounded-[20px] font-black uppercase tracking-widest text-[12px] relative z-10">
                    MENGERTI
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
                    iconContainer.classList.add('border-red-400', 'bg-red-50/80');
                    icon.classList.add('text-red-500');
                    icon.className = 'fa-solid fa-key text-3xl text-red-500'; // Icon Kunci
                    title.innerText = 'Password Salah';
                    text.innerText = 'Waduh, password yang kamu masukkan sepertinya tidak cocok. Coba cek kembali ya!';
                } else if (errorType === 'notfound') {
                    iconContainer.classList.add('border-amber-400', 'bg-amber-50/80');
                    icon.className = 'fa-solid fa-user-slash text-3xl text-amber-500'; // Icon User Hilang
                    title.innerText = 'Akun Tidak Ada';
                    text.innerText = 'Nomor HP ini belum terdaftar di Smartcash. Pastikan nomornya benar atau daftar dulu.';
                    footer.innerHTML = 'Belum punya akun? <a href="register.php" class="underline decoration-2 font-black hover:text-pink-lavender transition-colors">DAFTAR DISINI</a>';
                    footer.classList.remove('hidden');
                }

                // Tampilkan modal
                modal.classList.remove('hidden');
                modal.classList.add('flex'); // Add flex to ensure proper centering with the new styling
            }
        };
    </script>

</body>

</html>