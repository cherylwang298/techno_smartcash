<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT fullname, subscription FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$subscription = $user['subscription'] ?? 'free';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimplyCash | Premium</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700;800&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
                        'yellow-gold': '#e7d3b0',
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Nunito', sans-serif; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* SOFT FLUID BACKGROUND */
        .dreamy-bg {
            background-color: #fdfdfd;
            background-image: 
                radial-gradient(at 0% 0%, rgba(206,181,212,0.35) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(231,211,176,0.3) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(78,122,177,0.2) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(206,181,212,0.25) 0px, transparent 50%);
        }

        /* PHONE SHELL */
        .phone-shell {
            border: 12px solid #102B53;
            border-radius: 56px;
            box-shadow: 0 40px 100px rgba(16,43,83,0.2);
        }

        /* AESTHETIC GLASS CARD */
        .soft-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 32px;
            box-shadow: 0 10px 35px rgba(80, 105, 141, 0.08);
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

        /* MODAL ANIMATION */
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-modal { animation: fadeInScale 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen py-5">

<div class="relative w-[360px] h-[740px] phone-shell dreamy-bg overflow-hidden flex flex-col">

    <div class="pt-12 px-6 flex items-center relative z-10 shrink-0">
        <a href="profile.php" class="w-10 h-10 rounded-full flex items-center justify-center text-space-cadet active:scale-95 transition-transform hover:bg-white/50">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto hide-scrollbar px-6 pb-8 relative z-10 flex flex-col justify-between">

        <div class="pt-4 flex flex-col items-center text-center">
            
            <div class="relative group mb-6 mt-2">
                <div class="absolute inset-0 bg-yellow-gold/50 blur-xl rounded-full scale-125"></div>
                <div class="relative w-28 h-28 mx-auto rounded-full bg-gradient-to-br from-white to-yellow-gold/40 flex items-center justify-center text-[#d4af37] text-4xl border-4 border-white shadow-[0_8px_30px_rgba(231,211,176,0.6)]">
                    <i class="fa-solid fa-crown"></i>
                </div>
            </div>

            <h1 class="text-[26px] font-serif font-extrabold text-space-cadet mb-2 tracking-tight">
                SimplyCash Premium
            </h1>
            <p class="text-ucla-blue text-[14px] font-semibold px-4 mb-8">
                Buka semua fitur tanpa batas dan kembangkan bisnis Anda.
            </p>

            <div class="soft-card p-6 w-full text-left space-y-4 mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-[14px] bg-cyan-azure/10 text-cyan-azure flex items-center justify-center border border-white">
                        <i class="fa-solid fa-cash-register text-sm"></i>
                    </div>
                    <div>
                        <span class="font-bold text-space-cadet block text-[15px]">Fitur Kasir Pro</span>
                        <span class="text-[12px] text-ucla-blue">Kelola transaksi tanpa batas</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-[14px] bg-pink-lavender/20 text-[#a37ba8] flex items-center justify-center border border-white">
                        <i class="fa-solid fa-chart-pie text-sm"></i>
                    </div>
                    <div>
                        <span class="font-bold text-space-cadet block text-[15px]">Insight Bisnis</span>
                        <span class="text-[12px] text-ucla-blue">Analisa grafik harian & bulanan</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-[14px] bg-air-blue/20 text-ucla-blue flex items-center justify-center border border-white">
                        <i class="fa-solid fa-file-export text-sm"></i>
                    </div>
                    <div>
                        <span class="font-bold text-space-cadet block text-[15px]">Eksport Laporan</span>
                        <span class="text-[12px] text-ucla-blue">Unduh format CSV & PDF</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full">
            <?php if ($subscription === 'premium'): ?>
                <div class="soft-card py-5 text-center flex flex-col items-center justify-center border-green-200 bg-green-50/50">
                    <i class="fa-solid fa-circle-check text-green-500 text-3xl mb-2"></i>
                    <span class="text-space-cadet font-black text-[15px]">Akun Anda Premium</span>
                </div>
            <?php else: ?>
                <button onclick="openPayment()" class="w-full py-5 btn-gradient text-white rounded-[24px] font-serif font-black text-[15px] uppercase tracking-wide active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rocket text-sm"></i>
                    Upgrade Sekarang
                </button>
            <?php endif; ?>
        </div>

    </div>

    <div id="paymentModal" class="hidden absolute inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-space-cadet/30 backdrop-blur-md" onclick="closePayment()"></div>

        <div class="relative w-[85%] bg-white/90 backdrop-blur-xl border border-white rounded-[32px] p-6 shadow-[0_20px_60px_rgba(16,43,83,0.15)] animate-modal">
            
            <div class="w-12 h-12 mx-auto bg-cyan-azure/10 text-cyan-azure rounded-full flex items-center justify-center text-xl mb-3">
                <i class="fa-solid fa-wallet"></i>
            </div>

            <h3 class="text-center font-serif font-black text-space-cadet text-lg mb-1">
                Pilih Pembayaran
            </h3>
            <p class="text-center text-[13px] text-ucla-blue mb-6">
                Upgrade ke SimplyCash Premium
            </p>

            <div class="space-y-3 relative z-10">
                <button onclick="processUpgrade('QRIS')" class="w-full py-4 bg-white border-2 border-slate-100 rounded-[20px] flex justify-between items-center px-5 font-bold text-space-cadet hover:border-cyan-azure hover:bg-cyan-azure/5 transition-all shadow-sm">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-qrcode text-pink-lavender"></i> QRIS / E-Wallet</span>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                </button>

                <button onclick="processUpgrade('Card')" class="w-full py-4 bg-white border-2 border-slate-100 rounded-[20px] flex justify-between items-center px-5 font-bold text-space-cadet hover:border-cyan-azure hover:bg-cyan-azure/5 transition-all shadow-sm">
                    <span class="flex items-center gap-3"><i class="fa-regular fa-credit-card text-air-blue"></i> Credit Card</span>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                </button>
            </div>

            <button onclick="closePayment()" class="mt-6 w-full text-[13px] font-bold text-slate-400 hover:text-space-cadet transition-colors">
                Batal
            </button>
        </div>
    </div>

    <div id="successModal" class="hidden absolute inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-space-cadet/40 backdrop-blur-md"></div>

        <div class="relative bg-white/95 backdrop-blur-xl border border-white w-[80%] rounded-[32px] p-8 text-center shadow-[0_20px_60px_rgba(16,43,83,0.2)] animate-modal">

            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-[#cbeab9] to-[#92d075] text-white rounded-full flex items-center justify-center text-4xl mb-5 shadow-[0_8px_20px_rgba(146,208,117,0.4)] border-4 border-white">
                <i class="fa-solid fa-check"></i>
            </div>

            <h3 class="font-serif font-black text-xl text-space-cadet mb-2 tracking-tight">
                Upgrade Berhasil!
            </h3>

            <p class="text-[14px] text-ucla-blue mb-8 font-medium">
                Selamat! Sekarang kamu sudah bisa menikmati fitur Premium.
            </p>

            <button onclick="goToProfile()" class="w-full py-4 btn-gradient text-white rounded-[20px] font-bold text-[14px] shadow-lg active:scale-95 transition-transform">
                Kembali ke Profil
            </button>
        </div>
    </div>

</div>

<script>
function openPayment() {
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePayment() {
    document.getElementById('paymentModal').classList.add('hidden');
}

function processUpgrade(method) {
    fetch('subscription_management.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ method })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // tutup modal payment
            closePayment();
            // tampilkan success modal
            document.getElementById('successModal').classList.remove('hidden');
        } else {
            alert(data.message); // boleh tetep alert untuk error
        }
    })
    .catch(() => alert('Terjadi error'));
}

function goToProfile() {
    window.location.href = 'profile.php';
}
</script>

</body>
</html>