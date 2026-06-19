<?php
session_start();
include 'db.php';

// Proteksi Login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// 1. Ambil ID Bisnis
$sql_biz = "SELECT id FROM businesses WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql_biz);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$business_data = $stmt->get_result()->fetch_assoc();
$business_id = $business_data['id'] ?? 0;

// 2. Logika Filter Kategori
$category_filter = $_GET['category'] ?? 'Semua';

// Perhatikan: Kolom min_stock ditambahkan ke query!
$sql_stok = "SELECT id, name, category, buy_price, sell_price, stock, image_path, min_stock 
             FROM products 
             WHERE business_id = ?";

if ($category_filter !== 'Semua') {
    $sql_stok .= " AND category = ?";
}
$sql_stok .= " ORDER BY stock ASC";

$stmt_stok = $conn->prepare($sql_stok);
if ($category_filter !== 'Semua') {
    $stmt_stok->bind_param("is", $business_id, $category_filter);
} else {
    $stmt_stok->bind_param("i", $business_id);
}
$stmt_stok->execute();
$products = $stmt_stok->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash | Stok</title>
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
                        'air-blue': '#7D9FC0',
                        'blush-pink': '#E8778A'
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
        .orb-3 { width: 120px; height: 120px; background: #4E7AB1; top: 40%; left: 30%; animation: float 7s ease-in-out infinite 1s; opacity: 0.3; }

        /* PHONE SHELL BORDER */
        .phone-shell {
            border: 12px solid #102B53;
            border-radius: 56px;
            box-shadow: 0 40px 100px rgba(16,43,83,0.2);
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

        /* TEXT GRADIENT */
        .text-gradient {
            background: linear-gradient(135deg, #102B53 0%, #4E7AB1 45%, #CEB5D4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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

        /* NAVBAR (Floating Glass) */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 40px rgba(16, 43, 83, 0.1);
            border-radius: 999px;
        }

        .nav-item { transition: all 0.3s ease; }
        .nav-item.active { background: linear-gradient(135deg, rgba(206,181,212,0.25) 0%, rgba(78,122,177,0.15) 100%); border-radius: 999px; }
        .nav-item.active .nav-icon { color: #4E7AB1; }
        .nav-item.active .nav-label { color: #102B53; font-weight: 800; }
        .nav-icon { color: #7D9FC0; font-size: 16px; transition: color 0.3s; }
        .nav-label { font-size: 9px; font-weight: 700; color: #7D9FC0; letter-spacing: 0.02em; transition: color 0.3s; }

        /* MODAL INPUTS */
        .glass-input {
            background: rgba(255, 255, 255, 0.8); 
            border: 1.5px solid rgba(255,255,255,0.9);
            border-radius: 20px; 
            font-size: 12px; font-weight: 700; color: #102B53;
            padding: 14px 16px; outline: none; transition: all 0.3s; 
            box-shadow: inset 0 2px 5px rgba(80, 105, 141, 0.02);
            width: 100%;
        }
        .glass-input:focus { border-color: #CEB5D4; background: #fff; box-shadow: 0 4px 20px rgba(206,181,212,0.2); }
        .glass-input::placeholder { color: #7D9FC0; font-weight: 600; opacity: 0.8; }

        /* Alert Animation */
        @keyframes alert-pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(232, 119, 138, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(232, 119, 138, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(232, 119, 138, 0); }
        }
        .alert-animate { animation: alert-pulse 2s infinite; }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen py-5">

    <div class="w-[360px] h-[740px] phone-shell dreamy-bg relative overflow-hidden flex flex-col">

        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="pt-12 pb-2 px-6 relative z-30 flex-shrink-0">
            <h1 class="text-4xl font-serif font-black text-bold text-gradient leading-tight tracking-tighter mb-5">STOK PRODUK</h1>
            
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-air-blue text-sm"></i>
                    </div>
                    <input type="text" id="searchInput" onkeyup="searchStok()" placeholder="Cari produk..."
                        class="w-full pl-11 pr-4 py-3.5 bg-white/70 backdrop-blur-md rounded-[20px] text-[13px] font-bold text-space-cadet placeholder-air-blue/80 outline-none shadow-sm border border-white transition focus:border-pink-lavender focus:bg-white focus:shadow-[0_4px_20px_rgba(206,181,212,0.2)]">
                </div>
                <!-- Tombol Add -->
                <button onclick="openModal('add')" class="w-[46px] h-[52px] rounded-[20px] btn-vibrant-gradient flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-plus text-lg"></i>
                </button>
            </div>

            <div class="flex gap-2.5 mt-5 overflow-x-auto hide-scrollbar pb-2 px-1">
                <?php
                $categories = ['Semua', 'Makanan', 'Minuman', 'Snack', 'Dessert'];
                foreach ($categories as $cat) :
                    $isActive = ($category_filter === $cat);
                    $btnClass = $isActive 
                        ? 'bg-gradient-to-r from-cyan-azure to-pink-lavender text-white shadow-[0_8px_20px_rgba(206,181,212,0.4)] border-transparent' 
                        : 'bg-white/50 backdrop-blur-md text-space-cadet border border-white hover:bg-white/80 shadow-sm';
                ?>
                    <a href="stok.php?category=<?= $cat ?>"
                        class="flex-none px-5 py-2.5 <?= $btnClass ?> text-[11px] font-black rounded-[16px] uppercase tracking-widest whitespace-nowrap transition-all">
                        <?= $cat ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto pb-36 hide-scrollbar px-5 pt-2 relative z-20">
            <div class="grid grid-cols-2 gap-4" id="stokList">
                <?php while ($row = $products->fetch_assoc()) :
                    // Ambil min_stock untuk produk ini (atau default 5 jika kosong)
                    $min_stock_limit = $row['min_stock'] ?? 5;
                    $isEmpty = ($row['stock'] <= 0);
                    $isLow = ($row['stock'] <= $min_stock_limit && !$isEmpty);
                ?>
                    <div onclick="openModal('edit', '<?= $row['id'] ?>', '<?= htmlspecialchars(addslashes($row['name'])) ?>', '<?= $row['stock'] ?>', '<?= $row['sell_price'] ?>', '<?= $row['buy_price'] ?>', '<?= $row['category'] ?>', '<?= $min_stock_limit ?>')"
                        class="product-item glass-card overflow-hidden flex flex-col cursor-pointer group <?= $isLow ? 'border-blush-pink/40 shadow-[0_4px_15px_rgba(232,119,138,0.1)]' : '' ?>">

                        <div class="w-full h-[110px] relative rounded-t-[28px] overflow-hidden bg-white/50">
                            <?php
                            $path_foto = (!empty($row['image_path']) && file_exists($row['image_path']))
                                ? $row['image_path']
                                : 'https://via.placeholder.com/300';
                            ?>
                            <img src="<?= $path_foto ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 <?= $isEmpty ? 'grayscale opacity-50' : '' ?>">

                            <?php if ($isEmpty) : ?>
                                <div class="absolute inset-0 bg-space-cadet/30 flex items-center justify-center backdrop-blur-sm">
                                    <span class="bg-blush-pink text-white text-[10px] font-black px-3.5 py-1.5 rounded-xl rotate-[-10deg] tracking-widest shadow-lg border border-white/50">HABIS</span>
                                </div>
                            <?php elseif ($isLow) : ?>
                                <div class="absolute top-2 right-2 w-7 h-7 bg-blush-pink text-white rounded-full flex items-center justify-center shadow-lg alert-animate border-2 border-white">
                                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-4 flex flex-col gap-2 bg-gradient-to-b from-white/40 to-white/10 flex-1">
                            <h3 class="product-name text-[13px] font-bold text-space-cadet font-serif leading-tight line-clamp-2"><?= htmlspecialchars($row['name']) ?></h3>
                            
                            <div class="space-y-1.5 mt-auto">
                                <div class="flex justify-between items-center bg-white/40 px-2.5 py-1 rounded-lg border border-white/50">
                                    <span class="text-[9px] font-extrabold text-air-blue uppercase tracking-widest">Modal</span>
                                    <span class="text-[11px] font-black text-ucla-blue">Rp <?= number_format($row['buy_price'], 0, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between items-center bg-cyan-azure/10 px-2.5 py-1 rounded-lg border border-cyan-azure/20">
                                    <span class="text-[9px] font-extrabold text-cyan-azure uppercase tracking-widest">Jual</span>
                                    <span class="text-[11px] font-black text-space-cadet">Rp <?= number_format($row['sell_price'], 0, ',', '.') ?></span>
                                </div>
                            </div>

                            <div class="mt-2 py-2 <?= $isLow ? 'bg-blush-pink/10 text-blush-pink border-blush-pink/20' : 'bg-white/80 text-space-cadet border-white' ?> rounded-[14px] flex items-center justify-center border shadow-sm">
                                <span class="text-[10px] font-black uppercase tracking-widest">Stok: <?= $row['stock'] ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="absolute bottom-5 left-5 right-5 navbar-glass px-4 py-3 flex justify-between items-center z-40">
            <a href="kasir.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-cash-register"></i>
                <span class="nav-label mt-1">Kasir</span>
            </a>
            <a href="main_page.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-house-chimney"></i>
                <span class="nav-label mt-1">Home</span>
            </a>
            <a href="stok.php" class="nav-item active flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-box-open"></i>
                <span class="nav-label mt-1">Stok</span>
            </a>
            <a href="profile.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-circle-user"></i>
                <span class="nav-label mt-1">Profil</span>
            </a>
        </div>

        <!-- MODAL TAMBAH/EDIT PRODUK -->
        <div id="modalStok" class="hidden absolute inset-0 bg-space-cadet/40 backdrop-blur-md z-[100] flex items-center justify-center p-5 transition-all duration-300">
            <div class="relative bg-white/95 backdrop-blur-xl w-full max-w-[320px] p-7 max-h-[90%] overflow-y-auto hide-scrollbar rounded-[40px] shadow-[0_20px_60px_rgba(16,43,83,0.2)] border border-white">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-pink-lavender/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 -left-10 w-32 h-32 bg-cyan-azure/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="w-12 h-1.5 bg-ucla-blue/20 rounded-full mx-auto mb-5"></div>
                <h3 id="modalTitle" class="text-[20px] font-serif font-black text-space-cadet mb-6 text-center tracking-tight">Edit Produk</h3>

                <form action="proses_stok.php" method="POST" enctype="multipart/form-data" class="space-y-4 relative z-10">
                    <input type="hidden" name="product_id" id="formId">
                    <input type="hidden" name="action_type" id="formAction">
                    <input type="hidden" name="business_id" value="<?= $business_id ?>">

                    <div>
                        <label class="text-[11px] font-bold text-ucla-blue block mb-2 ml-1">Nama Produk</label>
                        <input id="formName" name="name" type="text" required class="glass-input" placeholder="Misal: Kopi Susu Aren">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-bold text-ucla-blue block mb-2 ml-1">Jumlah Stok</label>
                            <input id="formStock" name="stock" type="number" required class="glass-input" placeholder="0">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-ucla-blue block mb-2 ml-1 flex items-center gap-1"><i class="fa-solid fa-bell text-blush-pink"></i> Batas Alert</label>
                            <input id="formMinStock" name="min_stock" type="number" required class="glass-input" placeholder="Misal: 5">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-bold text-ucla-blue block mb-2 ml-1">Harga Modal</label>
                            <input id="formBuyPrice" name="purchase_price" type="number" required class="glass-input" placeholder="Rp 0">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-ucla-blue block mb-2 ml-1">Harga Jual</label>
                            <input id="formPrice" name="price" type="number" required class="glass-input" placeholder="Rp 0">
                        </div>
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-ucla-blue block mb-2 ml-1">Kategori</label>
                        <select id="formCategory" name="category" class="glass-input cursor-pointer" style="padding-top: 13px; padding-bottom: 13px; padding-right: 10px;">
                            <option value="Makanan">Makanan</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Snack">Snack</option>
                            <option value="Dessert">Dessert</option>
                        </select>
                    </div>

                    <div class="mt-2">
                        <label class="text-[11px] font-bold text-ucla-blue block mb-2 ml-1">Foto Produk</label>
                        <div class="w-full h-28 bg-white/70 rounded-[24px] border-2 border-dashed border-pink-lavender/60 flex flex-col items-center justify-center text-air-blue hover:bg-white transition-all cursor-pointer relative overflow-hidden group shadow-sm">
                            <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                            
                            <div id="uploadPlaceholder" class="flex flex-col items-center group-hover:text-cyan-azure transition-colors">
                                <div class="w-10 h-10 bg-pink-lavender/10 rounded-full flex items-center justify-center mb-2 group-hover:bg-cyan-azure/10">
                                    <i class="fa-solid fa-camera text-lg"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest">Ketuk untuk pilih</span>
                            </div>
                            
                            <img id="imagePreview" class="hidden absolute inset-0 w-full h-full object-cover">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-5">
                        <button onclick="closeModal()" type="button" class="flex-1 py-4 bg-white border border-slate-100 shadow-sm text-ucla-blue rounded-[20px] text-[11px] font-bold uppercase tracking-widest transition hover:bg-slate-50 active:scale-95">
                            Batal
                        </button>
                        <button type="submit" class="flex-[1.5] py-4 btn-vibrant-gradient rounded-[20px] text-[11px] font-bold uppercase tracking-widest">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        // Perhatikan parameter minStock ditambahkan di fungsi ini
        function openModal(mode, id = '', name = '', stock = '', price = '', buyPrice = '', category = '', minStock = '5') {
            const modal = document.getElementById('modalStok');
            const title = document.getElementById('modalTitle');
            const placeholder = document.getElementById('uploadPlaceholder');
            const preview = document.getElementById('imagePreview');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('formAction').value = mode;

            preview.classList.add('hidden');
            preview.src = '';
            placeholder.classList.remove('hidden');

            if (mode === 'edit') {
                title.innerText = "Edit Produk";
                document.getElementById('formId').value = id;
                document.getElementById('formName').value = name;
                document.getElementById('formStock').value = stock;
                document.getElementById('formMinStock').value = minStock; // Set min_stock
                document.getElementById('formPrice').value = price;
                document.getElementById('formBuyPrice').value = buyPrice;
                document.getElementById('formCategory').value = category;
            } else {
                title.innerText = "Tambah Produk";
                document.getElementById('formId').value = '';
                document.getElementById('formName').value = '';
                document.getElementById('formStock').value = '';
                document.getElementById('formMinStock').value = '5'; // Default
                document.getElementById('formPrice').value = '';
                document.getElementById('formBuyPrice').value = '';
            }
        }

        function closeModal() {
            const modal = document.getElementById('modalStok');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function searchStok() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let items = document.getElementsByClassName('product-item');
            for (let item of items) {
                let name = item.querySelector('.product-name').innerText.toLowerCase();
                item.style.display = name.includes(input) ? "" : "none";
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>