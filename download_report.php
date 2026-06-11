<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Akses ditolak. Silahkan login terlebih dahulu.");
}

// 1. Data Bisnis (Ambil detail alamat & telepon)
$sql_biz = "SELECT id, business_name, address, city, phone_number FROM businesses WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql_biz);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();

$business_id = $business['id'] ?? 0;
$business_name = $business['business_name'] ?? "Bisnis Saya";
$address = $business['address'] ?? "-";
$city = $business['city'] ?? "-";
$phone = $business['phone_number'] ?? "-";

// 2. Tangkap Filter & Siapkan Variabel Chart
$filter = $_GET['filter'] ?? 'bulan';
$date_condition = "";
$judul_laporan = "";

$chart_labels = [];
$chart_in = [];
$chart_out = [];

if ($filter == 'hari') {
    $date_condition = "DATE(t.created_at) = CURRENT_DATE()";
    $judul_laporan = "Laporan Keuangan Harian";
    $periode_txt = date('d F Y');
    $chart_labels = ['Pagi (06-11)', 'Siang (12-14)', 'Sore (15-17)', 'Malam (18-23)'];
    $chart_in = [0, 0, 0, 0]; $chart_out = [0, 0, 0, 0];
} elseif ($filter == 'minggu') {
    $date_condition = "YEARWEEK(t.created_at, 1) = YEARWEEK(CURRENT_DATE(), 1)";
    $judul_laporan = "Laporan Keuangan Mingguan";
    $periode_txt = "Minggu Ini";
    $labels_minggu = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
    foreach ($labels_minggu as $k => $v) { $chart_labels[$k] = $v; $chart_in[$k] = 0; $chart_out[$k] = 0; }
} else {
    $date_condition = "MONTH(t.created_at) = MONTH(CURRENT_DATE()) AND YEAR(t.created_at) = YEAR(CURRENT_DATE())";
    $judul_laporan = "Laporan Keuangan Bulanan";
    $periode_txt = date('F Y');
    for ($i = 1; $i <= 5; $i++) { $chart_labels[$i] = "Minggu " . $i; $chart_in[$i] = 0; $chart_out[$i] = 0; }
}

// 3. Ambil Data Transaksi
$sql_trans = "SELECT t.type, t.nominal, t.description, t.created_at, t.qty, t.product_id, p.name as product_name, p.sell_price, p.buy_price 
              FROM transactions t
              LEFT JOIN products p ON t.product_id = p.id
              WHERE t.business_id = ? AND $date_condition 
              ORDER BY t.created_at ASC";
              
$stmt_trans = $conn->prepare($sql_trans);
$stmt_trans->bind_param("i", $business_id);
$stmt_trans->execute();
$transactions = $stmt_trans->get_result();

$total_pemasukan = 0;
$total_pengeluaran = 0;

// Kalkulasi untuk Summary & Chart (tanpa echo dulu)
$trans_data = [];
if ($transactions->num_rows > 0) {
    while ($row = $transactions->fetch_assoc()) {
        $trans_data[] = $row; // Simpan ke array untuk tabel nanti
        
        $timestamp = strtotime($row['created_at']);
        $in = 0; $out = 0;

        // Hitung nominal per baris
        if ($row['type'] == 'Pemasukan' && $row['product_id'] !== null) {
            $in = $row['sell_price'] * $row['qty'];
            $out = $row['buy_price'] * $row['qty'];
        } elseif ($row['type'] == 'Pemasukan') {
            $in = $row['nominal'];
        } elseif ($row['type'] == 'Pengeluaran') {
            $out = $row['nominal'];
        }

        $total_pemasukan += $in;
        $total_pengeluaran += $out;

        // Masukkan ke array Chart
        if ($filter == 'hari') {
            $jam = (int)date('H', $timestamp);
            if ($jam >= 6 && $jam < 12) $idx = 0; elseif ($jam >= 12 && $jam < 15) $idx = 1; elseif ($jam >= 15 && $jam < 18) $idx = 2; else $idx = 3;
            $chart_in[$idx] += $in; $chart_out[$idx] += $out;
        } elseif ($filter == 'minggu') {
            $idx = date('N', $timestamp);
            $chart_in[$idx] += $in; $chart_out[$idx] += $out;
        } else {
            $tanggal = (int)date('j', $timestamp);
            $idx = ceil($tanggal / 7); if ($idx > 5) $idx = 5;
            $chart_in[$idx] += $in; $chart_out[$idx] += $out;
        }
    }
}
$keuntungan_bersih = $total_pemasukan - $total_pengeluaran;

function formatRupiah($angka) {
    $prefix = $angka < 0 ? "- Rp " : "Rp ";
    return $prefix . number_format(abs($angka), 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judul_laporan ?> - <?= htmlspecialchars($business_name) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .page-container { max-width: 900px; margin: 20px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        @media print {
            body { background-color: white; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page-container { box-shadow: none; padding: 0; margin: 0; max-width: 100%; border-radius: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="text-slate-800">

    <div class="max-w-[900px] mx-auto mt-6 flex justify-between no-print px-4">
        <a href="main_page.php?filter=<?= $filter ?>" class="bg-slate-300 text-slate-700 px-5 py-2 rounded-lg font-bold text-sm hover:bg-slate-400 transition">KEMBALI</a>
        <button onclick="window.print()" class="bg-[#102B53] text-white px-5 py-2 rounded-lg font-bold text-sm hover:bg-[#102B53]/90 transition shadow-lg flex items-center gap-2">
            CETAK / SIMPAN PDF
        </button>
    </div>

    <div class="page-container">
        <div class="flex justify-between items-start border-b-4 border-[#102B53] pb-6 mb-8">
            <div>
                <h1 class="text-3xl font-black text-[#102B53] uppercase tracking-tight"><?= htmlspecialchars($business_name) ?></h1>
                <p class="text-sm font-semibold text-slate-500 mt-1 uppercase tracking-widest"><?= $judul_laporan ?></p>
            </div>
            <div class="text-right text-sm text-slate-600">
                <p class="font-bold text-slate-800">PERIODE: <?= strtoupper($periode_txt) ?></p>
                <p class="mt-2"><?= htmlspecialchars($address) ?>, <?= htmlspecialchars($city) ?></p>
                <p>Telp: <?= htmlspecialchars($phone) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl border-l-4 border-l-[#4E7AB1]">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Total Pemasukan</p>
                <p class="text-xl font-black text-[#102B53]"><?= formatRupiah($total_pemasukan) ?></p>
            </div>
            <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl border-l-4 border-l-[#CEB5D4]">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Total Modal/Pengeluaran</p>
                <p class="text-xl font-black text-[#102B53]"><?= formatRupiah($total_pengeluaran) ?></p>
            </div>
            <div class="bg-[#102B53] text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-xs font-bold text-white/70 uppercase tracking-widest mb-1">Keuntungan Bersih</p>
                    <p class="text-xl font-black"><?= formatRupiah($keuntungan_bersih) ?></p>
                </div>
            </div>
        </div>

        <div class="mb-10 page-break">
            <h3 class="text-sm font-bold text-[#102B53] uppercase tracking-widest mb-4 border-b pb-2">Grafik Kinerja Keuangan</h3>
            <div class="w-full h-64 bg-slate-50 border border-slate-200 rounded-2xl p-4">
                <canvas id="reportChart"></canvas>
            </div>
        </div>

        <div class="page-break">
            <h3 class="text-sm font-bold text-[#102B53] uppercase tracking-widest mb-4 border-b pb-2">Rincian Transaksi</h3>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#102B53] text-white">
                        <th class="py-3 px-4 text-xs font-bold uppercase tracking-wider w-[5%] rounded-tl-lg">No</th>
                        <th class="py-3 px-4 text-xs font-bold uppercase tracking-wider w-[20%]">Tanggal</th>
                        <th class="py-3 px-4 text-xs font-bold uppercase tracking-wider w-[35%]">Keterangan</th>
                        <th class="py-3 px-4 text-xs font-bold uppercase tracking-wider w-[20%] text-right">Pemasukan</th>
                        <th class="py-3 px-4 text-xs font-bold uppercase tracking-wider w-[20%] text-right rounded-tr-lg">Pengeluaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php 
                    $no = 1;
                    if (count($trans_data) > 0) :
                        foreach ($trans_data as $row) : 
                            $masuk_txt = "-";
                            $keluar_txt = "-";
                            $deskripsi = htmlspecialchars($row['description']);
                            
                            // Jika Penjualan Produk (Ada Product ID)
                            if ($row['type'] == 'Pemasukan' && $row['product_id'] !== null) {
                                $nilai_masuk = $row['sell_price'] * $row['qty'];
                                $nilai_modal = $row['buy_price'] * $row['qty'];
                                $masuk_txt = formatRupiah($nilai_masuk);
                                $keluar_txt = formatRupiah($nilai_modal) . "<br><span class='text-[10px] text-slate-400 italic'>(Modal Barang)</span>";
                                $deskripsi .= " <span class='font-bold text-[#102B53]'>(" . $row['qty'] . "x " . htmlspecialchars($row['product_name']) . ")</span>";
                            } 
                            // Jika Pemasukan Manual
                            elseif ($row['type'] == 'Pemasukan') {
                                $masuk_txt = formatRupiah($row['nominal']);
                            } 
                            // Jika Pengeluaran Manual
                            elseif ($row['type'] == 'Pengeluaran') {
                                $keluar_txt = formatRupiah($row['nominal']);
                            }
                    ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 text-sm text-slate-600 text-center"><?= $no++ ?></td>
                            <td class="py-3 px-4 text-sm text-slate-600"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td class="py-3 px-4 text-sm text-slate-800"><?= $deskripsi ?></td>
                            <td class="py-3 px-4 text-sm font-semibold text-[#4E7AB1] text-right"><?= $masuk_txt ?></td>
                            <td class="py-3 px-4 text-sm font-semibold text-[#CEB5D4] text-right"><?= $keluar_txt ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-sm text-slate-400 italic">Tidak ada transaksi pada periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-12 text-center text-xs text-slate-400 border-t pt-4">
            Dokumen ini dicetak secara otomatis oleh Sistem SimplyCash pada <?= date('d F Y, H:i') ?> WIB.
        </div>
    </div>

    <script>
        const ctx = document.getElementById('reportChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_values($chart_labels)) ?>,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: <?= json_encode(array_values($chart_in)) ?>,
                        backgroundColor: '#4E7AB1',
                        borderRadius: 4,
                    },
                    // check
                    {
                        label: 'Pengeluaran (Modal / Operasional)',
                        data: <?= json_encode(array_values($chart_out)) ?>,
                        backgroundColor: '#CEB5D4',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    // Memicu Print Otomatis setelah grafik selesai dirender (animasi selesai)
                    onComplete: function() {
                        if(!window.printed) {
                            window.printed = true;
                            setTimeout(() => { window.print(); }, 500);
                        }
                    }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 11, weight: 'bold' }, usePointStyle: true } }
                },
                scales: {
                    y: { 
                        beginAtZero: true, grid: { color: '#e2e8f0' }, border: { display: false },
                        ticks: { font: { size: 10, family: 'Inter' }, callback: function(value) { if (value >= 1000000) return (value / 1000000) + 'M'; if (value >= 1000) return (value / 1000) + 'K'; return value; } }
                    },
                    x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11, family: 'Inter', weight: '600' } } }
                }
            }
        });
        
        // Fallback jika grafik gagal memicu print
        setTimeout(() => {
            if(!window.printed) { window.print(); }
        }, 2000);
    </script>
</body>
</html>