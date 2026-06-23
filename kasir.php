<?php
session_start();
include 'db.php';
include 'PagesController.php';

$isPremium = isPremium($conn);

// Proteksi Login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// 1. Ambil Data Bisnis (untuk Nama Toko & Alamat di Struk)
$sql_biz = "SELECT id, business_name, address, phone_number FROM businesses WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql_biz);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();

$business_id = $business['id'] ?? 0;
$nama_toko = $business['business_name'] ?? "Toko Saya";
$alamat_toko = $business['address'] ?? "Alamat belum diatur";
$telp_toko = $business['phone_number'] ?? "-";

// 2. Ambil Data Produk Milik Bisnis Ini (Hanya stok > 0)
$sql_products = "SELECT id, name, sell_price, stock, image_path FROM products WHERE business_id = ? AND stock > 0 ORDER BY name ASC";
$stmt_prod = $conn->prepare($sql_products);
$stmt_prod->bind_param("i", $business_id);
$stmt_prod->execute();
$products = $stmt_prod->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimplyCash | Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700;800&family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                radial-gradient(at 0% 0%, rgba(206,181,212,0.3) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(125,159,192,0.25) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(78,122,177,0.2) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(206,181,212,0.25) 0px, transparent 50%);
        }

        /* FLOATING ORBS ANIMATION */
        @keyframes float {
            0% { transform: translateY(0px) translateX(0px) scale(1); }
            33% { transform: translateY(-20px) translateX(15px) scale(1.05); }
            66% { transform: translateY(15px) translateX(-15px) scale(0.95); }
            100% { transform: translateY(0px) translateX(0px) scale(1); }
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(40px); opacity: 0.6; z-index: 0; }
        .orb-1 { width: 150px; height: 150px; background: #CEB5D4; top: 10%; left: -20%; animation: float 8s ease-in-out infinite; }
        .orb-2 { width: 180px; height: 180px; background: #7D9FC0; bottom: 30%; right: -20%; animation: float 10s ease-in-out infinite reverse; }
        .orb-3 { width: 120px; height: 120px; background: #e7d3b0; top: 40%; left: 30%; animation: float 7s ease-in-out infinite 1s; opacity: 0.4; }

        /* PHONE SHELL */
        .phone-shell {
            border: 12px solid #102B53;
            border-radius: 56px;
            box-shadow: 0 40px 100px rgba(16, 43, 83, 0.2);
        }

        /* SUPER GLASSY CARDS */
        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 30px rgba(80, 105, 141, 0.08);
            border-radius: 28px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:active { transform: scale(0.96); }

        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            border-top: 1px solid rgba(255, 255, 255, 0.9);
        }

        /* TEXT GRADIENT */
        .text-gradient {
            background: linear-gradient(135deg, #102B53 0%, #4E7AB1 40%, #CEB5D4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0px 4px 6px rgba(16, 43, 83, 0.25));
        }

        /* VIBRANT BUTTONS */
        .btn-vibrant-gradient {
            background: linear-gradient(135deg, #4E7AB1 0%, #CEB5D4 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(206,181,212,0.5);
            transition: all 0.3s ease;
        }
        .btn-vibrant-gradient:active { transform: scale(0.95); box-shadow: 0 4px 15px rgba(206,181,212,0.4); }

        /* RECEIPT PAPER EFFECT */
        .receipt-paper {
            background: rgba(255, 255, 255, 0.95);
            position: relative;
            border-radius: 24px;
        }

        /* NAVBAR (Floating Glass) */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 40px rgba(16, 43, 83, 0.1);
            border-radius: 999px;
        }

        .nav-item { transition: all 0.3s ease; }
        .nav-item.active {
            background: linear-gradient(135deg, rgba(206, 181, 212, 0.25) 0%, rgba(78, 122, 177, 0.15) 100%);
            border-radius: 999px;
        }
        .nav-item.active .nav-icon { color: #4E7AB1; }
        .nav-item.active .nav-label { color: #102B53; font-weight: 800; }
        .nav-icon { color: #7D9FC0; font-size: 16px; transition: color 0.3s; }
        .nav-label { font-size: 9px; font-weight: 700; color: #7D9FC0; letter-spacing: 0.02em; transition: color 0.3s; }

        /* SWAL CUSTOM BUTTON */
        .swal-gradient-btn {
            background: linear-gradient(135deg, #4E7AB1 0%, #CEB5D4 100%) !important;
            color: white !important;
            border-radius: 999px !important;
            font-weight: 800;
            font-size: 12px;
            padding: 14px 42px !important;
            box-shadow: 0 10px 25px rgba(206, 181, 212, 0.5) !important;
            border: none !important;
        }

        #phone {
    position: relative;
    overflow: hidden;
}

#phone .swal2-container {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
}

#phone .swal2-backdrop-show {
    background: rgba(16, 43, 83, 0.5) !important;
}
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen py-5">

    <div id="phone"class="w-[360px] h-[740px] phone-shell dreamy-bg relative overflow-hidden flex flex-col">

        <!-- FLOATING COLORFUL ORBS -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <!-- HEADER -->
        <div class="pt-12 pb-4 px-6 relative z-30 flex-shrink-0">
            <h1 class="text-4xl font-serif font-black text-bold text-gradient leading-tight tracking-tighter mb-5">KASIR</h1>

            <div class="flex items-center gap-3">
                <button class="w-12 h-12 bg-white/70 backdrop-blur-md rounded-[20px] flex items-center justify-center text-cyan-azure shadow-sm border border-white transition active:scale-95">
                    <i class="fa-solid fa-sliders text-sm"></i>
                </button>
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-air-blue text-sm"></i>
                    </div>
                    <input type="text" id="searchInput" onkeyup="searchProduct()" placeholder="Cari menu..."
                        class="w-full pl-11 pr-4 py-3.5 bg-white/70 backdrop-blur-md rounded-[20px] text-[13px] font-bold text-space-cadet placeholder-air-blue/80 outline-none shadow-sm border border-white transition focus:border-pink-lavender focus:bg-white focus:shadow-[0_4px_20px_rgba(206,181,212,0.2)]">
                </div>
            </div>
        </div>

        <!-- PRODUCT GRID -->
        <div class="flex-1 overflow-y-auto px-5 pt-2 pb-48 hide-scrollbar relative z-20">
            <div class="grid grid-cols-2 gap-4" id="productList">
                <?php if ($products->num_rows > 0) : ?>
                    <?php while ($row = $products->fetch_assoc()) : ?>
                        <div onclick="addToCart(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>', <?= $row['sell_price'] ?>, <?= $row['stock'] ?>)"
                            class="product-card glass-card overflow-hidden flex flex-col cursor-pointer group">

                            <div class="w-full h-[110px] relative rounded-t-[28px] overflow-hidden bg-white/50">
                                <img src="<?= !empty($row['image_path']) ? $row['image_path'] : 'https://via.placeholder.com/300' ?>" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg shadow-sm border border-white">
                                    <span class="text-[9px] font-black text-cyan-azure uppercase tracking-widest">Sisa <?= $row['stock'] ?></span>
                                </div>
                            </div>

                            <div class="p-4 flex flex-col flex-1 bg-gradient-to-b from-white/40 to-white/10">
                                <h3 class="product-name text-[13px] font-bold font-serif text-space-cadet leading-tight line-clamp-2"><?= htmlspecialchars($row['name']) ?></h3>
                                <p class="text-[14px] font-black text-cyan-azure mt-auto pt-2">Rp <?= number_format($row['sell_price'], 0, ',', '.') ?></p>

                                <div class="absolute bottom-3 right-3 w-8 h-8 bg-gradient-to-br from-cyan-azure to-pink-lavender text-white rounded-full flex items-center justify-center shadow-[0_4px_12px_rgba(206,181,212,0.5)] group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-plus text-[12px]"></i>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-span-2 text-center py-16">
                        <div class="w-20 h-20 mx-auto bg-white/50 rounded-full flex items-center justify-center text-air-blue mb-4 shadow-sm border border-white">
                            <i class="fa-solid fa-box-open text-3xl"></i>
                        </div>
                        <p class="text-[13px] text-ucla-blue font-bold">Belum ada produk siap jual.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- FLOATING CART BUTTON (VIBRANT) -->
        <div onclick="openCart()"
            class="absolute mb-2 bottom-24 left-1/2 -translate-x-1/2 w-[88%] bg-gradient-to-r from-cyan-azure via-[#81A4CD] to-pink-lavender p-4 rounded-[28px] shadow-[0_15px_35px_rgba(206,181,212,0.4)] flex items-center justify-between cursor-pointer active:scale-95 transition-transform z-40 border border-white/40">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-[20px] flex items-center justify-center text-white relative backdrop-blur-md border border-white/30">
                    <i class="fa-solid fa-basket-shopping text-xl"></i>
                    <span id="cartCount" class="absolute -top-2 -right-2 bg-space-cadet text-white text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full shadow-md border-2 border-transparent">0</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-white/80 uppercase tracking-widest mb-0.5">Total Keranjang</p>
                    <p id="cartTotalDisplay" class="text-[18px] font-black text-white tracking-tight leading-none transition-transform">Rp 0</p>
                </div>
            </div>
            <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center border border-white/30 backdrop-blur-sm">
                <i class="fa-solid fa-arrow-right text-white text-sm"></i>
            </div>
        </div>

        <!-- NAVBAR -->
        <div class="absolute bottom-5 left-5 right-5 navbar-glass px-4 py-3 flex justify-between items-center z-30">
            <a href="kasir.php" class="nav-item active flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-cash-register"></i>
                <span class="nav-label mt-1">Kasir</span>
            </a>
            <a href="main_page.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-house-chimney"></i>
                <span class="nav-label mt-1">Home</span>
            </a>
            <a href="stok.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-box"></i>
                <span class="nav-label mt-1">Stok</span>
            </a>
            <a href="profile.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-circle-user"></i>
                <span class="nav-label mt-1">Profil</span>
            </a>
        </div>

        <!-- CART MODAL -->
        <div id="cartModal" class="hidden absolute inset-0 bg-space-cadet/30 backdrop-blur-md z-[70] flex flex-col justify-end transition-all">
            <div class="glass-panel w-full rounded-t-[45px] p-7 max-h-[85%] flex flex-col relative overflow-hidden">
                <!-- Decorative Blur inside modal -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-pink-lavender/30 rounded-full blur-3xl pointer-events-none"></div>

                <div class="w-12 h-1.5 bg-ucla-blue/20 rounded-full mx-auto mb-6 shrink-0"></div>

                <div class="receipt-paper p-6 shadow-sm border border-white mb-6 flex-1 overflow-y-auto hide-scrollbar z-10">
                    <div class="text-center border-b-[1.5px] border-dashed border-air-blue/40 pb-4 mb-4">
                        <h4 class="text-[14px] font-black text-space-cadet uppercase tracking-widest"><?= htmlspecialchars($nama_toko) ?></h4>
                        <p class="text-[10px] font-bold text-ucla-blue mt-1.5"><?= htmlspecialchars($alamat_toko) ?></p>
                        <p class="text-[10px] font-bold text-ucla-blue"><?= htmlspecialchars($telp_toko) ?></p>
                    </div>

                    <div id="receiptItems" class="space-y-4 mb-5 min-h-[50px]"></div>

                    <div class="border-t-[1.5px] border-dashed border-air-blue/40 pt-4 flex justify-between items-center">
                        <span class="text-[12px] font-black text-space-cadet uppercase tracking-widest">Total Bayar</span>
                        <span id="receiptTotal" class="text-[18px] font-black text-cyan-azure">Rp 0</span>
                    </div>
                </div>

                <div class="flex gap-3 shrink-0 z-10">
                    <button onclick="closeModal('cartModal')" class="flex-1 py-4 bg-white/80 text-ucla-blue rounded-[20px] font-bold text-[11px] uppercase tracking-widest border border-white hover:bg-white shadow-sm transition">Tutup</button>
                    <button onclick="clearCart()" class="flex-1 py-4 bg-[#E8778A]/10 text-[#E8778A] rounded-[20px] font-bold text-[11px] uppercase tracking-widest hover:bg-[#E8778A]/20 shadow-sm transition">Reset</button>
                    <button onclick="openPayment()" class="flex-[1.5] py-4 btn-vibrant-gradient rounded-[20px] font-bold text-[11px] uppercase tracking-widest">Bayar</button>
                </div>
            </div>
        </div>

        <!-- PAYMENT METHOD MODAL -->
        <div id="paymentModal" class="hidden absolute inset-0 z-[80] flex items-center justify-center p-6 bg-space-cadet/40 backdrop-blur-md">
            <div class="relative bg-white/95 backdrop-blur-xl w-full rounded-[40px] p-8 text-center shadow-[0_20px_50px_rgba(16,43,83,0.2)] border border-white overflow-hidden">
                <!-- Decorative Blur -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-32 bg-cyan-azure/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative w-16 h-16 bg-gradient-to-br from-cyan-azure to-pink-lavender rounded-[22px] flex items-center justify-center mx-auto mb-5 text-white shadow-[0_8px_20px_rgba(206,181,212,0.4)]">
                    <i class="fa-solid fa-wallet text-2xl"></i>
                </div>
                <h3 class="text-[18px] font-serif font-black text-space-cadet mb-6">Pilih Pembayaran</h3>

                <!-- PERBAIKAN: Padding tombol diubah menjadi py-[18px] agar lebih lega -->
                <div class="space-y-3 mb-6 relative z-10">
                    <button onclick="successFinish('Tunai')" class="w-full py-[18px] bg-white rounded-[20px] flex items-center justify-between px-6 hover:border-cyan-azure border-2 border-slate-100 shadow-sm transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-9 h-9 bg-cyan-azure/10 text-cyan-azure rounded-full flex items-center justify-center group-hover:bg-cyan-azure group-hover:text-white transition-colors"><i class="fa-solid fa-money-bill-1-wave text-sm"></i></div>
                            <span class="font-bold text-[13px] text-space-cadet">Tunai / Cash</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-air-blue text-xs"></i>
                    </button>

                    <button onclick="showQRIS()" class="w-full py-[18px] bg-white rounded-[20px] flex items-center justify-between px-6 hover:border-[#5BBFA3] border-2 border-slate-100 shadow-sm transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-9 h-9 bg-[#5BBFA3]/10 text-[#5BBFA3] rounded-full flex items-center justify-center group-hover:bg-[#5BBFA3] group-hover:text-white transition-colors"><i class="fa-solid fa-qrcode text-sm"></i></div>
                            <span class="font-bold text-[13px] text-space-cadet">QRIS / E-Wallet</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-air-blue text-xs"></i>
                    </button>
                </div>
                <button onclick="closeModal('paymentModal')" class="text-[11px] font-bold text-air-blue uppercase tracking-widest hover:text-ucla-blue transition-colors relative z-10">Kembali</button>
            </div>
        </div>

        <!-- QRIS MODAL -->
        <div id="qrisModal" class="hidden absolute inset-0 z-[90] flex items-center justify-center p-6 bg-space-cadet/50 backdrop-blur-md">
            <div class="relative bg-white/95 backdrop-blur-xl w-full rounded-[40px] p-8 text-center shadow-2xl border border-white">
                <h3 class="text-[18px] font-serif font-black text-space-cadet mb-1">Scan QRIS</h3>
                <p class="text-[11px] text-ucla-blue font-bold mb-6">Scan dengan e-wallet atau m-banking</p>

                <div class="bg-white p-4 rounded-[28px] border-2 border-dashed border-pink-lavender inline-block mb-6 shadow-sm">
                    <img id="qrisImage" src="" alt="QR Code" class="w-[180px] h-[180px] mx-auto rounded-xl">
                </div>

                <p class="text-[10px] font-bold text-ucla-blue uppercase tracking-widest mb-1">Total Tagihan</p>
                <p id="qrisAmount" class="text-[22px] font-black text-cyan-azure mb-8">Rp 0</p>

                <button onclick="confirmQRISPaid()" class="w-full py-4 btn-vibrant-gradient rounded-[20px] font-bold text-[12px] uppercase tracking-widest mb-4">
                    Saya Sudah Bayar
                </button>
                <button onclick="closeModal('qrisModal'); document.getElementById('paymentModal').classList.remove('hidden');" class="text-[11px] font-bold text-air-blue uppercase tracking-widest hover:text-space-cadet">
                    Batal
                </button>
            </div>
        </div>

        <!-- PREMIUM MODAL -->
        <div id="premiumModal" class="hidden absolute inset-0 z-[100] flex items-center justify-center p-6 bg-space-cadet/60 backdrop-blur-md">
            <div class="relative bg-white/95 backdrop-blur-xl w-full rounded-[40px] p-8 text-center shadow-2xl border border-white overflow-hidden">
                <div class="absolute inset-0 bg-yellow-gold/10 blur-3xl rounded-full scale-150"></div>
                
                <div class="relative w-20 h-20 mx-auto bg-gradient-to-br from-[#f4d481] to-[#e7d3b0] text-white rounded-full flex items-center justify-center text-3xl mb-6 shadow-[0_8px_25px_rgba(231,211,176,0.6)] border-4 border-white z-10">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <h2 class="relative font-serif font-black text-2xl text-space-cadet mb-3 z-10">Fitur Premium</h2>
                <p class="relative text-[13px] text-ucla-blue mb-8 leading-relaxed font-semibold px-2 z-10">
                    Mesin kasir (Point of Sale) ini eksklusif untuk pengguna <span class="text-cyan-azure font-black">SimplyCash Premium</span>.
                </p>
                <button onclick="goUpgrade()" class="relative w-full py-4 btn-vibrant-gradient rounded-[20px] text-[12px] font-black uppercase tracking-widest mb-4 z-10">
                    Upgrade Sekarang
                </button>
                <button onclick="goBack()" class="relative text-[11px] font-bold text-air-blue uppercase tracking-widest hover:text-space-cadet z-10">
                    Kembali ke Beranda
                </button>
            </div>
        </div>
    </div>

    <script>
        const isPremium = <?= $isPremium ? 'true' : 'false' ?>;

        window.onload = function() {
            if (!isPremium) {
                document.getElementById('premiumModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        let cart = [];
        let total = 0;

        function addToCart(id, name, price, stock) {
            const currentQty = cart.filter(item => item.id === id).length;
            if (currentQty >= stock) {
                Swal.fire({
                    target: '#phone',
                    icon: 'warning',
                    iconColor: '#CEB5D4',
                    title: 'Stok Terbatas',
                    text: 'Stok barang ini sudah habis di keranjang.',
                    confirmButtonColor: '#4E7AB1',
                    width: '300px',
                    customClass: {
                        popup: 'rounded-[30px] border border-white shadow-xl',
                        title: 'text-lg font-serif font-bold text-space-cadet',
                        confirmButton: 'rounded-full font-bold text-xs px-8 py-3'
                    }
                });
                return;
            }

            cart.push({ id, name, price });
            updateCart();

            const totalBtn = document.getElementById('cartTotalDisplay');
            totalBtn.classList.add('scale-110');
            setTimeout(() => {
                totalBtn.classList.remove('scale-110');
            }, 200);
        }

        function updateCart() {
            total = cart.reduce((sum, item) => sum + item.price, 0);
            document.getElementById('cartCount').innerText = cart.length;
            document.getElementById('cartTotalDisplay').innerText = 'Rp ' + total.toLocaleString('id-ID');

            const receiptContainer = document.getElementById('receiptItems');
            receiptContainer.innerHTML = '';

            if (cart.length === 0) {
                receiptContainer.innerHTML = '<p class="text-center text-[11px] text-air-blue italic py-4">Keranjang masih kosong.</p>';
            }

            const grouped = cart.reduce((acc, item) => {
                acc[item.name] = (acc[item.name] || { qty: 0, price: item.price });
                acc[item.name].qty++;
                return acc;
            }, {});

            for (const name in grouped) {
                receiptContainer.innerHTML += `
                    <div class="flex justify-between items-center text-[12px] font-bold">
                        <span class="w-8 text-cyan-azure">${grouped[name].qty}x</span>
                        <span class="flex-1 text-space-cadet">${name}</span>
                        <span class="text-space-cadet text-right font-black">${(grouped[name].price * grouped[name].qty).toLocaleString('id-ID')}</span>
                    </div>
                `;
            }
            document.getElementById('receiptTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        function searchProduct() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('product-card');
            for (let card of cards) {
                let name = card.querySelector('.product-name').innerText.toLowerCase();
                card.style.display = name.includes(input) ? "" : "none";
            }
        }

        function openCart() {
            if (cart.length > 0) document.getElementById('cartModal').classList.remove('hidden');
        }

        function openPayment() {
            document.getElementById('cartModal').classList.add('hidden');
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function showQRIS() {
            document.getElementById('paymentModal').classList.add('hidden');
            document.getElementById('qrisAmount').innerText = 'Rp ' + total.toLocaleString('id-ID');

            const qrData = `SMARTCASH-PAY-${Date.now()}-${total}`;
            document.getElementById('qrisImage').src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(qrData)}`;

            document.getElementById('qrisModal').classList.remove('hidden');
        }

        function confirmQRISPaid() {
            document.getElementById('qrisModal').classList.add('hidden');
            successFinish('QRIS');
        }

        function clearCart() {
            cart = [];
            updateCart();
            closeModal('cartModal');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function successFinish(method) {
            if (cart.length === 0) {
                Swal.fire({
                    target: '#phone',
                    icon: 'warning',
                    iconColor: '#CEB5D4',
                    title: 'Keranjang Kosong',
                    text: 'Pilih produk dulu yuk sebelum bayar!',
                    confirmButtonColor: '#4E7AB1',
                    width: '300px',
                    customClass: {
                        popup: 'rounded-[30px] border border-white',
                        title: 'text-lg font-serif font-bold text-space-cadet',
                        confirmButton: 'rounded-full font-bold text-xs px-8 py-3'
                    }
                });
                return;
            }

            Swal.fire({
                target: '#phone',
                title: 'Memproses Pembayaran...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); },
                background: 'rgba(255, 255, 255, 0.95)',
                customClass: { popup: 'rounded-[32px] border border-white backdrop-blur-md overflow-hidden' }
            });

            fetch('transaksi_kasir.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cart: cart, method: method })
                })
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        Swal.fire({
                            target: '#phone',
                            html: `
                                <div class="relative">
                                    <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full blur-2xl opacity-40 pointer-events-none" style="background: #CEB5D4;"></div>
                                    <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full blur-2xl opacity-40 pointer-events-none" style="background: #4E7AB1;"></div>

                                    <div class="relative z-10 pt-4">
                                        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 border-4 border-white shadow-[0_10px_25px_rgba(78,122,177,0.4)]" style="background: linear-gradient(135deg, #4E7AB1 0%, #CEB5D4 100%);">
                                            <i class="fa-solid fa-check text-white text-3xl"></i>
                                        </div>

                                        <h2 class="text-2xl font-serif font-black text-space-cadet mb-1">Berhasil!</h2>
                                        <p class="text-[11px] font-bold text-air-blue uppercase tracking-widest mb-6">Metode: ${method}</p>

                                        <div class="bg-white/80 border border-white rounded-[24px] py-5 px-5 shadow-sm">
                                            <p class="text-[10px] font-bold text-ucla-blue uppercase tracking-widest mb-1">Total Pembayaran</p>
                                            <p class="text-[24px] font-black text-cyan-azure">Rp ${total.toLocaleString('id-ID')}</p>
                                        </div>
                                    </div>
                                </div>
                            `,
                            buttonsStyling: false,
                            confirmButtonText: 'Selesai',
                            width: '320px',
                            background: 'rgba(255, 255, 255, 0.95)',
                            backdrop: 'rgba(16, 43, 83, 0.5)',
                            // 
                            customClass: {
                                popup: 'rounded-[40px] border border-white shadow-2xl',
                                // PERBAIKAN UTAMA: Paksa kontainer HTML SweetAlert agar overflow-nya terpotong
                                htmlContainer: '!overflow-hidden !m-0 !p-5', 
                                confirmButton: 'swal-gradient-btn mt-2 mb-2'
                            }
                        }).then(() => {
                            window.location.href = 'main_page.php';
                        });
                    } else {
                        Swal.fire({
                            target: '#phone',
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            confirmButtonColor: '#102B53',
                            customClass: { popup: 'rounded-[30px]' }
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    Swal.fire({
                        target: '#phone',
                        icon: 'error',
                        title: 'Error Server',
                        text: 'Gagal memproses transaksi.',
                        confirmButtonColor: '#102B53',
                        customClass: { popup: 'rounded-[30px]' }
                    });
                });
        }

        function goUpgrade() { window.location.href = "upgrade_subscription.php"; }
        function goBack() { window.location.href = "main_page.php"; }
    </script>
</body>

</html>