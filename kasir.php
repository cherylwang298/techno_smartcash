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

// 2. Ambil Data Produk Milik Bisnis Ini
$sql_products = "SELECT id, name, sell_price, stock, image_path FROM products WHERE business_id = ? AND stock > 0 ORDER BY name ASC";
$stmt_prod = $conn->prepare($sql_products);
$stmt_prod->bind_param("i", $business_id);
$stmt_prod->execute();
$products = $stmt_prod->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash - Kasir Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
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
        @keyframes flow {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .bg-animasi-smartcash {
            background: linear-gradient(-45deg, #FFFFFF, #CEB5D4, #4E7AB1, #FFFFFF);
            background-size: 400% 400%;
            animation: flow 15s ease infinite;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .receipt-paper {
            background: #ffffff;
            background-image: radial-gradient(#f1f5f9 1.5px, transparent 0);
            background-size: 15px 15px;
            position: relative;
        }

        .receipt-paper::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(-45deg, transparent 5px, white 5px), linear-gradient(45deg, transparent 5px, white 5px);
            background-size: 10px 10px;
        }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-2xl border-[8px] border-slate-900 relative overflow-hidden flex flex-col">

        <div class="bg-animasi-smartcash pt-10 pb-5 px-5 relative z-30 border-b-2 border-white/50 shadow-sm">
            <div class="flex items-center gap-2">
                <button class="w-10 h-10 bg-white/80 backdrop-blur-md rounded-xl flex items-center justify-center text-space-cadet shadow-sm border border-white">
                    <i class="fa-solid fa-filter text-xs"></i>
                </button>
                <div class="relative flex-1">
                    <input type="text" id="searchInput" onkeyup="searchProduct()" placeholder="Cari nama produk..."
                        class="w-full pl-4 pr-10 py-3 bg-white/90 rounded-2xl text-sm font-black text-space-cadet outline-none shadow-sm">
                    <button class="absolute right-4 top-1/2 -translate-y-1/2 text-space-cadet">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto pb-44 hide-scrollbar bg-slate-50/50 p-5">
            <div class="grid grid-cols-2 gap-4" id="productList">
                <?php if ($products->num_rows > 0) : ?>
                    <?php while ($row = $products->fetch_assoc()) : ?>
                        <div onclick="addToCart(
                            <?= $row['id'] ?>,
                            '<?= htmlspecialchars($row['name']) ?>',
                            <?= $row['sell_price'] ?>,
                            <?= $row['stock'] ?>
                        )"
                            class="product-card bg-white rounded-[32px] overflow-hidden flex flex-col active:scale-95 transition-all shadow-sm border border-slate-100 cursor-pointer">

                            <div class="w-full h-32 bg-slate-100 relative">
                                <img src="<?= !empty($row['image_path']) ? $row['image_path'] : 'https://via.placeholder.com/300' ?>"
                                    class="w-full h-full object-cover">
                            </div>

                            <div class="p-4 flex flex-col flex-1">
                                <h3 class="product-name text-[12px] font-black text-space-cadet uppercase tracking-tighter">
                                    <?= htmlspecialchars($row['name']) ?>
                                </h3>
                                <p class="text-[14px] font-black text-cyan-azure mt-1">
                                    Rp <?= number_format($row['sell_price'], 0, ',', '.') ?>
                                </p>

                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-[10px] font-black text-ucla-blue/40">Stok: <?= $row['stock'] ?></span>
                                    <div class="w-9 h-9 bg-space-cadet text-white rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                <?php endif; ?>
            </div>
        </div>

        <div onclick="openCart()"
            class="absolute bottom-24 left-1/2 -translate-x-1/2 w-[92%] bg-space-cadet p-5 rounded-[28px] shadow-2xl flex items-center justify-between cursor-pointer active:scale-95 transition z-40 border-2 border-white/10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-white relative">
                    <i class="fa-solid fa-cart-shopping text-lg"></i>
                    <span id="cartCount" class="absolute -top-2 -right-2 bg-pink-lavender text-space-cadet text-[11px] font-black px-2 py-0.5 rounded-full border-2 border-space-cadet">0</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-white/40 uppercase tracking-widest leading-none mb-1">Total Transaksi</p>
                    <p id="cartTotalDisplay" class="text-[16px] font-black text-white tracking-tight leading-none">Rp 0</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-up text-pink-lavender animate-bounce"></i>
        </div>

        <div id="cartModal" class="hidden absolute inset-0 bg-space-cadet/80 backdrop-blur-md z-[70] flex flex-col justify-end">
            <div class="bg-slate-100 w-full rounded-t-[50px] p-8 max-h-[90%] overflow-y-auto hide-scrollbar">
                <div class="w-12 h-1.5 bg-slate-300 rounded-full mx-auto mb-8"></div>

                <div class="receipt-paper p-6 rounded-3xl shadow-xl border border-slate-200 mb-8 overflow-hidden">
                    <div class="text-center border-b-2 border-dashed border-slate-200 pb-4 mb-4">
                        <h4 class="text-sm font-black text-space-cadet uppercase tracking-widest"><?= htmlspecialchars($nama_toko) ?></h4>
                        <p class="text-[9px] font-bold text-ucla-blue mt-1 italic"><?= htmlspecialchars($alamat_toko) ?></p>
                        <p class="text-[9px] font-bold text-ucla-blue"><?= htmlspecialchars($telp_toko) ?></p>
                    </div>

                    <div id="receiptItems" class="space-y-3 mb-6">
                    </div>

                    <div class="border-t-2 border-dashed border-slate-200 pt-4 flex justify-between items-center">
                        <span class="text-[11px] font-black text-space-cadet uppercase">Total Akhir</span>
                        <span id="receiptTotal" class="text-[15px] font-black text-cyan-azure">Rp 0</span>
                    </div>
                </div>

                <div class="flex gap-4 mb-4">
                    <button onclick="closeModal('cartModal')" class="flex-1 py-4 bg-white text-ucla-blue rounded-2xl font-black text-[11px] uppercase tracking-widest border-2 border-slate-200">Batal</button>
                    <button onclick="clearCart()" class="flex-1 py-4 bg-white text-ucla-blue rounded-2xl font-black text-[11px] uppercase tracking-widest border-2 border-slate-200">Clear</button>
                    <button onclick="openPayment()" class="flex-1 py-4 bg-space-cadet text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-xl">Proses</button>
                </div>
            </div>
        </div>

        <div id="paymentModal" class="hidden absolute inset-0 z-[80] flex items-center justify-center p-8">
            <div class="absolute inset-0 bg-animasi-smartcash"></div>
            <div class="relative bg-white w-full rounded-[45px] p-8 text-center shadow-2xl border border-white/50">
                <div class="w-16 h-16 bg-space-cadet/10 rounded-3xl flex items-center justify-center mx-auto mb-6 text-space-cadet">
                    <i class="fa-solid fa-wallet text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-space-cadet mb-8 uppercase tracking-widest italic leading-none">Pilih Pembayaran</h3>
                <div class="space-y-3 mb-8">
                    <button onclick="successFinish('Tunai')" class="w-full py-4 bg-slate-50 rounded-2xl flex items-center justify-between px-6 hover:bg-space-cadet hover:text-white transition-all group">
                        <span class="font-black text-[11px] uppercase tracking-widest">Tunai / Cash</span>
                        <i class="fa-solid fa-money-bill-1-wave"></i>
                    </button>
                    <button onclick="successFinish('QRIS')" class="w-full py-4 bg-slate-50 rounded-2xl flex items-center justify-between px-6 hover:bg-space-cadet hover:text-white transition-all group">
                        <span class="font-black text-[11px] uppercase tracking-widest">QRIS / E-Wallet</span>
                        <i class="fa-solid fa-qrcode"></i>
                    </button>
                </div>
                <button onclick="closeModal('paymentModal')" class="text-[10px] font-black text-ucla-blue/40 uppercase tracking-[0.2em]">Kembali</button>
            </div>
        </div>

        <div id="premiumModal" class="hidden absolute inset-0 z-[100] flex items-center justify-center">

    <!-- overlay -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    <!-- modal -->
    <div class="relative bg-white w-[85%] rounded-3xl p-6 text-center shadow-2xl">

        <div class="w-16 h-16 mx-auto bg-yellow-100 text-yellow-500 rounded-2xl flex items-center justify-center text-2xl mb-4">
            <i class="fa-solid fa-crown"></i>
        </div>

        <h2 class="font-black text-lg text-space-cadet mb-2">
            Fitur Premium
        </h2>

        <p class="text-sm text-gray-500 mb-6">
            Kasir hanya untuk user premium
        </p>

        <button onclick="goUpgrade()" 
            class="w-full py-3 bg-space-cadet text-white rounded-xl font-black mb-3">
            Upgrade Sekarang
        </button>

        <button onclick="goBack()" 
            class="text-xs text-gray-400">
            Kembali
        </button>
    </div>
</div>

        <div class="absolute bottom-0 w-full bg-white/95 backdrop-blur-md border-t border-slate-100 px-8 py-6 flex justify-between items-center z-50 rounded-b-[40px]">
            <a href="kasir.php" class="flex flex-col items-center text-space-cadet relative">
                <i class="fa-solid fa-cash-register text-xl"></i>
                <span class="text-[9px] font-black mt-1.5 uppercase">Kasir</span>
                <div class="absolute -bottom-2 w-1.5 h-1.5 bg-space-cadet rounded-full"></div>
            </a>
            <a href="main_page.php" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-house-chimney text-xl"></i><span class="text-[9px] font-black mt-1.5 uppercase">Beranda</span></a>
            <a href="stok.php" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-box-open text-xl"></i><span class="text-[9px] font-black mt-1.5 uppercase">Stok</span></a>
            <a href="profile.php" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-circle-user text-xl"></i><span class="text-[9px] font-black mt-1.5 uppercase">Profil</span></a>
        </div>
    </div>

    <script>
        let cart = [];
        let total = 0;

        function addToCart(id, name, price, stock) {
    // hitung qty barang ini di cart
    const currentQty = cart.filter(item => item.id === id).length;

    if (currentQty >= stock) {
        return;
    }

    cart.push({
        id,
        name,
        price
    });

    updateCart();
}

        function updateCart() {
            total = cart.reduce((sum, item) => sum + item.price, 0);
            document.getElementById('cartCount').innerText = cart.length;
            document.getElementById('cartTotalDisplay').innerText = 'Rp ' + total.toLocaleString('id-ID');

            // Update Receipt Items
            const receiptContainer = document.getElementById('receiptItems');
            receiptContainer.innerHTML = '';

            // Grouping items for receipt
            const grouped = cart.reduce((acc, item) => {
                acc[item.name] = (acc[item.name] || {
                    qty: 0,
                    price: item.price
                });
                acc[item.name].qty++;
                return acc;
            }, {});

            for (const name in grouped) {
                receiptContainer.innerHTML += `
                    <div class="flex justify-between items-center text-[11px] font-black">
                        <span class="w-8 text-ucla-blue/60">${grouped[name].qty}x</span>
                        <span class="flex-1 text-space-cadet uppercase">${name}</span>
                        <span class="text-space-cadet text-right">${(grouped[name].price * grouped[name].qty).toLocaleString('id-ID')}</span>
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
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function clearCart()
            {
                cart = [];
                updateCart();
            }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        // function successFinish(method) {
        //     alert('Transaksi ' + method + ' Berhasil! Total: Rp ' + total.toLocaleString('id-ID'));
        //     window.location.href = 'main_page.php';
     //}

     function successFinish(method) {
    fetch('transaksi_kasir.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            cart: cart,
            method: method
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Transaksi berhasil! Total: Rp ' + total.toLocaleString('id-ID'));
            window.location.href = 'main_page.php';
        } else {
            alert('Gagal: ' + data.message);
        }
    });
}
    </script>

<script>
const isPremium = <?= $isPremium ? 'true' : 'false' ?>;
</script>

<script>
window.onload = function() {
    if (!isPremium) {
        document.getElementById('premiumModal').classList.remove('hidden');

        document.body.style.overflow = 'hidden';
    }
}

function goUpgrade() {
    window.location.href = "upgrade_subscription.php";
}

function goBack() {
    window.location.href = "main_page.php";
}
</script>
</body>

</html>