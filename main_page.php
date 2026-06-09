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

// 2. Set Filter Waktu (Hari, Minggu, Bulan)
$filter = $_GET['filter'] ?? 'bulan'; // Default bulan
$date_condition = "";

if ($filter == 'hari') {
    $date_condition = "DATE(created_at) = CURRENT_DATE()";
} elseif ($filter == 'minggu') {
    $date_condition = "YEARWEEK(created_at, 1) = YEARWEEK(CURRENT_DATE(), 1)";
} else {
    $date_condition = "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
}

// 3. Ambil dan Kelompokkan Data untuk Grafik & Total Nominal
$pemasukan = 0;
$pengeluaran = 0;
$keuntungan = 0;

// Siapkan wadah (array) untuk grafik berdasarkan filter
$chart_labels = [];
$chart_in = [];
$chart_out = [];

if ($filter == 'hari') {
    // Jika Hari Ini, kita bagi jadi 4 waktu
    $chart_labels = ['Pagi (06-11)', 'Siang (12-14)', 'Sore (15-17)', 'Malam (18-23)'];
    $chart_in = [0, 0, 0, 0];
    $chart_out = [0, 0, 0, 0];
} elseif ($filter == 'minggu') {
    // Jika Minggu Ini, H1 (Senin) sampai H7 (Minggu)
    $labels_minggu = [1 => 'H1 (Sen)', 2 => 'H2 (Sel)', 3 => 'H3 (Rab)', 4 => 'H4 (Kam)', 5 => 'H5 (Jum)', 6 => 'H6 (Sab)', 7 => 'H7 (Min)'];
    foreach ($labels_minggu as $k => $v) {
        $chart_labels[$k] = $v;
        $chart_in[$k] = 0;
        $chart_out[$k] = 0;
    }
} else {
    // Jika Bulan Ini, W1 sampai W5
    for ($i = 1; $i <= 5; $i++) {
        $chart_labels[$i] = "W" . $i;
        $chart_in[$i] = 0;
        $chart_out[$i] = 0;
    }
}

// Proses Query Data
if ($business_id > 0) {
    $sql_trans = "SELECT t.type, t.nominal, t.qty, t.product_id, p.sell_price, p.buy_price, t.created_at 
                  FROM transactions t
                  LEFT JOIN products p ON t.product_id = p.id
                  WHERE t.business_id = ? AND $date_condition";
                  
    $stmt_trans = $conn->prepare($sql_trans);
    $stmt_trans->bind_param("i", $business_id);
    $stmt_trans->execute();
    $result_trans = $stmt_trans->get_result();

    while ($row = $result_trans->fetch_assoc()) {
        $timestamp = strtotime($row['created_at']);
        $in = 0;
        $out = 0;
        
        // Kalkulasi Nominal
        if ($row['type'] == 'Pemasukan') {
            if ($row['product_id'] !== null) {
                $in = $row['sell_price'] * $row['qty'];
                $out = $row['buy_price'] * $row['qty']; // Potong Modal
            } else {
                $in = $row['nominal'];
            }
        } elseif ($row['type'] == 'Pengeluaran') {
            $out = $row['nominal'];
        }

        $pemasukan += $in;
        $pengeluaran += $out;

        // Masukkan Nominal ke Keranjang Grafik yang Sesuai
        if ($filter == 'hari') {
            $jam = (int)date('H', $timestamp);
            if ($jam >= 6 && $jam < 12) $idx = 0; // Pagi
            elseif ($jam >= 12 && $jam < 15) $idx = 1; // Siang
            elseif ($jam >= 15 && $jam < 18) $idx = 2; // Sore
            else $idx = 3; // Malam
            
            $chart_in[$idx] += $in;
            $chart_out[$idx] += $out;
        } elseif ($filter == 'minggu') {
            $idx = date('N', $timestamp); // 1 (Senin) - 7 (Minggu)
            $chart_in[$idx] += $in;
            $chart_out[$idx] += $out;
        } else {
            // Logic W1 - W5
            $tanggal = (int)date('j', $timestamp);
            $idx = ceil($tanggal / 7);
            if ($idx > 5) $idx = 5; // Batas maksimal W5
            
            $chart_in[$idx] += $in;
            $chart_out[$idx] += $out;
        }
    }
    
    $keuntungan = $pemasukan - $pengeluaran;
}

// Siapkan Data Javascript
$js_labels = json_encode(array_values($chart_labels));
$js_in = json_encode(array_values($chart_in));
$js_out = json_encode(array_values($chart_out));

// 4. Ambil Produk Terlaris (Top 2)
if ($business_id > 0) {
    $sql_best = "SELECT name, sold_count FROM products WHERE business_id = ? ORDER BY sold_count DESC LIMIT 2";
    $stmt_best = $conn->prepare($sql_best);
    $stmt_best->bind_param("i", $business_id);
    $stmt_best->execute();
    $best_products = $stmt_best->get_result();
} else {
    $best_products = null; 
}

function formatRupiah($angka) {
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                'space-cadet': '#102B53', 'ucla-blue': '#50698D',
                'pink-lavender': '#CEB5D4', 'cyan-azure': '#4E7AB1',
                'air-blue': '#7D9FC0', 'gold-cream': '#e7d3b0',
            }}}
        }
        function showAlert() { document.getElementById('customAlert').classList.remove('hidden'); document.getElementById('customAlert').classList.add('flex'); }
        function closeAlert() { document.getElementById('customAlert').classList.remove('flex'); document.getElementById('customAlert').classList.add('hidden'); }
        function openModal(type) {
            const bizId = <?= $business_id ?>;
            if (bizId === 0) { showAlert(); return; }
            document.getElementById('modalTransaksi').classList.remove('hidden');
            document.getElementById('modalTransaksi').classList.add('flex');
            document.getElementById('modalTitle').innerText = 'Tambah ' + type;
            document.getElementById('modalType').value = type;
        }
        function closeModal() { document.getElementById('modalTransaksi').classList.remove('flex'); document.getElementById('modalTransaksi').classList.add('hidden'); }
        function changeFilter(value) { window.location.href = '?filter=' + value; }
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            alert("Transaksi berhasil dicatat!");
            window.history.replaceState({}, document.title, window.location.pathname + "?filter=" + (urlParams.get('filter') || 'bulan'));
        }
    </script>
    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .premium-card { background: #FFFFFF; border: 1.5px solid rgba(16, 43, 83, 0.08); box-shadow: 0 10px 25px -5px rgba(16, 43, 83, 0.1); }
    </style>
</head>
<body class="bg-slate-200 flex items-center justify-center min-h-screen">
    <div class="w-[360px] h-[740px] bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.2)] border-[8px] border-slate-900 relative overflow-hidden flex flex-col">
        
        <div class="bg-[#4E7AB1] pt-10 pb-5 px-6 relative z-30 border-b-2 border-white/50 shadow-sm">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <?php if (!empty($logo)) : ?>
                        <img src="<?= htmlspecialchars($logo) ?>" class="w-11 h-11 rounded-2xl object-cover shadow-xl border border-white/30" alt="Business Logo">
                    <?php else : ?>
                        <div class="w-11 h-11 bg-space-cadet rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-xl">
                            <?= strtoupper(substr($business_name, 0, 2)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-base font-black text-space-cadet leading-none tracking-tighter"><?= htmlspecialchars($business_name) ?></h1>
                        <p class="text-[9px] font-black text-space-cadet/60 mt-1 uppercase tracking-widest italic">Personal Dashboard</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto pb-32 hide-scrollbar bg-slate-50/50">
            <div class="px-5 mt-5">
                <div class="premium-card p-5 rounded-[35px] border-b-4 border-space-cadet/10">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-[11px] font-black text-space-cadet uppercase tracking-widest">Statistik</h2>
                        
                        <div class="flex items-center gap-2">
                            <select onchange="changeFilter(this.value)" class="text-[9px] bg-slate-100 rounded-full px-3 py-1.5 font-black text-ucla-blue outline-none cursor-pointer border border-slate-200">
                                <option value="hari" <?= $filter == 'hari' ? 'selected' : '' ?>>HARI INI</option>
                                <option value="minggu" <?= $filter == 'minggu' ? 'selected' : '' ?>>MINGGU INI</option>
                                <option value="bulan" <?= $filter == 'bulan' ? 'selected' : '' ?>>BULAN INI</option>
                            </select>
                            
                            <a href="download_report.php?filter=<?= $filter ?>" target="_blank" class="w-7 h-7 bg-space-cadet text-white rounded-full flex items-center justify-center shadow-md hover:bg-space-cadet/90 transition" title="Cetak Laporan">
                                <i class="fa-solid fa-print text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    <div class="w-full h-44 bg-slate-50 rounded-3xl p-3 flex items-center justify-center border border-slate-100 relative">
                        <canvas id="financeChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="mt-5 px-5 grid grid-cols-3 gap-3">
                <div class="premium-card p-4 rounded-2xl text-center border-b-4 border-cyan-azure">
                    <p class="text-[8px] font-black text-ucla-blue/60 uppercase mb-1 leading-none">Masuk</p>
                    <p class="text-[11px] font-black text-space-cadet mt-1"><?= formatRupiah($pemasukan) ?></p>
                </div>
                <div class="premium-card p-4 rounded-2xl text-center border-b-4 border-pink-lavender">
                    <p class="text-[8px] font-black text-ucla-blue/60 uppercase mb-1 leading-none">Keluar</p>
                    <p class="text-[11px] font-black text-space-cadet mt-1"><?= formatRupiah($pengeluaran) ?></p>
                </div>
                <div class="premium-card p-4 rounded-2xl text-center border-b-4 border-ucla-blue bg-slate-50">
                    <p class="text-[8px] font-black text-ucla-blue/60 uppercase mb-1 leading-none">Untung</p>
                    <p class="text-[11px] font-black text-space-cadet mt-1"><?= formatRupiah($keuntungan) ?></p>
                </div>
            </div>

            <div class="px-5 mt-5 grid grid-cols-2 gap-4">
                <button onclick="openModal('Pemasukan')" class="p-5 rounded-[30px] flex flex-col items-center bg-cyan-azure/70 border-2 border-white/20 active:scale-95 transition-all group shadow-lg w-full">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center mb-3 transition-all group-hover:bg-white group-hover:text-cyan-azure">
                        <i class="fa-solid fa-plus-circle text-2xl"></i>
                    </div>
                    <span class="text-[11px] font-black text-space-cadet uppercase tracking-tighter">Tambah Pemasukan</span>
                </button>
                <button onclick="openModal('Pengeluaran')" class="p-5 rounded-[30px] flex flex-col items-center bg-pink-lavender/70 border-2 border-white/20 active:scale-95 transition-all group shadow-lg w-full">
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
                                <th class="px-6 py-4 text-[10px] font-black text-white uppercase tracking-widest">Nama Produk</th>
                                <th class="px-6 py-4 text-[10px] font-black text-white uppercase tracking-widest text-right">Terjual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if ($best_products && $best_products->num_rows > 0) : ?>
                                <?php while ($row = $best_products->fetch_assoc()) : ?>
                                    <tr class="bg-white hover:bg-slate-50 transition">
                                        <td class="px-6 py-5 text-xs font-black text-ucla-blue italic"><?= htmlspecialchars($row['name']) ?></td>
                                        <td class="px-6 py-5 text-xs font-black text-space-cadet text-right"><?= $row['sold_count'] ?> pcs</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr><td colspan="2" class="px-6 py-5 text-[10px] text-center text-ucla-blue/50 italic">Belum ada data penjualan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 w-full bg-white px-8 py-6 flex justify-between items-center z-50 rounded-b-[40px] shadow-[0_-10px_40px_rgba(0,0,0,0.08)] border-t border-slate-100">
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

        <div id="modalTransaksi" class="absolute inset-0 bg-space-cadet/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-6">
            <div class="bg-white w-full max-w-[320px] rounded-[40px] p-8 shadow-2xl relative">
                <h2 id="modalTitle" class="font-black text-space-cadet mb-6 uppercase tracking-widest text-center">Tambah Transaksi</h2>
                <form action="proses_transaksi.php" method="POST">
                    <input type="hidden" name="business_id" value="<?= $business_id ?>">
                    <input type="hidden" id="modalType" name="type" value="Pemasukan">
                    <div class="mb-5">
                        <label class="text-[9px] font-black text-ucla-blue/50 uppercase ml-2">Nominal (Rp)</label>
                        <input type="number" name="nominal" required class="w-full bg-slate-100 rounded-2xl px-5 py-4 mt-1 font-black text-space-cadet outline-none focus:ring-2 focus:ring-cyan-azure transition">
                    </div>
                    <div class="mb-5">
                        <label class="text-[9px] font-black text-ucla-blue/50 uppercase ml-2">Keterangan</label>
                        <textarea name="description" rows="2" class="w-full bg-slate-100 rounded-2xl px-5 py-3 mt-1 font-black text-space-cadet outline-none focus:ring-2 focus:ring-cyan-azure transition"></textarea>
                    </div>
                    <div class="mb-8">
                        <label class="text-[9px] font-black text-ucla-blue/50 uppercase ml-2">Tanggal</label>
                        <input type="date" name="created_at" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-100 rounded-2xl px-5 py-4 mt-1 font-black text-space-cadet outline-none">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal()" class="flex-1 py-4 bg-slate-100 text-ucla-blue rounded-2xl font-black uppercase text-[10px]">Batal</button>
                        <button type="submit" class="flex-1 py-4 bg-space-cadet text-white rounded-2xl font-black uppercase text-[10px] shadow-lg shadow-space-cadet/20">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="customAlert" class="absolute inset-0 z-[110] hidden items-center justify-center px-6 bg-space-cadet/20 backdrop-blur-[2px] rounded-[40px]">
            <div class="bg-white w-[85%] rounded-[35px] p-8 shadow-2xl border border-white/50 text-center">
                <div class="w-14 h-14 bg-pink-lavender/30 text-space-cadet rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-store-slash text-xl"></i>
                </div>
                <h3 class="font-black text-space-cadet uppercase tracking-widest text-[11px] mb-2">Profil Belum Ada</h3>
                <p class="text-[9px] font-bold text-ucla-blue/70 leading-relaxed mb-6">Wah, kamu belum mengisi informasi dasar usaha. Silahkan lengkapi Nama Usaha dan Lokasi di menu Profil terlebih dahulu ya!</p>
                <button onclick="closeAlert()" class="w-full py-3.5 bg-space-cadet text-white rounded-2xl font-black uppercase text-[9px] shadow-lg active:scale-95 transition-all">Siap, Mengerti!</button>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('financeChart').getContext('2d');
        const labelsData = <?= $js_labels ?>;
        const pemasukanData = <?= $js_in ?>;
        const pengeluaranData = <?= $js_out ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labelsData,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: pemasukanData,
                        backgroundColor: '#4E7AB1',
                        borderRadius: 4,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Pengeluaran',
                        data: pengeluaranData,
                        backgroundColor: '#CEB5D4',
                        borderRadius: 4,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'top', 
                        labels: { font: { size: 9, family: 'sans-serif', weight: 'bold' }, usePointStyle: true, boxWidth: 6 } 
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': Rp ';
                                label += context.raw.toLocaleString('id-ID');
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        border: { display: false }, 
                        grid: { color: '#f1f5f9' }, 
                        title: {
                            display: true,
                            text: 'Nominal (Rp)',
                            font: { size: 9, weight: 'bold' },
                            color: '#50698D'
                        },
                        ticks: { 
                            font: { size: 8 },
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + 'M';
                                if (value >= 1000) return (value / 1000) + 'K';
                                return value;
                            }
                        } 
                    },
                    x: { 
                        border: { display: false }, 
                        grid: { display: false }, 
                        title: {
                            display: true,
                            text: 'Periode Waktu',
                            font: { size: 9, weight: 'bold' },
                            color: '#50698D'
                        },
                        ticks: { font: { size: 8 } } 
                    }
                }
            }
        });
    </script>
</body>
</html>