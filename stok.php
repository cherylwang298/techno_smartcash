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

// Kita sebutkan nama kolomnya satu per satu agar PASTI terambil sesuai DB
$sql_stok = "SELECT id, name, category, buy_price, sell_price, stock, image_path 
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash - Stok Pro Dashboard</title>
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
                        'gold-cream': '#e7d3b0',
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

        @keyframes alert-pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1.1);
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
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

        .premium-card {
            background: #FFFFFF;
            box-shadow: 0 12px 30px -10px rgba(16, 43, 83, 0.15);
            border: 2px solid #F1F5F9;
        }

        .alert-animate {
            animation: alert-pulse 2s infinite;
        }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-2xl border-[8px] border-slate-900 relative overflow-hidden flex flex-col">

        <div class="bg-animasi-smartcash pt-10 pb-5 px-6 relative z-30 border-b-2 border-white/50 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <input type="text" id="searchInput" onkeyup="searchStok()" placeholder="Cari produk..."
                        class="w-full pl-4 pr-4 py-3.5 bg-white/90 rounded-2xl text-sm font-black text-space-cadet outline-none shadow-sm">
                </div>
                <button onclick="openModal('add')" class="w-12 h-12 bg-space-cadet text-white rounded-2xl flex items-center justify-center shadow-lg active:scale-90 transition">
                    <i class="fa-solid fa-plus text-lg"></i>
                </button>
            </div>

            <div class="flex gap-3 mt-5 overflow-x-auto hide-scrollbar pb-2 px-1">
                <?php
                $categories = ['Semua', 'Makanan', 'Minuman', 'Snack', 'Dessert'];
                foreach ($categories as $cat) :
                    $isActive = ($category_filter === $cat) ? 'bg-space-cadet text-white' : 'bg-white/60 text-space-cadet border border-white';
                ?>
                    <a href="stok.php?category=<?= $cat ?>"
                        class="flex-none px-6 py-2.5 <?= $isActive ?> text-[10px] font-black rounded-full shadow-md uppercase tracking-widest whitespace-nowrap">
                        <?= $cat ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto pb-32 hide-scrollbar bg-slate-50/50 p-5">
            <div class="grid grid-cols-2 gap-4" id="stokList">
                <?php while ($row = $products->fetch_assoc()) :
                    $isLow = ($row['stock'] <= 5);
                    $isEmpty = ($row['stock'] <= 0);
                ?>
                    <div onclick="openModal('edit', '<?= $row['id'] ?>', '<?= htmlspecialchars($row['name']) ?>', '<?= $row['stock'] ?>', '<?= $row['sell_price'] ?>', '<?= $row['buy_price'] ?>', '<?= $row['category'] ?>')"
                        class="product-item premium-card rounded-[35px] overflow-hidden flex flex-col cursor-pointer active:scale-95 transition-all <?= $isLow ? 'border-red-200 bg-red-50/30' : '' ?>">

                        <div class="w-full h-28 bg-slate-100 relative">
                            <?php
                            // Gunakan image_path sesuai kolom di database kamu
                            $path_foto = (!empty($row['image_path']) && file_exists($row['image_path']))
                                ? $row['image_path']
                                : 'https://via.placeholder.com/300';
                            ?>
                            <img src="<?= $path_foto ?>" class="w-full h-full object-cover <?= $isEmpty ? 'grayscale' : '' ?>">

                            <?php if ($isEmpty) : ?>
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    <span class="bg-white text-black text-[10px] font-black px-3 py-1 rounded-lg rotate-12">HABIS</span>
                                </div>
                            <?php elseif ($isLow) : ?>
                                <div class="absolute top-2 right-2 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg alert-animate">
                                    <i class="fa-solid fa-triangle-exclamation text-[12px]"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-4 flex flex-col gap-3">
                            <h3 class="product-name text-[13px] font-black text-space-cadet uppercase tracking-tighter leading-tight"><?= htmlspecialchars($row['name']) ?></h3>
                            <div class="space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-black text-cyan-azure uppercase">Beli</span>
                                    <span class="text-[11px] font-black text-cyan-azure">Rp <?= number_format($row['buy_price'], 0, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-black text-space-cadet/80 uppercase">Jual</span>
                                    <span class="text-[11px] font-black text-space-cadet/80">Rp <?= number_format($row['sell_price'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                            <div class="mt-1 py-3 <?= $isLow ? 'bg-red-100 text-red-600' : 'bg-slate-50 text-ucla-blue' ?> rounded-2xl flex items-center justify-center border border-slate-100">
                                <span class="text-[12px] font-black uppercase tracking-widest">Stok: <?= $row['stock'] ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div id="modalStok" class="hidden absolute inset-0 bg-space-cadet/40 backdrop-blur-sm z-[60] flex items-end transition-all duration-300">
            <div class="w-full bg-white rounded-t-[40px] px-6 pt-4 pb-8 shadow-2xl">
                <div class="w-10 h-1 bg-slate-200 rounded-full mx-auto mb-6"></div>
                <h3 id="modalTitle" class="text-lg font-black text-space-cadet mb-6 text-center uppercase tracking-widest">Edit Produk</h3>

                <form action="proses_stok.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="product_id" id="formId">
                    <input type="hidden" name="action_type" id="formAction">
                    <input type="hidden" name="business_id" value="<?= $business_id ?>">

                    <div class="space-y-3">
                        <div class="bg-slate-50 rounded-xl px-4 py-2.5">
                            <label class="block text-[9px] font-black text-ucla-blue/60 uppercase mb-1">Nama Produk</label>
                            <input id="formName" name="name" type="text" required class="w-full bg-transparent outline-none font-bold text-space-cadet text-base">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-50 rounded-xl px-4 py-2.5">
                                <label class="block text-[9px] font-black text-ucla-blue/60 uppercase mb-1">Sisa Stok</label>
                                <input id="formStock" name="stock" type="number" required class="w-full bg-transparent outline-none font-bold text-space-cadet text-base">
                            </div>
                            <div class="bg-slate-50 rounded-xl px-4 py-2.5">
                                <label class="block text-[9px] font-black text-ucla-blue/60 uppercase mb-1">Kategori</label>
                                <select id="formCategory" name="category" class="w-full bg-transparent outline-none font-bold text-space-cadet text-base">
                                    <option value="Makanan">Makanan</option>
                                    <option value="Minuman">Minuman</option>
                                    <option value="Snack">Snack</option>
                                    <option value="Dessert">Dessert</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-50 rounded-xl px-4 py-2.5 border border-pink-500/30">
                                <label class="block text-[9px] font-black text-pink-600 uppercase mb-1">Harga Beli (Rp)</label>
                                <input id="formBuyPrice" name="purchase_price" type="number" required class="w-full bg-transparent outline-none font-bold text-space-cadet text-base">
                            </div>
                            <div class="bg-slate-50 rounded-xl px-4 py-2.5 border border-cyan-500/30">
                                <label class="block text-[9px] font-black text-cyan-600 uppercase mb-1">Harga Jual (Rp)</label>
                                <input id="formPrice" name="price" type="number" required class="w-full bg-transparent outline-none font-bold text-space-cadet text-base">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="w-full h-24 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-ucla-blue/40 hover:bg-slate-100 transition-all cursor-pointer relative overflow-hidden">
                            <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImage(this)">
                            <div id="uploadPlaceholder" class="flex flex-col items-center">
                                <i class="fa-solid fa-camera text-xl mb-1"></i>
                                <span class="text-[9px] font-black uppercase">Upload Foto Produk</span>
                            </div>
                            <img id="imagePreview" class="hidden absolute inset-0 w-full h-full object-cover">
                        </div>
                    </div>

                    <div class="flex flex-col gap-2.5 mt-6">
                        <button type="submit" class="w-full py-4 bg-space-cadet text-white rounded-xl font-black text-[12px] uppercase tracking-widest shadow-lg active:scale-95 transition-all">
                            Simpan Data
                        </button>
                        <button onclick="closeModal()" type="button" class="w-full py-3 bg-slate-100 text-ucla-blue rounded-xl font-black text-[11px] uppercase tracking-widest">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="absolute bottom-0 w-full bg-white/95 backdrop-blur-md border-t border-slate-100 px-8 py-6 flex justify-between items-center z-50 rounded-b-[40px]">
            <a href="kasir.php" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-cash-register text-xl"></i><span class="text-[9px] font-black mt-1.5 uppercase">Kasir</span></a>
            <a href="main_page.php" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-house-chimney text-xl"></i><span class="text-[9px] font-black mt-1.5 uppercase">Beranda</span></a>
            <a href="stok.php" class="flex flex-col items-center text-space-cadet relative">
                <i class="fa-solid fa-box-open text-xl"></i>
                <span class="text-[9px] font-black mt-1.5 uppercase tracking-widest">Stok</span>
                <div class="absolute -bottom-2 w-1.5 h-1.5 bg-space-cadet rounded-full"></div>
            </a>
            <a href="profile.php" class="flex flex-col items-center text-ucla-blue/30"><i class="fa-solid fa-circle-user text-xl"></i><span class="text-[9px] font-black mt-1.5 uppercase">Profil</span></a>
        </div>
    </div>

    <script>
        function openModal(mode, id = '', name = '', stock = '', price = '', buyPrice = '', category = '') {
            const modal = document.getElementById('modalStok');
            const title = document.getElementById('modalTitle');

            modal.classList.remove('hidden');
            document.getElementById('formAction').value = mode;

            if (mode === 'edit') {
                title.innerText = "Edit Produk";
                document.getElementById('formId').value = id;
                document.getElementById('formName').value = name;
                document.getElementById('formStock').value = stock;
                document.getElementById('formPrice').value = price;
                document.getElementById('formBuyPrice').value = buyPrice;
                document.getElementById('formCategory').value = category;
            } else {
                title.innerText = "Tambah Produk";
                document.getElementById('formId').value = '';
                document.getElementById('formName').value = '';
                document.getElementById('formStock').value = '';
                document.getElementById('formPrice').value = '';
                document.getElementById('formBuyPrice').value = '';
            }
        }

        function closeModal() {
            document.getElementById('modalStok').classList.add('hidden');
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