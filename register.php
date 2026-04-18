<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash - Buat Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .bg-animasi-smartcash {
            background: linear-gradient(-45deg, #FFFFFF, #CEB5D4, #4E7AB1, #FFFFFF);
            background-size: 400% 400%;
            animation: flowAnimation 15s ease infinite;
        }

        .glass-form {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .input-focus:focus {
            border-color: #4E7AB1;
            box-shadow: 0 0 0 4px rgba(78, 122, 177, 0.1);
        }
    </style>
</head>

<body class="bg-slate-200 flex items-center justify-center min-h-screen">

    <div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 relative overflow-hidden flex flex-col bg-animasi-smartcash p-7">

        <div class="flex flex-col items-center mt-6 mb-6">
            <h2 class="text-3xl font-black tracking-tighter text-space-cadet">BUAT AKUN</h2>
            <p class="text-[11px] font-bold text-ucla-blue/70 uppercase tracking-widest mt-1 text-center">Mulai kelola keuanganmu sekarang</p>
        </div>

        <div class="glass-form rounded-[35px] p-6 shadow-2xl relative z-10">
            <form action="RegistrationController.php" method="POST" class="space-y-3.5">
                
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
                        <i class="fa-solid fa-user text-xs"></i>
                    </span>
                    <input 
                        type="text"
                        name="fullname"
                        placeholder="Nama Lengkap"
                        required
                        class="w-full pl-10 pr-4 py-3.5 bg-white border-2 border-transparent rounded-2xl outline-none input-focus transition-all font-bold text-space-cadet placeholder:text-ucla-blue/30 text-sm"
                    />
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
                        <i class="fa-solid fa-phone text-xs"></i>
                    </span>
                    <input 
                        type="tel"
                        name="phone"
                        placeholder="Nomor Telepon"
                        required
                        class="w-full pl-10 pr-4 py-3.5 bg-white border-2 border-transparent rounded-2xl outline-none input-focus transition-all font-bold text-space-cadet placeholder:text-ucla-blue/30 text-sm"
                    />
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
                        <i class="fa-solid fa-key text-xs"></i>
                    </span>
                    <input 
                        type="password"
                        name="password"
                        placeholder="Buat Password"
                        required
                        class="w-full pl-10 pr-4 py-3.5 bg-white border-2 border-transparent rounded-2xl outline-none input-focus transition-all font-bold text-space-cadet placeholder:text-ucla-blue/30 text-sm"
                    />
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
                        <i class="fa-solid fa-check-double text-xs"></i>
                    </span>
                    <input 
                        type="password"
                        name="confirm_password"
                        placeholder="Konfirmasi Password"
                        required
                        class="w-full pl-10 pr-4 py-3.5 bg-white border-2 border-transparent rounded-2xl outline-none input-focus transition-all font-bold text-space-cadet placeholder:text-ucla-blue/30 text-sm"
                    />
                </div>

                <div class="pt-2 space-y-3">
                    <button type="submit" name="action" value="dashboard" class="w-full bg-space-cadet text-white py-4 rounded-2xl font-black hover:bg-slate-800 transition active:scale-95 shadow-lg shadow-space-cadet/20 tracking-widest text-[11px]">
                        SELESAI & MASUK
                    </button>

                    <button type="submit" name="action" value="usaha" class="w-full border-2 border-space-cadet text-space-cadet py-3.5 rounded-2xl font-black hover:bg-space-cadet hover:text-white transition active:scale-95 text-[11px] tracking-widest bg-white/50">
                        LANJUT DAFTAR USAHA
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-auto mb-12 text-center relative z-20">
            <div class="bg-white/40 backdrop-blur-md py-3 px-6 rounded-full inline-block border border-white/60 shadow-sm">
                <p class="text-[12px] font-bold text-space-cadet">
                    Sudah punya akun? 
                    <a href="login.php" class="text-cyan-azure hover:text-space-cadet font-black underline decoration-2 underline-offset-2 ml-1 transition-all">MASUK</a>
                </p>
            </div>
        </div>

        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-pink-lavender/40 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-10 w-24 h-24 bg-cyan-azure/20 rounded-full blur-3xl"></div>

    </div>

</body>
</html>