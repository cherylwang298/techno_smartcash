<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Ambil data user
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
<title>Upgrade Premium</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'space-cadet': '#102B53',
                'pink-lavender': '#CEB5D4',
                'cyan-azure': '#4E7AB1',
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

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.25s ease;
}
</style>
</head>

<body class="bg-slate-200 flex items-center justify-center min-h-screen">

<!-- DEVICE FRAME -->
<div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 overflow-hidden flex flex-col relative">

    <!-- CONTENT -->
    <div class="flex-1 bg-animasi-smartcash p-6 flex flex-col justify-between">

        <!-- HEADER -->
        <div>
            <a href="profile.php" class="text-space-cadet">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>

            <div class="mt-10 text-center">
                <div class="w-20 h-20 mx-auto bg-space-cadet text-white rounded-full flex items-center justify-center text-3xl shadow-xl">
                    <i class="fa-solid fa-crown"></i>
                </div>

                <h1 class="text-2xl font-black text-space-cadet mt-6">
                    Upgrade ke Premium
                </h1>

                <p class="text-sm text-space-cadet/70 mt-2">
                    Buka semua fitur SmartCash Pro
                </p>
            </div>
        </div>

        <!-- FEATURES -->
        <div class="bg-white/60 backdrop-blur-md mt-[-8rem] rounded-3xl p-6 space-y-4 border border-white">

            <div class="flex items-center gap-3">
                <i class="fa-solid fa-check text-green-500"></i>
                <span class="font-bold text-sm">Unlimited Produk</span>
            </div>

            <div class="flex items-center gap-3">
                <i class="fa-solid fa-check text-green-500"></i>
                <span class="font-bold text-sm">Statistik Penjualan</span>
            </div>

        </div>

        <div>
            <?php if ($subscription === 'premium'): ?>

                <div class="text-center text-blue-900 font-black text-lg">
                    Kamu sudah Premium
                </div>

            <?php else: ?>

                <button onclick="openPayment()"
                    class="w-full py-4 bg-space-cadet text-white rounded-2xl font-black text-sm shadow-xl active:scale-95 transition">
                    Upgrade Sekarang
                </button>

            <?php endif; ?>
        </div>

    </div>

<!-- pilihan method pembayaran -->
    <div id="paymentModal" class="hidden absolute inset-0 z-50 flex items-center justify-center">

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        <!-- Modal Box -->
        <div class="relative w-[85%] bg-white rounded-3xl p-6 shadow-2xl animate-fadeIn">

            <h3 class="text-center font-black text-space-cadet text-lg mb-1">
                Pilih Metode Pembayaran
            </h3>
            <p class="text-center text-xs text-gray-400 mb-5">
                Upgrade ke SmartCash Pro
            </p>

            <div class="space-y-3">

                <button onclick="processUpgrade('QRIS')" 
                    class="w-full py-3 bg-slate-100 rounded-xl flex justify-between items-center px-4 font-bold hover:bg-slate-200 transition">
                    <span>QRIS / E-Wallet</span>
                    <i class="fa-solid fa-qrcode"></i>
                </button>

                <button onclick="processUpgrade('Cash')" 
                    class="w-full py-3 bg-slate-100 rounded-xl flex justify-between items-center px-4 font-bold hover:bg-slate-200 transition">
                    <span>Tunai</span>
                    <i class="fa-solid fa-money-bill"></i>
                </button>

                <button onclick="processUpgrade('Card')" 
                    class="w-full py-3 bg-slate-100 rounded-xl flex justify-between items-center px-4 font-bold hover:bg-slate-200 transition">
                    <span>Credit Card</span>
                    <i class="fa-solid fa-credit-card"></i>
                </button>

            </div>

            <button onclick="closePayment()" 
                class="mt-5 w-full text-xs text-gray-400">
                Batal
            </button>

        </div>
    </div>

    <!-- modal sukses -->
<div id="successModal" class="hidden absolute inset-0 z-50 flex items-center justify-center">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Modal Box -->
    <div class="relative bg-white w-[80%] rounded-3xl p-6 text-center shadow-2xl">

        <div class="w-16 h-16 mx-auto bg-green-100 text-green-500 rounded-full flex items-center justify-center text-2xl mb-4">
            <i class="fa-solid fa-check"></i>
        </div>

        <h3 class="font-black text-lg text-space-cadet mb-2">
            Upgrade Berhasil
        </h3>

        <p class="text-sm text-gray-500 mb-5">
            Sekarang kamu sudah menjadi Premium User
        </p>

        <button onclick="goToProfile()"
            class="w-full py-3 bg-space-cadet text-white rounded-xl font-bold">
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