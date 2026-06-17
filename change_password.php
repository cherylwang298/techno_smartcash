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

        /* SOFT FLUID BACKGROUND */
        .dreamy-bg {
            background-color: #fdfdfd;
            background-image: 
                radial-gradient(at 0% 0%, rgba(206,181,212,0.35) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(125,159,192,0.25) 0px, transparent 50%),
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

        /* INPUT FIELDS */
        .input-group { position: relative; width: 100%; }
        .soft-input {
            width: 100%; padding: 16px 16px 16px 48px;
            font-size: 14px; font-weight: 600; color: #102B53;
            background: rgba(255, 255, 255, 0.8);
            border: 1.5px solid #e2e8f0;
            border-radius: 20px; outline: none; transition: all 0.3s ease;
            box-shadow: inset 0 2px 5px rgba(80, 105, 141, 0.02);
        }
        .soft-input::placeholder { color: #7D9FC0; font-weight: 500; opacity: 0.8; }
        .soft-input:focus {
            background: #ffffff; border-color: #CEB5D4;
            box-shadow: 0 4px 20px rgba(206,181,212,0.2);
        }
        .input-icon {
            position: absolute; left: 18px; top: 50%; transform: translateY(-50%);
            font-size: 16px; color: #7D9FC0; transition: color 0.3s ease; pointer-events: none;
        }
        .soft-input:focus + .input-icon { color: #4E7AB1; }

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

    <div class="relative w-[360px] h-[740px] phone-shell dreamy-bg overflow-hidden flex flex-col">

        <div class="pt-12 px-6 flex items-center relative z-10 shrink-0">
            <a href="profile.php" class="w-10 h-10 rounded-full flex items-center justify-center text-space-cadet active:scale-95 transition-transform hover:bg-white/50">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <span class="font-serif font-bold text-space-cadet ml-4 text-lg">Keamanan</span>
        </div>

        <div class="flex-1 overflow-y-auto hide-scrollbar px-6 pb-8 relative z-10">

            <div class="pt-6 pb-8 flex flex-col items-center text-center">
                <div class="relative group mb-6">
                    <div class="absolute inset-0 bg-pink-lavender/40 blur-xl rounded-full scale-110"></div>
                    <div class="relative w-28 h-28 rounded-full bg-gradient-to-br from-white to-pink-lavender/30 flex items-center justify-center text-cyan-azure text-4xl border-4 border-white shadow-[0_8px_30px_rgba(206,181,212,0.5)]">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>

                <h1 class="text-2xl font-serif font-bold text-space-cadet mb-2 tracking-tight">
                    Ubah Password
                </h1>
                <p class="text-ucla-blue text-[13px] font-semibold px-4">
                    Pastikan password baru Anda kuat dan mudah diingat.
                </p>
            </div>

            <form action="UpdatePasswordController.php" method="POST" class="space-y-6">

                <div class="soft-card p-5">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 rounded-full bg-cyan-azure/10 text-cyan-azure flex items-center justify-center">
                            <i class="fa-solid fa-key text-sm"></i>
                        </div>
                        <h2 class="font-serif font-bold text-space-cadet text-[15px]">Kredensial Akun</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="input-group">
                            <input type="password" name="old_password" placeholder="Password Lama" required class="soft-input">
                            <i class="fa-solid fa-lock-open input-icon"></i>
                        </div>

                        <div class="input-group">
                            <input type="password" name="new_password" placeholder="Password Baru" required class="soft-input">
                            <i class="fa-solid fa-lock input-icon"></i>
                        </div>

                        <div class="input-group">
                            <input type="password" name="confirm_password" placeholder="Konfirmasi Password Baru" required class="soft-input">
                            <i class="fa-solid fa-circle-check input-icon"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 btn-gradient text-white rounded-[24px] font-serif font-bold text-[15px] active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                    Update Password
                </button>

            </form>

        </div>
    </div>

</body>
</html>