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
</style>
</head>

<body class="bg-slate-200 flex items-center justify-center min-h-screen">

<!-- DEVICE FRAME -->
<div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 overflow-hidden flex flex-col">

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

                <div class="text-center text-yellow-500 font-black text-lg">
                    Kamu sudah Premium
                </div>

            <?php else: ?>

                <button onclick="upgradeNow()"
                    class="w-full py-4 bg-space-cadet text-white rounded-2xl font-black text-sm shadow-xl active:scale-95 transition">
                    Upgrade Sekarang
                </button>

            <?php endif; ?>
        </div>

    </div>

</div>

<script>
function upgradeNow() {
    fetch('subscription_management.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Berhasil upgrade');
            window.location.href = 'profile.php';
        } else {
            alert(data.message);
        }
    })
    .catch(() => alert('Terjadi error'));
}
</script>

</body>
</html>