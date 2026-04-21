<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash - Daftar Usaha</title>
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
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .input-focus:focus {
            border-color: #4E7AB1;
            box-shadow: 0 0 0 4px rgba(78, 122, 177, 0.1);
        }
    </style>
</head>

<body class="bg-slate-200 flex items-center justify-center min-h-screen">

    <div class="w-[360px] h-[780px] bg-white rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 relative overflow-hidden flex flex-col bg-animasi-smartcash p-7">

        <!-- HEADER -->
        <div class="flex flex-col items-center mt-6 mb-6">
            <h2 class="text-3xl font-black tracking-tighter text-space-cadet">DAFTAR USAHA</h2>
            <p class="text-[11px] font-bold text-ucla-blue/70 uppercase tracking-widest mt-1 text-center">
                Kelola bisnismu lebih cerdas
            </p>
        </div>

        <!-- FORM -->
        <div class="glass-form rounded-[35px] p-6 shadow-2xl relative z-10 overflow-y-auto max-h-[520px]">
            <form action="BusinessController.php" method="POST" enctype="multipart/form-data" class="space-y-3.5">

    <!-- user_id (hidden dari session nanti) -->
    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?? '' ?>">

    <!-- Nama Usaha -->
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
            <i class="fa-solid fa-store text-xs"></i>
        </span>
        <input type="text" name="business_name" placeholder="Nama Usaha" required
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm"/>
    </div>

    <!-- Jenis Usaha -->
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
            <i class="fa-solid fa-layer-group text-xs"></i>
        </span>
        <input type="text" name="business_type" placeholder="Jenis Usaha (F&B, Retail, dll)" required
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm"/>
    </div>

    <!-- Category -->
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
            <i class="fa-solid fa-tags text-xs"></i>
        </span>
        <input type="text" name="category" placeholder="Kategori (Contoh: Food, Tech, Service)" required
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm"/>
    </div>

    <!-- Logo -->
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
            <i class="fa-solid fa-image text-xs"></i>
        </span>
        <input type="file" name="logo" accept="image/*"
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm bg-white"/>
    </div>

    <!-- Alamat -->
    <div class="relative">
        <span class="absolute top-4 left-4 text-space-cadet/40">
            <i class="fa-solid fa-location-dot text-xs"></i>
        </span>
        <textarea name="address" placeholder="Alamat Usaha" required
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm resize-none h-20"></textarea>
    </div>

    <!-- Kota -->
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
            <i class="fa-solid fa-city text-xs"></i>
        </span>
        <input type="text" name="city" placeholder="Kota" required
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm"/>
    </div>

    <!-- Nomor Telepon -->
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
            <i class="fa-solid fa-phone text-xs"></i>
        </span>
        <input type="tel" name="phone_number" placeholder="Nomor Telepon Usaha" required
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm"/>
    </div>

    <!-- Modal -->
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-space-cadet/40">
            <i class="fa-solid fa-coins text-xs"></i>
        </span>
        <input type="number" name="capital" placeholder="Modal Awal (Rp)" required
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm"/>
    </div>

    <!-- Deskripsi -->
    <div class="relative">
        <span class="absolute top-4 left-4 text-space-cadet/40">
            <i class="fa-solid fa-align-left text-xs"></i>
        </span>
        <textarea name="description" placeholder="Deskripsi Usaha" required
            class="w-full pl-10 pr-4 py-3.5 rounded-2xl input-focus font-bold text-space-cadet text-sm resize-none h-20"></textarea>
    </div>

    <!-- is_pro (default 0 / optional upgrade) -->
    <input type="hidden" name="is_pro" value="0">

    <!-- BUTTON -->
    <div class="pt-2 space-y-3">
        <button type="submit"
            class="w-full bg-space-cadet text-white py-4 rounded-2xl font-black hover:bg-slate-800 transition active:scale-95 shadow-lg tracking-widest text-[11px]">
            SIMPAN USAHA
        </button>
    </div>

</form>
        </div>
      <!-- DECOR -->
        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-pink-lavender/40 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-10 w-24 h-24 bg-cyan-azure/20 rounded-full blur-3xl"></div>

    </div>

</body>
</html>