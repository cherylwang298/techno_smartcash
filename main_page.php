<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// 1. Data Bisnis
$sql_biz = "SELECT id, business_name, logo FROM businesses WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql_biz);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$business = $result->fetch_assoc();

$business_id = $business['id'] ?? 0;
$business_name = $business['business_name'] ?? "Bisnis Saya";
$logo = $business['logo'] ?? "";

// 2. Ambil Total Pemasukan & Pengeluaran (Bulan Ini)
$pemasukan = 0;
$pengeluaran = 0;
$keuntungan = 0;

if ($business_id > 0) {
    $sql_stats = "SELECT 
        SUM(CASE WHEN type = 'Pemasukan' THEN nominal ELSE 0 END) as total_in,
        SUM(CASE WHEN type = 'Pengeluaran' THEN nominal ELSE 0 END) as total_out
        FROM transactions WHERE business_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    $stmt_stats = $conn->prepare($sql_stats);
    $stmt_stats->bind_param("i", $business_id);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();

    $pemasukan = $stats['total_in'] ?? 0;
    $pengeluaran = $stats['total_out'] ?? 0;
    $keuntungan = $pemasukan - $pengeluaran;
}

// 3. Ambil Produk Terlaris (Top 2)
if ($business_id > 0) {
    $sql_best = "SELECT name, sold_count FROM products WHERE business_id = ? ORDER BY sold_count DESC LIMIT 2";
    $stmt_best = $conn->prepare($sql_best);
    $stmt_best->bind_param("i", $business_id);
    $stmt_best->execute();
    $best_products = $stmt_best->get_result();
} else {
    $best_products = $result; 
}

function formatRupiah($angka)
{
    $prefix = $angka < 0 ? "-" : "";
    $angka = abs($angka);
    if ($angka >= 1000000) return $prefix . number_format($angka / 1000000, 1) . 'M';
    if ($angka >= 1000) return $prefix . number_format($angka / 1000, 0) . 'K';
    return $prefix . number_format($angka, 0);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash | Dashboard</title>
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

        function showAlert() {
            const alertBox = document.getElementById('customAlert');
            alertBox.classList.remove('hidden');
            alertBox.classList.add('flex');
        }

        function closeAlert() {
            const alertBox = document.getElementById('customAlert');
            alertBox.classList.remove('flex');
            alertBox.classList.add('hidden');
        }

        function openModal(type) {
            const bizId = <?= $business_id ?>;

            if (bizId === 0) {
                showAlert();
                return;
            }

            document.getElementById('modalTransaksi').classList.remove('hidden');
            document.getElementById('modalTransaksi').classList.add('flex');
            document.getElementById('modalTitle').innerText = 'Tambah ' + type;
            document.getElementById('modalType').value = type;
        }
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            alert("Transaksi berhasil dicatat!");
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
    <style>
        @keyframes flowAnimation {
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
            animation: flowAnimation 15s ease infinite;
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
            border: 1.5px solid rgba(16, 43, 83, 0.08);
            box-shadow: 0 10px 25px -5px rgba(16, 43, 83, 0.1);
        }
    </style>
</head>

<body class="bg-slate-200 flex items-center justify-center min-h-screen">

    <div
        class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 relative overflow-hidden flex flex-col">

        <div class="bg-[#4E7AB1] pt-10 pb-5 px-6 relative z-30 border-b-2 border-white/50 shadow-sm">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <?php if (!empty($logo)) : ?>
                        <img src="<?= htmlspecialchars($logo) ?>"
                            class="w-11 h-11 rounded-2xl object-cover shadow-xl border border-white/30"
                            alt="Business Logo">
                    <?php else : ?>
                        <div class="w-11 h-11 bg-space-cadet rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-xl">
                            <?= strtoupper(substr($business_name, 0, 2)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-base font-black text-space-cadet leading-none tracking-tighter">
                            <?= htmlspecialchars($business_name) ?>
                        </h1>
                        <p class="text-[9px] font-black text-space-cadet/60 mt-1 uppercase tracking-widest italic">
                            Personal Dashboard</p>
                    </div>
                </div>
                <button
                    class="w-9 h-9 bg-white/80 rounded-xl flex items-center justify-center text-space-cadet border border-white shadow-sm">
                    <i class="fa-solid fa-bell"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto pb-32 hide-scrollbar bg-slate-50/50">

            <div class="px-5 mt-5">
                <div class="premium-card p-6 rounded-[35px] border-b-4 border-space-cadet/10">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-[11px] font-black text-space-cadet uppercase tracking-widest">Statistik Bisnis
                        </h2>
                        <select
                            class="text-[9px] bg-slate-100 rounded-full px-3 py-1 font-black text-ucla-blue outline-none border-none">
                            <option>HARI INI</option>
                            <option>MINGGU INI</option>
                        </select>
                    </div>

                    <div
                        class="w-full h-32 bg-slate-50 rounded-3xl flex items-end justify-around px-4 pb-4 relative border border-slate-100">
                        <div class="absolute top-3 w-full text-center">
                            <p class="text-[7px] font-black text-ucla-blue/30 uppercase tracking-[0.4em]">Performance
                                Insight</p>
                        </div>
                        <div class="w-3 bg-space-cadet/20 h-[40%] rounded-full"></div>
                        <div class="w-3 bg-space-cadet/40 h-[65%] rounded-full"></div>
                        <div class="w-3 bg-space-cadet h-[90%] rounded-full shadow-lg shadow-space-cadet/20"></div>
                        <div class="w-3 bg-space-cadet/60 h-[75%] rounded-full"></div>
                        <div class="w-3 bg-space-cadet/30 h-[50%] rounded-full"></div>
                        <div class="w-3 bg-space-cadet/10 h-[30%] rounded-full"></div>
                    </div>

                    <div class="flex p-1.5 bg-slate-100 rounded-2xl mt-5">
                        <button
                            class="flex-1 py-2.5 bg-space-cadet text-white rounded-xl text-[9px] font-black uppercase shadow-lg">Masuk</button>
                        <button class="flex-1 py-2.5 text-ucla-blue text-[9px] font-black uppercase">Keluar</button>
                        <button class="flex-1 py-2.5 text-ucla-blue text-[9px] font-black uppercase">Untung</button>
                    </div>
                </div>
            </div>

            <div class="mt-5 px-5 grid grid-cols-3 gap-3">
                <div class="premium-card p-4 rounded-2xl text-center border-b-4 border-cyan-azure">
                    <p class="text-[8px] font-black text-ucla-blue/60 uppercase mb-1 leading-none">Pemasukan</p>
                    <p class="text-[12px] font-black text-space-cadet mt-1"><?= formatRupiah($pemasukan) ?></p>
                </div>

                <div class="premium-card p-4 rounded-2xl text-center border-b-4 border-pink-lavender">
                    <p class="text-[8px] font-black text-ucla-blue/60 uppercase mb-1 leading-none">Pengeluaran</p>
                    <p class="text-[12px] font-black text-space-cadet mt-1"><?= formatRupiah($pengeluaran) ?></p>
                </div>

                <div class="premium-card p-4 rounded-2xl text-center border-b-4 border-ucla-blue bg-slate-50">
                    <p class="text-[8px] font-black text-ucla-blue/60 uppercase mb-1 leading-none">Keuntungan</p>
                    <p class="text-[12px] font-black text-space-cadet mt-1"><?= formatRupiah($keuntungan) ?></p>
                </div>
            </div>

            <div class="px-5 mt-5 grid grid-cols-2 gap-4">

                <button onclick="openModal('Pemasukan')"
                    class="p-5 rounded-[30px] flex flex-col items-center bg-cyan-azure/70 border-2 border-white/20 active:scale-95 transition-all group shadow-lg w-full">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center mb-3 transition-all group-hover:bg-white group-hover:text-cyan-azure">
                        <i class="fa-solid fa-plus-circle text-2xl"></i>
                    </div>
                    <span class="text-[11px] font-black text-space-cadet uppercase tracking-tighter">Tambah Pemasukan</span>
                </button>

                <button onclick="openModal('Pengeluaran')"
                    class="p-5 rounded-[30px] flex flex-col items-center bg-pink-lavender/70 border-2 border-white/20 active:scale-95 transition-all group shadow-lg w-full">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center mb-3 transition-all group-hover:bg-white group-hover:text-pink-lavender">
                        <i class="fa-solid fa-minus-circle text-2xl"></i>
                    </div>
                    <span class="text-[11px] font-black text-space-cadet uppercase tracking-tighter">Tambah Pengeluaran</span>
                </button>
            </div>


            <div class="mt-8 px-5 pb-12">
                <div class="flex items-center gap-2 mb-4 ml-1">
                    <div class="h-4 w-1.5 bg-space-cadet rounded-full"></div>
                    <h3 class="text-[12px] font-black text-space-cadet uppercase tracking-[0.3em]">Terlaris</h3>
                </div>

                <div class="premium-card rounded-[35px] overflow-hidden border-2 border-space-cadet/5">
                    <table class="w-full text-left">
                        <thead class="bg-space-cadet">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-white uppercase tracking-widest">Nama
                                    Produk</th>
                                <th
                                    class="px-6 py-4 text-[10px] font-black text-white uppercase tracking-widest text-right">
                                    Terjual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if ($best_products->num_rows > 0) : ?>
                                <?php while ($row = $best_products->fetch_assoc()) : ?>
                                    <tr class="bg-white hover:bg-slate-50 transition">
                                        <td class="px-6 py-5 text-xs font-black text-ucla-blue italic"><?= htmlspecialchars($row['name']) ?></td>
                                        <td class="px-6 py-5 text-xs font-black text-space-cadet text-right"><?= $row['sold_count'] ?> pcs</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="2" class="px-6 py-5 text-[10px] text-center text-ucla-blue/50 italic">Belum ada data penjualan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            class="absolute bottom-0 w-full bg-white px-8 py-6 flex justify-between items-center z-50 rounded-b-[40px] shadow-[0_-10px_40px_rgba(0,0,0,0.08)] border-t border-slate-100">
            <a href="kasir.php" class="flex flex-col items-center text-ucla-blue/30 hover:text-space-cadet transition">
                <i class="fa-solid fa-cash-register text-xl"></i>
                <span class="text-[9px] font-black mt-1.5 uppercase tracking-tighter">Kasir</span>
            </a>
            <a href="main_page.php" class="flex flex-col items-center text-space-cadet relative">
                <i class="fa-solid fa-house-chimney text-xl"></i>
                <span class="text-[9px] font-black mt-1.5 uppercase tracking-widest">Beranda</span>
                <div class="absolute -bottom-2 w-1.5 h-1.5 bg-space-cadet rounded-full"></div>
            </a>
            <a href="stok.php" class="flex flex-col items-center text-ucla-blue/30 hover:text-space-cadet transition">
                <i class="fa-solid fa-box text-xl"></i>
                <span class="text-[9px] font-black mt-1.5 uppercase tracking-tighter">Stok</span>
            </a>
            <a href="profile.php" class="flex flex-col items-center text-ucla-blue/30 hover:text-space-cadet transition">
                <i class="fa-solid fa-circle-user text-xl"></i>
                <span class="text-[9px] font-black mt-1.5 uppercase tracking-tighter">Profil</span>
            </a>
        </div>

        <div id="modalTransaksi" class="fixed inset-0 bg-space-cadet/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-6">
            <div class="bg-white w-full max-w-[320px] rounded-[40px] p-8 shadow-2xl relative animate-in fade-in zoom-in duration-300">

                <h2 id="modalTitle" class="font-black text-space-cadet mb-6 uppercase tracking-widest text-center">Tambah Transaksi</h2>

                <form action="proses_transaksi.php" method="POST">
                    <input type="hidden" name="business_id" value="<?= $business_id ?>">
                    <input type="hidden" id="modalType" name="type" value="Pemasukan">

                    <div class="mb-5">
                        <label class="text-[9px] font-black text-ucla-blue/50 uppercase ml-2">Nominal (Rp)</label>
                        <input type="number" name="nominal" required
                            class="w-full bg-slate-100 rounded-2xl px-5 py-4 mt-1 font-black text-space-cadet outline-none focus:ring-2 focus:ring-cyan-azure transition">
                    </div>

                    <div class="mb-5">
                        <label class="text-[9px] font-black text-ucla-blue/50 uppercase ml-2">Keterangan</label>
                        <textarea name="description" rows="2"
                            class="w-full bg-slate-100 rounded-2xl px-5 py-3 mt-1 font-black text-space-cadet outline-none focus:ring-2 focus:ring-cyan-azure transition"></textarea>
                    </div>

                    <div class="mb-8">
                        <label class="text-[9px] font-black text-ucla-blue/50 uppercase ml-2">Tanggal</label>
                        <input type="date" name="created_at" value="<?= date('Y-m-d') ?>"
                            class="w-full bg-slate-100 rounded-2xl px-5 py-4 mt-1 font-black text-space-cadet outline-none">
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal()"
                            class="flex-1 py-4 bg-slate-100 text-ucla-blue rounded-2xl font-black uppercase text-[10px]">Batal</button>
                        <button type="submit"
                            class="flex-1 py-4 bg-space-cadet text-white rounded-2xl font-black uppercase text-[10px] shadow-lg shadow-space-cadet/20">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="customAlert" class="absolute inset-0 z-[110] hidden items-center justify-center px-6 bg-space-cadet/20 backdrop-blur-[2px] rounded-[40px]">
            <div class="bg-white w-[85%] rounded-[35px] p-8 shadow-2xl border border-white/50 text-center animate-in zoom-in duration-300">
                <div class="w-14 h-14 bg-pink-lavender/30 text-space-cadet rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-store-slash text-xl"></i>
                </div>

                <h3 class="font-black text-space-cadet uppercase tracking-widest text-[11px] mb-2">Profil Belum Ada</h3>
                <p class="text-[9px] font-bold text-ucla-blue/70 leading-relaxed mb-6">
                    Wah, kamu belum mengisi informasi dasar usaha.
                    Silahkan lengkapi Nama Usaha dan Lokasi di menu Profil terlebih dahulu ya!
                </p>

                <button onclick="closeAlert()"
                    class="w-full py-3.5 bg-space-cadet text-white rounded-2xl font-black uppercase text-[9px] shadow-lg active:scale-95 transition-all">
                    Siap, Mengerti!
                </button>
            </div>
        </div>
    </div>
</body>

</html>