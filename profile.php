<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

// ─── LOGIKA UPLOAD FOTO PROFIL ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $target_dir = "uploads/logos/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_extension = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
    $new_filename = "logo_biz_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($file_extension, $allowed_types)) {
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $update_sql = "UPDATE businesses SET logo = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("si", $target_file, $user_id);
            $stmt_update->execute();
            header("Location: profile.php?upload=success");
            exit;
        } else {
            $upload_error = "Gagal menyimpan foto ke server.";
        }
    } else {
        $upload_error = "Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.";
    }
}

// Ambil data user & bisnis
$sql_user = "SELECT fullname, subscription FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$sql_biz = "SELECT business_name, address, city, logo FROM businesses WHERE user_id = ? LIMIT 1";
$stmt2 = $conn->prepare($sql_biz);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$biz = $stmt2->get_result()->fetch_assoc();

$nama_toko = $biz['business_name'] ?? "Toko Saya";
$alamat = $biz['address'] ?? "Alamat belum diatur";
$city = $biz['city'] ?? "";
$logo = $biz['logo'] ?? null;
$subscription = $user['subscription'] ?? "free";

$initial = strtoupper(substr($nama_toko, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash - Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        /* GLASSMORPHISM CARDS */
        .glass-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 30px rgba(80, 105, 141, 0.08);
            border-radius: 26px;
            transition: all 0.3s ease;
        }
        .glass-card:active { transform: scale(0.97); }

        .glass-card-sm {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 15px rgba(80, 105, 141, 0.05);
            border-radius: 999px;
        }

        /* TEXT GRADIENT */
        .text-gradient {
            background: linear-gradient(135deg, #102B53 0%, #4E7AB1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* NAVBAR (Floating Glass) */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 40px rgba(16, 43, 83, 0.12);
            border-radius: 999px;
        }

        .nav-item { transition: all 0.3s ease; }
        .nav-item.active { background: linear-gradient(135deg, rgba(206,181,212,0.25) 0%, rgba(78,122,177,0.15) 100%); border-radius: 999px; }
        .nav-item.active .nav-icon { color: #4E7AB1; }
        .nav-item.active .nav-label { color: #102B53; font-weight: 800; }
        .nav-icon { color: #7D9FC0; font-size: 16px; transition: color 0.3s; }
        .nav-label { font-size: 9px; font-weight: 700; color: #7D9FC0; letter-spacing: 0.02em; transition: color 0.3s; }

        /* CUSTOM SWEETALERT BUTTON */
        .swal-gradient-btn {
            background: linear-gradient(135deg, #4E7AB1 0%, #CEB5D4 100%) !important;
            color: white !important;
            border-radius: 999px !important;
            font-weight: 800;
            font-size: 12px;
            padding: 16px 42px !important;
            box-shadow: 0 10px 25px rgba(206, 181, 212, 0.5) !important;
            border: none !important;
            outline: none !important;
            transition: all 0.2s ease;
        }
        .swal-gradient-btn:active { transform: scale(0.96) !important; }

        /* FLOATING HELPER BUTTON ANIMATION */
        @keyframes float-helper {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .animate-float-helper { animation: float-helper 3s ease-in-out infinite; }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen py-5">

    <div class="w-[360px] h-[740px] phone-shell dreamy-bg relative overflow-hidden flex flex-col">

        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="flex-1 overflow-y-auto pb-32 hide-scrollbar px-6 relative z-10">

            <div class="pt-14 pb-6 flex flex-col items-center text-center">
                
                <form action="" method="POST" enctype="multipart/form-data" id="logoForm" class="relative mb-5 inline-block group">
                    <?php if (!empty($logo) && file_exists($logo)): ?>
                        <img src="<?= htmlspecialchars($logo) ?>" alt="Logo Toko" class="w-[110px] h-[110px] rounded-full object-cover shadow-[0_15px_30px_rgba(80,105,141,0.25)] border-[4px] border-white group-hover:scale-105 transition-transform duration-300">
                    <?php else: ?>
                        <div class="w-[110px] h-[110px] rounded-full flex items-center justify-center text-cyan-azure font-serif font-black text-5xl bg-gradient-to-br from-white to-pink-lavender/30 shadow-[0_15px_30px_rgba(206,181,212,0.3)] border-[4px] border-white group-hover:scale-105 transition-transform duration-300">
                            <?= $initial ?>
                        </div>
                    <?php endif; ?>

                    <input type="file" name="logo" id="logoInput" class="hidden" accept="image/jpeg, image/png, image/webp" onchange="document.getElementById('logoForm').submit();">

                    <button type="button" onclick="document.getElementById('logoInput').click();" class="absolute bottom-0 right-0 bg-gradient-to-br from-cyan-azure to-pink-lavender text-white w-10 h-10 rounded-full flex items-center justify-center border-[3px] border-white shadow-[0_4px_15px_rgba(206,181,212,0.6)] hover:scale-110 active:scale-90 transition-transform">
                        <i class="fa-solid fa-camera text-sm"></i>
                    </button>
                </form>

                <h1 class="text-[24px] font-serif font-black text-space-cadet tracking-tight leading-none mb-2"><?= htmlspecialchars($nama_toko) ?></h1>
                <p class="text-[12px] text-ucla-blue font-bold">
                    <i class="fa-solid fa-location-dot mr-1 text-pink-lavender"></i>
                    <?= htmlspecialchars($alamat . ', ' . $city) ?>
                </p>

                <div class="mt-4 px-5 py-3 glass-card-sm flex items-center gap-2">
                    <div class="w-2.5 h-2.5 bg-[#5BBFA3] rounded-full animate-pulse shadow-[0_0_8px_rgba(91,191,163,0.8)]"></div>
                    <span class="text-[11px] font-black text-space-cadet uppercase tracking-widest"> <?= strtoupper($subscription) ?> USER</span>
                </div>
            </div>

            <div class="space-y-4">
                
                <a href="edit_profile.php" class="glass-card w-full p-5 flex items-center justify-between group hover:border-pink-lavender/50 hover:bg-white/80 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-[20px] bg-gradient-to-br from-cyan-azure to-[#81A4CD] text-white flex items-center justify-center shadow-[0_4px_15px_rgba(78,122,177,0.3)]">
                            <i class="fa-solid fa-user-pen text-lg"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-[14px] font-black text-space-cadet leading-tight">Edit Profile</h3>
                            <p class="text-[10px] font-bold text-air-blue mt-1">Ubah data & kategori bisnis</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-air-blue text-sm group-hover:text-cyan-azure transition-colors"></i>
                </a>

                <a href="change_password.php" class="glass-card w-full p-5 flex items-center justify-between group hover:border-pink-lavender/50 hover:bg-white/80 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-[20px] bg-gradient-to-br from-ucla-blue to-air-blue text-white flex items-center justify-center shadow-[0_4px_15px_rgba(80,105,141,0.3)]">
                            <i class="fa-solid fa-lock text-lg"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-[14px] font-black text-space-cadet leading-tight">Keamanan</h3>
                            <p class="text-[10px] font-bold text-air-blue mt-1">Ubah password akun</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-air-blue text-sm group-hover:text-cyan-azure transition-colors"></i>
                </a>

                <a href="upgrade_subscription.php" class="w-full bg-gradient-to-r from-ucla-blue to-space-cadet p-5 rounded-[28px] flex items-center justify-between shadow-[0_15px_30px_rgba(16,43,83,0.3)] mt-6 relative overflow-hidden group active:scale-[0.97] transition-transform">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-12 h-12 rounded-[20px] bg-gradient-to-br from-pink-lavender to-[#e1ccdb] text-space-cadet flex items-center justify-center shadow-inner">
                            <i class="fa-solid fa-crown text-xl"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-[15px] font-black text-pink-lavender leading-tight tracking-wide">SimplyCash Premium</h3>
                            <p class="text-[10px] font-bold text-air-blue mt-1">Klik untuk berlangganan</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right-long text-pink-lavender text-sm relative z-10 mr-1 group-hover:translate-x-1 transition-transform"></i>
                </a>

                <a href="LogoutController.php" class="w-full bg-gradient-to-r from-[#E8778A] to-[#d45a70] p-5 rounded-[28px] flex items-center justify-between shadow-[0_10px_25px_rgba(232,119,138,0.3)] mt-3 active:scale-[0.97] transition-transform group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-[20px] bg-white/20 text-white flex items-center justify-center shadow-inner backdrop-blur-sm">
                            <i class="fa-solid fa-right-from-bracket text-lg pl-1"></i>
                        </div>
                        <h3 class="text-[14px] font-black text-white tracking-wider">Logout</h3>
                    </div>
                    <i class="fa-solid fa-power-off text-white/70 text-sm mr-2 group-hover:text-white transition-colors"></i>
                </a>

            </div>
        </div>

        <button onclick="openSupport()" class="absolute bottom-[85px] right-6 w-14 h-14 bg-gradient-to-br from-cyan-azure to-pink-lavender rounded-full flex items-center justify-center text-white shadow-[0_10px_25px_rgba(206,181,212,0.6)] z-40 hover:scale-105 active:scale-95 transition-all border-[3px] border-white animate-float-helper">
            <i class="fa-solid fa-headset text-2xl"></i>
        </button>

        <div class="absolute bottom-5 left-5 right-5 navbar-glass px-4 py-3 flex justify-between items-center z-50">
            <a href="kasir.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-cash-register"></i>
                <span class="nav-label mt-1">Kasir</span>
            </a>
            <a href="main_page.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-house-chimney"></i>
                <span class="nav-label mt-1">Beranda</span>
            </a>
            <a href="stok.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-box"></i>
                <span class="nav-label mt-1">Stok</span>
            </a>
            <a href="profile.php" class="nav-item active flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-circle-user"></i>
                <span class="nav-label mt-1">Profil</span>
            </a>
        </div>

        <div id="supportModal" class="hidden absolute inset-0 z-[100] flex items-center justify-center p-6 bg-space-cadet/40 backdrop-blur-md transition-all">
            <div class="relative bg-white/95 backdrop-blur-xl w-full rounded-[40px] p-8 text-center shadow-[0_20px_60px_rgba(16,43,83,0.2)] border border-white overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-pink-lavender/20 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="w-16 h-16 rounded-[22px] mx-auto flex items-center justify-center bg-gradient-to-br from-cyan-azure to-pink-lavender shadow-[0_8px_20px_rgba(206,181,212,0.4)] border-2 border-white relative z-10 text-white mb-5">
                    <i class="fa-solid fa-headset text-3xl"></i>
                </div>

                <h3 class="text-[22px] font-serif font-black tracking-tight text-space-cadet mt-2 relative z-10">Butuh Bantuan?</h3>
                <p class="text-[12px] font-bold text-ucla-blue mt-2 leading-relaxed relative z-10 mb-6">Pilih layanan pelanggan yang ingin kamu hubungi.</p>

                <div class="space-y-4 relative z-10">
                    <a href="https://api.whatsapp.com/send?phone=6281335517865&text=Halo%20Admin%20Smartcash,%20saya%20butuh%20bantuan%20terkait%20aplikasi." target="_blank" class="w-full py-5 bg-white rounded-[24px] flex items-center justify-between px-6 hover:border-[#25D366] hover:bg-[#25D366]/5 border-2 border-slate-100 shadow-sm transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-[#25D366]/10 text-[#25D366] rounded-full flex items-center justify-center group-hover:bg-[#25D366] group-hover:text-white transition-colors">
                                <i class="fa-brands fa-whatsapp text-xl"></i>
                            </div>
                            <span class="font-black text-[14px] text-space-cadet">WhatsApp</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-air-blue text-xs"></i>
                    </a>

                    <a href="mailto:C14240048@john.petra.ac.id?subject=Bantuan%20Aplikasi%20Smartcash" class="w-full py-5 bg-white rounded-[24px] flex items-center justify-between px-6 hover:border-cyan-azure hover:bg-cyan-azure/5 border-2 border-slate-100 shadow-sm transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-cyan-azure/10 text-cyan-azure rounded-full flex items-center justify-center group-hover:bg-cyan-azure group-hover:text-white transition-colors">
                                <i class="fa-solid fa-envelope text-lg"></i>
                            </div>
                            <span class="font-black text-[14px] text-space-cadet">Email</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-air-blue text-xs"></i>
                    </a>
                </div>

                <button onclick="closeSupport()" class="mt-8 text-[11px] font-bold text-air-blue uppercase tracking-widest hover:text-ucla-blue transition-colors relative z-10">
                    Batal
                </button>
            </div>
        </div>

    </div>

    <script>
        // FUNGSI UNTUK MODAL SUPPORT (WA / EMAIL)
        function openSupport() {
            document.getElementById('supportModal').classList.remove('hidden');
        }
        function closeSupport() {
            document.getElementById('supportModal').classList.add('hidden');
        }

        const urlParams = new URLSearchParams(window.location.search);
        
        // Notifikasi Foto Upload
        if (urlParams.get('upload') === 'success') {
            Swal.fire({
                html: `
                    <div class="relative">
                        <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full blur-2xl opacity-40 pointer-events-none" style="background: #CEB5D4;"></div>
                        <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full blur-2xl opacity-40 pointer-events-none" style="background: #4E7AB1;"></div>

                        <div class="relative z-10 pt-4">
                            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 border-4 border-white shadow-[0_10px_25px_rgba(78,122,177,0.4)]" style="background: linear-gradient(135deg, #4E7AB1 0%, #CEB5D4 100%);">
                                <i class="fa-solid fa-check text-white text-3xl"></i>
                            </div>

                            <h2 class="text-2xl font-serif font-black text-space-cadet mb-2">Mantap!</h2>
                            <p class="text-[13px] font-medium text-ucla-blue mb-4">Foto profil usahamu berhasil diperbarui.</p>
                        </div>
                    </div>
                `,
                buttonsStyling: false,
                confirmButtonText: 'Tutup',
                width: '320px',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(16, 43, 83, 0.5)',
                customClass: { 
                    popup: 'rounded-[40px] border border-white shadow-2xl', 
                    htmlContainer: '!overflow-hidden !m-0 !p-5',
                    confirmButton: 'swal-gradient-btn mt-2 mb-2' 
                }
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }

        <?php if(isset($upload_error)): ?>
            Swal.fire({
                icon: 'error',
                iconColor: '#E8778A', 
                title: 'Gagal Upload',
                text: '<?= $upload_error ?>',
                confirmButtonColor: '#102B53',
                width: '300px',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(16, 43, 83, 0.4)',
                customClass: {
                    popup: 'rounded-[32px] border border-white shadow-xl',
                    title: 'text-xl font-serif font-bold text-[#102B53]',
                    htmlContainer: 'text-[12px] font-medium text-[#50698D] mt-2',
                    confirmButton: 'swal-gradient-btn mt-2' 
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>