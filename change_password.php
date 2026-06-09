<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Ubah Password</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'space-cadet': '#102B53',
                'pink-lavender': '#CEB5D4',
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

<div class="relative w-[360px] h-[740px] bg-white rounded-[50px] border-[8px] border-slate-900 overflow-hidden shadow-2xl flex flex-col">

    <div class="flex-1 overflow-y-auto hide-scrollbar bg-[#7D9FC0] p-6">

        <div class="pt-12 pb-8 flex flex-col items-center text-center">

            <a href="profile.php"
               class="absolute top-14 left-8 text-space-cadet text-xl">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <div class="w-24 h-24 rounded-full bg-space-cadet flex items-center justify-center text-white text-4xl border-4 border-white shadow-xl mb-4">
                <i class="fa-solid fa-lock"></i>
            </div>

            <h1 class="text-2xl font-black text-space-cadet">
                Ubah Password
            </h1>

            <p class="text-space-cadet/70 text-sm font-bold">
                Pastikan password baru mudah diingat
            </p>

        </div>

        <form action="UpdatePasswordController.php"
              method="POST"
              class="space-y-4">

            <div class="glass-card-clear rounded-[24px] p-5">

                <h3 class="font-black text-space-cadet mb-4">
                    Keamanan Akun
                </h3>

                <div class="space-y-4">

                    <input
                        type="password"
                        name="old_password"
                        placeholder="Password Lama"
                        required
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <input
                        type="password"
                        name="new_password"
                        placeholder="Password Baru"
                        required
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Konfirmasi Password Baru"
                        required
                        class="w-full bg-white/70 rounded-2xl px-4 py-3 outline-none font-bold">

                </div>

            </div>

            <button
                type="submit"
                class="w-full bg-space-cadet text-white py-5 rounded-[24px] font-black shadow-xl">

                <i class="fa-solid fa-key mr-2"></i>
                UPDATE PASSWORD

            </button>

        </form>

    </div>

</div>

</body>
</html>