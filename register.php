<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash - Daftar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700;800&family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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

        <!-- HEADER -->
        <div class="flex flex-col items-center mt-10 mb-8 relative z-10">
            <h2 class="text-3xl font-serif font-black tracking-tight text-gradient uppercase mb-1">Buat Akun</h2>
            <p class="text-[11px] font-extrabold text-ucla-blue uppercase tracking-widest text-center">Mulai kelola keuanganmu sekarang</p>
        </div>

        <!-- FORM CARD -->
        <div class="glass-card w-full p-7 relative z-10">
            <form action="RegistrationController.php" method="POST" class="space-y-4">

                <!-- Nama Lengkap -->
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                        <i class="fa-solid fa-user text-sm"></i>
                    </span>
                    <input
                        type="text"
                        name="fullname"
                        placeholder="Nama Lengkap"
                        required
                        class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]" />
                </div>

                <!-- Nomor Telepon -->
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

                <!-- Password -->
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                        <i class="fa-solid fa-key text-sm"></i>
                    </span>
                    <input
                        type="password"
                        name="password"
                        placeholder="Buat Password"
                        required
                        class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]" />
                </div>

                <!-- Konfirmasi Password -->
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                        <i class="fa-solid fa-check-double text-sm"></i>
                    </span>
                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Konfirmasi Password"
                        required
                        class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]" />
                </div>

                <div class="pt-3">
                    <a href="register_usaha.php" class="block w-full">
                        <button type="submit" name="action" value="dashboard" class="w-full btn-vibrant-gradient py-[18px] rounded-[20px] font-black uppercase tracking-widest text-[12px] flex items-center justify-center gap-2">
                            Lanjut Daftar Usaha <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </a>
                </div>
            </form>
        </div>

        <!-- FOOTER LINK -->
        <div class="mt-auto mb-4 text-center relative z-20">
            <div class="bg-white/60 backdrop-blur-xl py-3.5 px-6 rounded-full inline-block border border-white shadow-sm">
                <p class="text-[11px] font-bold text-space-cadet">
                    Sudah punya akun?
                    <a href="login.php" class="text-cyan-azure hover:text-pink-lavender font-black underline decoration-2 underline-offset-2 ml-1 transition-colors">MASUK DISINI</a>
                </p>
            </div>
        </div>

    </div>

</body>

</html>