<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash - Profil Kelihatan Jelas</title>
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
                        'yellow-gold': '#e7d3b0',
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
            background: linear-gradient(-45deg, #FFFFFF, #CEB5D4, #e7d3b0, #FFFFFF);
            background-size: 400% 400%;
            animation: flowAnimation 15s ease infinite;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Glassmorphism dibuat lebih solid putihnya agar teks terbaca */
        .glass-card-clear {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.6);
        }
    </style>
</head>

<body class="bg-slate-200 flex items-center justify-center min-h-screen">

    <div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 relative overflow-hidden flex flex-col">

        <div class="flex-1 overflow-y-auto pb-28 hide-scrollbar bg-animasi-smartcash p-6">
            
            <div class="pt-12 pb-10 flex flex-col items-center text-center">
                <div class="relative mb-4">
                    <div class="w-24 h-24 bg-space-cadet rounded-full flex items-center justify-center text-white font-black text-4xl shadow-2xl border-4 border-white">
                        TS
                    </div>
                    <button class="absolute bottom-0 right-0 bg-pink-lavender text-space-cadet w-8 h-8 rounded-full flex items-center justify-center border-2 border-white shadow-lg">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </button>
                </div>
                
                <h1 class="text-2xl font-black text-space-cadet tracking-tight leading-none">Toko Sejahtera</h1>
                <p class="text-sm text-space-cadet/80 font-bold mt-2">
                    <i class="fa-solid fa-location-dot mr-1"></i> Jl. Siwalankerto No. 121, Surabaya
                </p>
                
                <div class="mt-5 px-5 py-2 bg-space-cadet rounded-full flex items-center gap-2 shadow-xl border border-white/20">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse shadow-[0_0_8px_#4ade80]"></div>
                    <span class="text-xs font-black text-white uppercase tracking-widest">Free User</span>
                </div>
            </div>

            <div class="space-y-4">
                <a href="#" class="glass-card-clear w-full p-5 rounded-[24px] flex items-center justify-between hover:scale-[1.02] transition transform active:scale-95 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-space-cadet text-white flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-user-pen text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-space-cadet leading-tight">Edit Profile</h3>
                            <p class="text-xs text-space-cadet/70 font-bold mt-0.5">Ubah data & kategori bisnis</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-space-cadet/30 text-sm"></i>
                </a>

                <a href="#" class="glass-card-clear w-full p-5 rounded-[24px] flex items-center justify-between hover:scale-[1.02] transition transform active:scale-95 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-space-cadet text-white flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-globe text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-space-cadet leading-tight">Bahasa</h3>
                            <p class="text-xs text-space-cadet/70 font-bold mt-0.5">Indonesia (Default)</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-space-cadet/30 text-sm"></i>
                </a>

                <a href="#" class="glass-card-clear w-full p-5 rounded-[24px] flex items-center justify-between hover:scale-[1.02] transition transform active:scale-95 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-space-cadet text-white flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-lock text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-space-cadet leading-tight">Keamanan</h3>
                            <p class="text-xs text-space-cadet/70 font-bold mt-0.5">Ubah password akun</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-space-cadet/30 text-sm"></i>
                </a>

                <a href="#" class="w-full bg-space-cadet p-5 rounded-[24px] flex items-center justify-between shadow-2xl mt-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-pink-lavender text-space-cadet flex items-center justify-center shadow-inner">
                            <i class="fa-solid fa-crown text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-pink-lavender leading-tight">SmartCash Pro</h3>
                            <p class="text-xs text-white/70 font-bold mt-0.5">Klik untuk berlangganan</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right-long text-pink-lavender/40 text-lg relative z-10"></i>
                </a>

                <a href="LogoutController.php"
   class="glass-card-clear w-full p-5 rounded-[24px] flex items-center justify-between  bg-red-500
          hover:scale-[1.02] transition transform active:scale-95 group border border-red-200/40">

    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-white text-red-500  flex items-center justify-center shadow-lg">
            <i class="fa-solid fa-right-from-bracket text-lg"></i>
        </div>

        <h3 class="text-base font-black text-white leading-wide text-center">
            Logout
        </h3>
    </div>

    <i class="fa-solid fa-chevron-right text-red-400 text-sm"></i>
</a>
            </div>
        </div>

        <div class="absolute bottom-0 w-full bg-white/90 backdrop-blur-md border-t border-gray-100 px-6 py-5 flex justify-between items-center z-40 rounded-b-[40px] shadow-[0_-10px_30px_rgba(0,0,0,0.05)]">
            <a href="kasir.html" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-cash-register text-xl mb-1"></i><span class="text-[10px] font-bold">Kasir</span></a>
            <a href="main_page.php" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-house text-xl mb-1"></i><span class="text-[10px] font-bold">Beranda</span></a>
            <a href="stok.html" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-box text-xl mb-1"></i><span class="text-[10px] font-bold">Stok</span></a>
            <a href="profile.php" class="flex flex-col items-center text-space-cadet"><i class="fa-solid fa-circle-user text-2xl mb-1"></i><span class="text-[10px] font-black uppercase">Profil</span></a>
        </div>
    </div>

</body>
</html>