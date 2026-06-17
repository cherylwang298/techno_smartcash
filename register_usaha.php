<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash | Pendaftaran Usaha</title>
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

        /* Custom File Input Styling */
        input[type="file"]::file-selector-button {
            border: none;
            background: #4E7AB1;
            color: white;
            padding: 6px 12px;
            border-radius: 12px;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            margin-right: 12px;
            transition: all 0.3s ease;
        }
        input[type="file"]::file-selector-button:hover { background: #102B53; }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen py-5">

    <div class="w-[360px] h-[780px] phone-shell dreamy-bg relative overflow-hidden flex flex-col p-6">

        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="flex flex-col items-center mt-6 mb-6 relative z-10 shrink-0">
            <h2 class="text-[26px] font-serif font-black tracking-tight text-gradient uppercase mb-1 text-center">Daftar Usaha</h2>
            <p class="text-[11px] font-extrabold text-ucla-blue uppercase tracking-widest text-center">
                Kelola bisnismu lebih cerdas
            </p>
        </div>

        <div class="flex-1 overflow-y-auto hide-scrollbar relative z-10 pb-6 rounded-[36px]">
            <div class="glass-card w-full p-6">
                <form action="BusinessController.php" method="POST" enctype="multipart/form-data" class="space-y-4">

                    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?? '' ?>">

                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                            <i class="fa-solid fa-store text-sm"></i>
                        </span>
                        <input type="text" name="business_name" placeholder="Nama Usaha" required
                            class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]"/>
                    </div>

                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                            <i class="fa-solid fa-layer-group text-sm"></i>
                        </span>
                        <input type="text" name="business_type" placeholder="Jenis Usaha (F&B, Retail, dll)" required
                            class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]"/>
                    </div>

                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                            <i class="fa-solid fa-tags text-sm"></i>
                        </span>
                        <input type="text" name="category" placeholder="Kategori (Contoh: Food, Tech)" required
                            class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]"/>
                    </div>

                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                            <i class="fa-solid fa-image text-sm"></i>
                        </span>
                        <input type="file" name="logo" accept="image/*"
                            class="glass-input w-full pl-12 pr-4 py-[12px] font-bold text-space-cadet text-[12px] cursor-pointer"/>
                    </div>

                    <div class="relative group">
                        <span class="absolute top-[18px] left-0 flex items-start pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                            <i class="fa-solid fa-location-dot text-sm"></i>
                        </span>
                        <textarea name="address" placeholder="Alamat Usaha Lengkap" required
                            class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px] resize-none h-24"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                                <i class="fa-solid fa-city text-sm"></i>
                            </span>
                            <input type="text" name="city" placeholder="Kota" required
                                class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]"/>
                        </div>

                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                                <i class="fa-solid fa-coins text-sm"></i>
                            </span>
                            <input type="number" name="capital" placeholder="Modal (Rp)" required
                                class="glass-input w-full pl-[38px] pr-3 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]"/>
                        </div>
                    </div>

                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                            <i class="fa-solid fa-phone text-sm"></i>
                        </span>
                        <input type="tel" name="phone_number" placeholder="Nomor Handphone Bisnis" required
                            class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px]"/>
                    </div>

                    <div class="relative group">
                        <span class="absolute top-[18px] left-0 flex items-start pl-5 text-air-blue group-focus-within:text-cyan-azure transition-colors">
                            <i class="fa-solid fa-align-left text-sm"></i>
                        </span>
                        <textarea name="description" placeholder="Deskripsi Singkat Usaha" required
                            class="glass-input w-full pl-12 pr-4 py-[16px] font-bold text-space-cadet placeholder:text-air-blue/80 text-[13px] resize-none h-24"></textarea>
                    </div>

                    <input type="hidden" name="is_pro" value="0">

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full btn-vibrant-gradient py-[18px] rounded-[20px] font-black uppercase tracking-widest text-[12px] flex items-center justify-center gap-2">
                            Simpan Usaha <i class="fa-solid fa-cloud-arrow-up"></i>
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

</body>
</html>