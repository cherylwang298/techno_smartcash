<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

// $sql = "
// SELECT
//     u.fullname,
//     u.phone,
//     b.business_name,
//     b.address,
//     b.city
// FROM users u
// LEFT JOIN businesses b ON b.user_id = u.id
// WHERE u.id = ?
// ";

$sql = "
SELECT
    u.fullname,
    u.phone,

    b.business_name,
    b.business_type,
    b.category,
    b.logo,
    b.address,
    b.city,
    b.phone_number,
    b.capital,
    b.description

FROM users u
LEFT JOIN businesses b ON b.user_id = u.id
WHERE u.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
tailwind.config = {
    theme: {
        extend: {
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
    .hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.glass-card-clear {
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 2px solid rgba(255,255,255,0.6);
}
</style>
</head>

<body class="bg-slate-200 flex items-center justify-center min-h-screen">

<!-- <div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 overflow-hidden flex flex-col"> -->
<div class="relative w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 overflow-hidden flex flex-col">
 <a href="profile.php"
               class="absolute top-14 left-8 text-space-cadet text-xl">
                <i class="fa-solid fa-arrow-left text-black"></i>
            </a>

    <div class="flex-1 overflow-y-auto hide-scrollbar bg-[#7D9FC0] p-6">

        <!-- HEADER -->
        <div class="pt-12 pb-8 flex flex-col items-center text-center">

            <!-- <a href="profile.php"
               class="absolute top-14 left-8 text-space-cadet text-xl">
                <i class="fa-solid fa-arrow-left"></i>
            </a> -->

            <div class="relative mb-4">
                <div class="w-24 h-24 bg-space-cadet rounded-full flex items-center justify-center text-white font-black text-4xl shadow-2xl border-4 border-white">
                    <?= strtoupper(substr($data['business_name'] ?? 'T',0,1)) ?>
                </div>

                <!-- <div class="absolute bottom-0 right-0 bg-pink-lavender text-space-cadet w-8 h-8 rounded-full flex items-center justify-center border-2 border-white shadow-lg">
                    <i class="fa-solid fa-pen text-xs"></i>
                </div> -->
            </div>

            <h1 class="text-2xl font-black text-space-cadet">
                Edit Profil
            </h1>

            <p class="text-sm text-space-cadet/70 font-bold">
                Perbarui data akun & bisnis
            </p>

        </div>

        <form action="UpdateProfileController.php" method="POST" class="space-y-4 pb-10">

            <!-- BISNIS -->

            <div class="glass-card-clear rounded-[24px] p-5">

                <h2 class="font-black text-space-cadet mb-4">
                    Informasi Bisnis
                </h2>

                <div class="space-y-4">

                    <input
                        type="text"
                        name="business_name"
                        placeholder="Nama Toko"
                        value="<?= htmlspecialchars($data['business_name'] ?? '') ?>"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <input
                        type="text"
                        name="business_type"
                        placeholder="Jenis Usaha"
                        value="<?= htmlspecialchars($data['business_type'] ?? '') ?>"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <input
                        type="text"
                        name="category"
                        placeholder="Kategori"
                        value="<?= htmlspecialchars($data['category'] ?? '') ?>"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <input
                        type="text"
                        name="phone_number"
                        placeholder="Nomor Bisnis"
                        value="<?= htmlspecialchars($data['phone_number'] ?? '') ?>"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <textarea
                        name="address"
                        rows="3"
                        placeholder="Alamat"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold"><?= htmlspecialchars($data['address'] ?? '') ?></textarea>

                    <input
                        type="text"
                        name="city"
                        placeholder="Kota"
                        value="<?= htmlspecialchars($data['city'] ?? '') ?>"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <input
                        type="number"
                        name="capital"
                        placeholder="Modal Awal"
                        value="<?= htmlspecialchars($data['capital'] ?? '') ?>"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <textarea
                        name="description"
                        rows="4"
                        placeholder="Deskripsi Usaha"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>

                </div>

            </div>

            <!-- AKUN -->

            <div class="glass-card-clear rounded-[24px] p-5">

                <h2 class="font-black text-space-cadet mb-4">
                    Informasi Akun
                </h2>

                <div class="space-y-4">

                    <input
                        type="text"
                        name="fullname"
                        placeholder="Nama Pemilik"
                        value="<?= htmlspecialchars($data['fullname'] ?? '') ?>"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <input
                        type="text"
                        name="phone"
                        placeholder="Nomor HP"
                        value="<?= htmlspecialchars($data['phone'] ?? '') ?>"
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                </div>

            </div>

            <!-- BUTTON -->

            <button
                type="submit"
                class="w-full bg-space-cadet text-white py-5 rounded-[24px] font-black shadow-2xl active:scale-95 transition">

                <i class="fa-solid fa-floppy-disk mr-2"></i>
                SIMPAN PERUBAHAN

            </button>

        </form>

    </div>

</div>

</body>
</html>