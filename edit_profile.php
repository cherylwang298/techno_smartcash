<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Ambil data user & bisnis
$sql = "SELECT u.fullname, u.phone, b.business_name, b.business_type, b.category, b.logo, b.address, b.city, b.phone_number, b.capital, b.description 
        FROM users u 
        LEFT JOIN businesses b ON b.user_id = u.id 
        WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$logo = $data['logo'] ?? null;
$initial = strtoupper(substr($data['business_name'] ?? 'T', 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil | SimplyCash Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
                        'air-blue': '#7D9FC0'
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Nunito', sans-serif; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* SOFT FLUID BACKGROUND MATCHING REFERENCE */
        .dreamy-bg {
            background-color: #f4f6fa;
            background-image: 
                radial-gradient(at 0% 0%, rgba(206,181,212,0.3) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(125,159,192,0.2) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(78,122,177,0.2) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(206,181,212,0.2) 0px, transparent 50%);
        }

        /* PHONE SHELL */
        .phone-shell {
            border: 12px solid #102B53;
            border-radius: 56px;
            box-shadow: 0 40px 100px rgba(16,43,83,0.2);
        }

        /* CLEAN SOFT CARDS MATCHING REFERENCE */
        .soft-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 32px;
            box-shadow: 0 8px 32px rgba(80, 105, 141, 0.06);
        }

        /* AESTHETIC INPUTS - CLEAN & ROUNDED */
        .input-group {
            position: relative;
            width: 100%;
        }
        .soft-input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            font-size: 14px;
            font-weight: 600;
            color: #102B53;
            background: rgba(255, 255, 255, 0.7);
            border: 1.5px solid #e2e8f0;
            border-radius: 20px;
            outline: none;
            transition: all 0.3s ease;
        }
        .soft-input::placeholder { color: #7D9FC0; font-weight: 500; opacity: 0.7; }
        .soft-input:focus {
            background: #ffffff;
            border-color: #CEB5D4;
            box-shadow: 0 4px 20px rgba(206,181,212,0.15);
        }
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #7D9FC0;
            transition: color 0.3s ease;
            pointer-events: none;
        }
        .soft-input:focus + .input-icon { color: #4E7AB1; }

        /* TEXT AREA ADJUSTMENT */
        textarea.soft-input { 
            padding-left: 16px; 
            border-radius: 20px;
            resize: none; 
        }

        /* LABELS */
        .soft-label {
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            font-size: 13px;
            color: #50698D;
            margin-bottom: 8px;
            margin-left: 6px;
            display: block;
        }

        /* BUTTON GRADIENT */
        .btn-gradient {
            background: linear-gradient(135deg, #4E7AB1 0%, #CEB5D4 100%);
            box-shadow: 0 8px 25px rgba(206,181,212,0.4);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            box-shadow: 0 10px 30px rgba(206,181,212,0.6);
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen py-5">

    <div class="w-[360px] h-[740px] phone-shell dreamy-bg relative overflow-hidden flex flex-col">
        
        <div class="pt-12 px-6 flex items-center relative z-10 shrink-0 mb-2">
            <a href="profile.php" class="w-10 h-10 rounded-full flex items-center justify-center text-space-cadet active:scale-95 transition-transform hover:bg-white/50">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-xl font-serif font-bold text-space-cadet ml-4">Profile</h1>
        </div>

        <div class="flex-1 overflow-y-auto hide-scrollbar px-5 pb-8 relative z-10">
            <form action="UpdateProfileController.php" method="POST" class="space-y-6">

                <div class="flex flex-col items-center pt-2 pb-6 relative group">
                    <div class="relative">
                        <?php if (!empty($logo) && file_exists($logo)): ?>
                            <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" class="w-28 h-28 rounded-full object-cover shadow-[0_8px_25px_rgba(78,122,177,0.2)] border-4 border-white transition-transform duration-300 group-hover:scale-105">
                        <?php else: ?>
                            <div class="w-28 h-28 rounded-full flex items-center justify-center text-white font-serif font-black text-4xl bg-gradient-to-br from-cyan-azure to-pink-lavender shadow-[0_8px_25px_rgba(206,181,212,0.4)] border-4 border-white transition-transform duration-300 group-hover:scale-105">
                                <?= $initial ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="absolute bottom-0 right-0 w-8 h-8 bg-white rounded-full flex items-center justify-center text-cyan-azure shadow-md cursor-pointer border border-slate-100 hover:bg-slate-50 transition-colors">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="soft-card p-5 space-y-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-cyan-azure/10 text-cyan-azure flex items-center justify-center">
                            <i class="fa-solid fa-store text-sm"></i>
                        </div>
                        <h2 class="font-serif font-bold text-space-cadet text-[15px]">Bisnis Anda</h2>
                    </div>
                    
                    <div class="space-y-4 relative z-10">
                        <div class="input-group">
                            <label class="soft-label">Nama Toko</label>
                            <div class="relative">
                                <input type="text" name="business_name" value="<?= htmlspecialchars($data['business_name'] ?? '') ?>" placeholder="Toko Impianku" class="soft-input">
                                <i class="fa-solid fa-signature input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="input-group">
                                <label class="soft-label">Jenis Usaha</label>
                                <div class="relative">
                                    <input type="text" name="business_type" value="<?= htmlspecialchars($data['business_type'] ?? '') ?>" placeholder="Retail" class="soft-input pl-[42px]">
                                    <i class="fa-solid fa-tag input-icon"></i>
                                </div>
                            </div>
                            <div class="input-group">
                                <label class="soft-label">Kategori</label>
                                <div class="relative">
                                    <input type="text" name="category" value="<?= htmlspecialchars($data['category'] ?? '') ?>" placeholder="F&B" class="soft-input pl-[42px]">
                                    <i class="fa-solid fa-shapes input-icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="input-group">
                            <label class="soft-label">Alamat Lengkap</label>
                            <textarea name="address" rows="2" placeholder="Masukkan alamat lengkap..." class="soft-input"><?= htmlspecialchars($data['address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="input-group">
                                <label class="soft-label">Kota</label>
                                <div class="relative">
                                    <input type="text" name="city" value="<?= htmlspecialchars($data['city'] ?? '') ?>" placeholder="Malang" class="soft-input pl-[42px]">
                                    <i class="fa-solid fa-location-dot input-icon"></i>
                                </div>
                            </div>
                            <div class="input-group">
                                <label class="soft-label">Modal Awal</label>
                                <div class="relative">
                                    <input type="number" name="capital" value="<?= htmlspecialchars($data['capital'] ?? '') ?>" placeholder="5000000" class="soft-input pl-[42px]">
                                    <i class="fa-solid fa-wallet input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="soft-card p-5 space-y-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-pink-lavender/20 text-pink-lavender flex items-center justify-center">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <h2 class="font-serif font-bold text-space-cadet text-[15px]">Informasi Akun</h2>
                    </div>

                    <div class="space-y-4 relative z-10">
                        <div class="input-group">
                            <label class="soft-label">Nama Lengkap</label>
                            <div class="relative">
                                <input type="text" name="fullname" value="<?= htmlspecialchars($data['fullname'] ?? '') ?>" placeholder="Budi Santoso" class="soft-input">
                                <i class="fa-solid fa-id-badge input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label class="soft-label">Nomor HP</label>
                            <div class="relative">
                                <input type="text" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" placeholder="08123456789" class="soft-input">
                                <i class="fa-solid fa-phone input-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full mt-4 py-4 btn-gradient text-white rounded-[24px] font-serif font-bold text-[15px] active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-regular fa-circle-check text-lg"></i>
                    Simpan Perubahan
                </button>
                
                <div class="h-6"></div> </form>
        </div>
    </div>
</body>
</html>