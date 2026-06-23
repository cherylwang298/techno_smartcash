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

// 2. Set Filter Waktu
$filter = $_GET['filter'] ?? 'bulan';
$date_condition = "";

if ($filter == 'hari') {
    $date_condition = "DATE(created_at) = CURRENT_DATE()";
} elseif ($filter == 'minggu') {
    $date_condition = "YEARWEEK(created_at, 1) = YEARWEEK(CURRENT_DATE(), 1)";
} elseif ($filter == 'tahun') {
    $date_condition = "YEAR(created_at) = YEAR(CURRENT_DATE())";
} else {
    $date_condition = "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
}

// 3. Ambil Data Grafik
$pemasukan = 0;
$pengeluaran = 0;
$keuntungan = 0;

$chart_labels = [];
$chart_in = [];
$chart_out = [];

if ($filter == 'hari') {
    $chart_labels = ['Pagi', 'Siang', 'Sore', 'Malam'];
    $chart_in = [0, 0, 0, 0];
    $chart_out = [0, 0, 0, 0];
} elseif ($filter == 'minggu') {
    $labels_minggu = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
    foreach ($labels_minggu as $k => $v) {
        $chart_labels[$k] = $v;
        $chart_in[$k] = 0;
        $chart_out[$k] = 0;
    }
} elseif ($filter == 'tahun') {
    $bulan_singkat = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    for ($i = 1; $i <= 12; $i++) {
        $chart_labels[$i] = $bulan_singkat[$i];
        $chart_in[$i] = 0;
        $chart_out[$i] = 0;
    }
} else {
    for ($i = 1; $i <= 5; $i++) {
        $chart_labels[$i] = "W" . $i;
        $chart_in[$i] = 0;
        $chart_out[$i] = 0;
    }
}

if ($business_id > 0) {
    $sql_trans = "SELECT t.type, t.nominal, t.qty, t.product_id, p.sell_price, p.buy_price, t.created_at, t.description 
                  FROM transactions t
                  LEFT JOIN products p ON t.product_id = p.id
                  WHERE t.business_id = ? AND $date_condition";

    $stmt_trans = $conn->prepare($sql_trans);
    $stmt_trans->bind_param("i", $business_id);
    $stmt_trans->execute();
    $result_trans = $stmt_trans->get_result();

    $list_pemasukan = [];
    $list_pengeluaran = [];
    $list_keuntungan = [];

    while ($row = $result_trans->fetch_assoc()) {
        $timestamp = strtotime($row['created_at']);
        $date_formatted = date('d M Y', $timestamp);
        $desc = $row['description'] ? $row['description'] : '-';
        $in = 0;
        $out = 0;

        if ($row['type'] == 'Pemasukan') {
            if ($row['product_id'] !== null) {
                $in = $row['sell_price'] * $row['qty'];
                $profit_item = ($row['sell_price'] - $row['buy_price']) * $row['qty'];
            } else {
                $in = $row['nominal'];
                $profit_item = $in;
            }
            $list_pemasukan[] = ['date' => $date_formatted, 'desc' => $desc, 'amount' => $in];
            $list_keuntungan[] = ['date' => $date_formatted, 'desc' => $desc, 'amount' => $profit_item, 'is_positive' => true];
        } elseif ($row['type'] == 'Pengeluaran') {
            $out = $row['nominal'];
            $list_pengeluaran[] = ['date' => $date_formatted, 'desc' => $desc, 'amount' => $out];
            $list_keuntungan[] = ['date' => $date_formatted, 'desc' => $desc, 'amount' => $out, 'is_positive' => false];
        }

        $pemasukan += $in;
        $pengeluaran += $out;

        // ... (Biarkan kode logika grafik $filter == 'hari' dkk yang ada di bawahnya tetap sama)
        if ($filter == 'hari') {
            $jam = (int)date('H', $timestamp);
            if ($jam >= 6 && $jam < 12) $idx = 0;
            elseif ($jam >= 12 && $jam < 15) $idx = 1;
            elseif ($jam >= 15 && $jam < 18) $idx = 2;
            else $idx = 3;
            $chart_in[$idx] += $in;
            $chart_out[$idx] += $out;
        } elseif ($filter == 'minggu') {
            $idx = date('N', $timestamp);
            $chart_in[$idx] += $in;
            $chart_out[$idx] += $out;
        } elseif ($filter == 'tahun') {
            $idx = (int)date('n', $timestamp);
            $chart_in[$idx] += $in;
            $chart_out[$idx] += $out;
        } else {
            $tanggal = (int)date('j', $timestamp);
            $idx = ceil($tanggal / 7);
            if ($idx > 5) $idx = 5;
            $chart_in[$idx] += $in;
            $chart_out[$idx] += $out;
        }
    }
    $keuntungan = $pemasukan - $pengeluaran;
}

$js_labels = json_encode(array_values($chart_labels));
$js_in = json_encode(array_values($chart_in));
$js_out = json_encode(array_values($chart_out));

// Tambahkan variabel JS untuk list detail
$js_list_pemasukan = json_encode($list_pemasukan ?? []);
$js_list_pengeluaran = json_encode($list_pengeluaran ?? []);
$js_list_keuntungan = json_encode($list_keuntungan ?? []);

// 4. Ambil Produk Terlaris
if ($business_id > 0) {
    $sql_best = "SELECT name, sold_count FROM products WHERE business_id = ? ORDER BY sold_count DESC LIMIT 2";
    $stmt_best = $conn->prepare($sql_best);
    $stmt_best->bind_param("i", $business_id);
    $stmt_best->execute();
    $best_products = $stmt_best->get_result();
} else {
    $best_products = null;
}

// 5. LOGIKA STOK OPNAME MALAM HARI
date_default_timezone_set('Asia/Jakarta');
$jam_sekarang = (int)date('H');
$is_malam = ($jam_sekarang >= 18 || $jam_sekarang < 6);

$products_opname = [];
if ($is_malam && $business_id > 0) {
    $sql_stok = "SELECT id, name, stock, real_stock FROM products WHERE business_id = ? ORDER BY name ASC";
    $stmt_stok = $conn->prepare($sql_stok);
    $stmt_stok->bind_param("i", $business_id);
    $stmt_stok->execute();
    $result_stok = $stmt_stok->get_result();
    while ($row = $result_stok->fetch_assoc()) {
        $products_opname[] = $row;
    }
}

$opname_js_data = [];
if ($is_malam && !empty($products_opname)) {
    foreach ($products_opname as $p) {
        if ($p['real_stock'] !== null) {
            $opname_js_data[] = ['id' => $p['id'], 'stock' => $p['stock']];
        }
    }
}
$js_opname = json_encode($opname_js_data);

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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcash | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700;800&family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        const chartLabelsData = <?= $js_labels ?>;
        const chartPemasukanData = <?= $js_in ?>;
        const chartPengeluaranData = <?= $js_out ?>;
        const opnameData = <?= $js_opname ?>;

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
                        'blush-pink': '#E8778A',
                        'yellow-gold': '#F0C14B',
                        'mint-green': '#5BBFA3'
                    }
                }
            }
        }

        function openModal(type) {
            document.getElementById('modalTransaksi').classList.remove('hidden');
            document.getElementById('modalTransaksi').classList.add('flex');
            document.getElementById('modalTitle').innerText = 'Tambah ' + type;
            document.getElementById('modalType').value = type;
        }

        function closeModal() {
            document.getElementById('modalTransaksi').classList.remove('flex');
            document.getElementById('modalTransaksi').classList.add('hidden');
        }

        function changeFilter(value) {
            window.location.href = '?filter=' + value;
        }
    </script>

    <style>
        * {
            font-family: 'Nunito', sans-serif;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* DREAMY FLUID BACKGROUND */
        .dreamy-bg {
            background-color: #fdfdfd;
            background-image:
                radial-gradient(at 0% 0%, rgba(206, 181, 212, 0.3) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(125, 159, 192, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(78, 122, 177, 0.2) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(206, 181, 212, 0.25) 0px, transparent 50%);
        }

        /* FLOATING ORBS ANIMATION */
        @keyframes float {
            0% {
                transform: translateY(0px) translateX(0px) scale(1);
            }

            33% {
                transform: translateY(-20px) translateX(15px) scale(1.05);
            }

            66% {
                transform: translateY(15px) translateX(-15px) scale(0.95);
            }

            100% {
                transform: translateY(0px) translateX(0px) scale(1);
            }
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.5;
            z-index: 0;
        }

        .orb-1 {
            width: 150px;
            height: 150px;
            background: #CEB5D4;
            top: -5%;
            left: -20%;
            animation: float 8s ease-in-out infinite;
        }

        .orb-2 {
            width: 180px;
            height: 180px;
            background: #7D9FC0;
            top: 40%;
            right: -20%;
            animation: float 10s ease-in-out infinite reverse;
        }

        .orb-3 {
            width: 120px;
            height: 120px;
            background: #4E7AB1;
            bottom: 10%;
            left: 10%;
            animation: float 7s ease-in-out infinite 1s;
            opacity: 0.3;
        }

        /* PHONE SHELL BORDER */
        .phone-shell {
            border: 12px solid #102B53;
            border-radius: 56px;
            box-shadow: 0 40px 100px rgba(16, 43, 83, 0.2);
        }

        /* GLASSMORPHISM CARDS */
        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 30px rgba(80, 105, 141, 0.06);
            border-radius: 28px;
        }

        .glass-card-sm {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 6px 20px rgba(80, 105, 141, 0.05);
            border-radius: 22px;
            transition: transform 0.3s ease;
        }

        .glass-card-sm:active {
            transform: scale(0.95);
        }

        /* TEXT GRADIENT */
        .text-gradient {
            background: linear-gradient(135deg, #102B53 0%, #4E7AB1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* BUTTON GRADIENTS */
        .btn-dreamy {
            background: linear-gradient(135deg, #81A4CD 0%, #CEB5D4 100%);
            color: #102B53;
            box-shadow: 0 8px 25px rgba(206, 181, 212, 0.4);
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-dreamy:active {
            transform: scale(0.96);
            box-shadow: 0 4px 12px rgba(206, 181, 212, 0.2);
        }

        .btn-dreamy-alt {
            background: linear-gradient(135deg, #4E7AB1 0%, #81A4CD 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(78, 122, 177, 0.3);
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-dreamy-alt:active {
            transform: scale(0.96);
            box-shadow: 0 4px 12px rgba(78, 122, 177, 0.2);
        }

        /* NAVBAR (Floating Glass) */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 40px rgba(16, 43, 83, 0.1);
            border-radius: 999px;
        }

        .nav-item {
            transition: all 0.3s ease;
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(206, 181, 212, 0.25) 0%, rgba(78, 122, 177, 0.15) 100%);
            border-radius: 999px;
        }

        .nav-item.active .nav-icon {
            color: #4E7AB1;
        }

        .nav-item.active .nav-label {
            color: #102B53;
            font-weight: 800;
        }

        .nav-icon {
            color: #7D9FC0;
            font-size: 16px;
            transition: color 0.3s;
        }

        .nav-label {
            font-size: 9px;
            font-weight: 700;
            color: #7D9FC0;
            letter-spacing: 0.02em;
            transition: color 0.3s;
        }

        /* MODAL & INPUTS */
        .modal-input {
            background: rgba(255, 255, 255, 0.8);
            border: 1.5px solid rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            font-size: 13px;
            font-weight: 700;
            color: #102B53;
            padding: 14px 16px;
            outline: none;
            transition: all 0.3s;
            box-shadow: inset 0 2px 5px rgba(80, 105, 141, 0.02);
            width: 100%;
        }

        .modal-input:focus {
            border-color: #CEB5D4;
            background: #fff;
            box-shadow: 0 4px 20px rgba(206, 181, 212, 0.2);
        }

        .modal-input::placeholder {
            color: #7D9FC0;
            font-weight: 600;
            opacity: 0.8;
        }

        /* TABLE INPUTS OPNAME */
        .glass-input {
            background: rgba(255, 255, 255, 0.7);
            border: 1.5px solid rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            text-align: center;
            font-size: 12px;
            font-weight: 800;
            color: #102B53;
            padding: 8px 2px;
            outline: none;
            transition: all 0.2s;
            box-shadow: inset 0 2px 4px rgba(16, 43, 83, 0.02);
        }

        .glass-input:focus {
            border-color: #CEB5D4;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(206, 181, 212, 0.2);
        }

        .filter-select {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            color: #102B53;
            font-size: 10px;
            font-weight: 800;
            border-radius: 999px;
            padding: 6px 12px;
            outline: none;
            cursor: pointer;
            letter-spacing: 0.03em;
        }

        /* Custom Scrollbar Opname */
        .opname-scroll {
            max-height: 180px;
            overflow-y: auto;
            border-radius: 20px;
        }

        .opname-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .opname-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .opname-scroll::-webkit-scrollbar-thumb {
            background: #CEB5D4;
            border-radius: 10px;
        }

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
            outline: none !important;
            transition: all 0.2s ease;
        }

        .swal-gradient-btn:active {
            transform: scale(0.96) !important;
            box-shadow: 0 4px 12px rgba(206, 181, 212, 0.3) !important;
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

        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="pt-12 pb-4 px-6 relative z-30 flex-shrink-0 flex justify-between items-center">
            <div class="flex items-center gap-3.5">

                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-tr from-cyan-azure/50 to-pink-lavender/50 rounded-full blur-md scale-110"></div>
                    <?php if (!empty($logo)) : ?>
                        <img src="<?= htmlspecialchars($logo) ?>" class="relative w-[46px] h-[46px] rounded-full object-cover border-[2.5px] border-white shadow-sm" alt="Logo">
                    <?php else : ?>
                        <div class="relative w-[46px] h-[46px] rounded-full flex items-center justify-center text-cyan-azure font-serif font-black text-xl bg-white border-[2.5px] border-white shadow-sm">
                            <?= strtoupper(substr($business_name, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <p class="text-[10px] font-extrabold text-air-blue tracking-[0.15em] mb-0.5 flex items-center gap-1.5 uppercase">
                        <i class="fa-solid <?= $is_malam ? 'fa-moon text-pink-lavender' : 'fa-sun text-yellow-gold' ?> text-[11px]"></i>
                        Dashboard
                    </p>
                    <h1 class="text-[19px] font-serif font-black text-space-cadet leading-none tracking-tight">
                        <?= htmlspecialchars($business_name) ?>
                    </h1>
                </div>
            </div>

            <button class="relative w-10 h-10 bg-white/50 backdrop-blur-xl rounded-full flex items-center justify-center text-space-cadet shadow-[0_4px_15px_rgba(80,105,141,0.08)] border border-white transition-transform active:scale-95">
                <i class="fa-regular fa-bell text-[16px]"></i>
                <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-blush-pink rounded-full border border-white"></span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto hide-scrollbar px-5 relative z-20" style="padding-bottom: 125px;">

            <div class="mt-6">
                <div class="glass-card p-5">
                    <div class="flex justify-between items-center mb-5">
                        <span class="text-[13px] font-bold text-space-cadet flex items-center gap-2">
                            <i class="fa-solid fa-chart-column text-pink-lavender"></i> Insight Bisnis
                        </span>

                        <div class="flex items-center gap-2">
                            <select onchange="changeFilter(this.value)" class="filter-select shadow-sm">
                                <option value="hari" <?= $filter == 'hari'  ? 'selected' : '' ?>>HARI INI</option>
                                <option value="minggu" <?= $filter == 'minggu' ? 'selected' : '' ?>>MINGGU INI</option>
                                <option value="bulan" <?= $filter == 'bulan' ? 'selected' : '' ?>>BULAN INI</option>
                                <option value="tahun" <?= $filter == 'tahun' ? 'selected' : '' ?>>TAHUN INI</option>
                            </select>

                            <a href="download_report.php?filter=<?= $filter ?>" target="_blank" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-[0_2px_8px_rgba(78,122,177,0.15)] border border-slate-100 hover:bg-slate-50 transition" title="Cetak PDF">
                                <i class="fa-solid fa-print text-cyan-azure text-[12px]"></i>
                            </a>

                            <a href="download_csv.php?filter=<?= $filter ?>" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-[0_2px_8px_rgba(91,191,163,0.15)] border border-slate-100 hover:bg-slate-50 transition" title="Unduh Excel">
                                <i class="fa-solid fa-file-excel text-mint-green text-[12px]"></i>
                            </a>
                        </div>
                    </div>
                    <div style="height:160px; border-radius:20px; padding:5px; background: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.6);">
                        <canvas id="financeChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-3">
                <div onclick="openDetailModal('pemasukan')" class="glass-card-sm p-4 text-center group cursor-pointer hover:bg-white/80 transition-colors">
                    <div class="w-10 h-10 bg-gradient-to-br from-cyan-azure to-[#81A4CD] rounded-full mx-auto mb-2 flex items-center justify-center shadow-[0_6px_15px_rgba(78,122,177,0.3)] group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-arrow-down text-white text-[14px]"></i>
                    </div>
                    <p class="text-[9px] font-bold text-ucla-blue tracking-widest mb-1">Pemasukan</p>
                    <p class="text-[12px] font-black text-space-cadet"><?= formatRupiah($pemasukan) ?></p>
                </div>

                <div onclick="openDetailModal('pengeluaran')" class="glass-card-sm p-4 text-center group cursor-pointer hover:bg-white/80 transition-colors">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-lavender to-[#e1ccdb] rounded-full mx-auto mb-2 flex items-center justify-center shadow-[0_6px_15px_rgba(206,181,212,0.4)] group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-arrow-up text-white text-[14px]"></i>
                    </div>
                    <p class="text-[9px] font-bold text-ucla-blue tracking-widest mb-1">Pengeluaran</p>
                    <p class="text-[12px] font-black text-space-cadet"><?= formatRupiah($pengeluaran) ?></p>
                </div>

                <div onclick="openDetailModal('keuntungan')" class="glass-card-sm p-4 text-center relative overflow-hidden group cursor-pointer hover:bg-white/80 transition-colors">
                    <i class="fa-solid fa-sparkles absolute top-2 right-2 text-yellow-gold/30 text-[12px]"></i>
                    <i class="fa-solid fa-star absolute bottom-3 left-2 text-yellow-gold/20 text-[10px]"></i>

                    <div class="w-10 h-10 bg-gradient-to-br from-mint-green to-[#88d5c2] rounded-full mx-auto mb-2 flex items-center justify-center shadow-[0_6px_15px_rgba(91,191,163,0.3)] group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-coins text-white text-[14px]"></i>
                    </div>
                    <p class="text-[9px] font-bold text-ucla-blue tracking-widest mb-1">Keuntungan</p>
                    <p class="text-[12px] font-black <?= $keuntungan >= 0 ? 'text-mint-green' : 'text-blush-pink' ?>"><?= formatRupiah($keuntungan) ?></p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <button onclick="openModal('Pemasukan')" class="btn-dreamy-alt px-3 py-4 flex items-center justify-center gap-2.5">
                    <div class="w-9 h-9 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center flex-shrink-0 border border-white/30">
                        <i class="fa-solid fa-plus text-white text-base"></i>
                    </div>
                    <span class="text-[11px] font-bold text-white leading-tight text-left">Catat<br>Pemasukan</span>
                </button>
                <button onclick="openModal('Pengeluaran')" class="btn-dreamy px-3 py-4 flex items-center justify-center gap-2.5">
                    <div class="w-9 h-9 bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center flex-shrink-0 border border-white/50">
                        <i class="fa-solid fa-minus text-space-cadet text-base"></i>
                    </div>
                    <span class="text-[11px] font-bold text-space-cadet leading-tight text-left">Catat<br>Pengeluaran</span>
                </button>
            </div>

            <div class="mt-8">
                <div class="flex items-center gap-2 mb-4 px-1">
                    <span class="text-[14px] font-bold text-space-cadet flex items-center gap-2">
                        <div class="w-6 h-6 bg-yellow-gold/20 text-yellow-gold rounded-md flex items-center justify-center border border-yellow-gold/30"><i class="fa-solid fa-star text-[10px]"></i></div> Terlaris
                    </span>
                </div>
                <div class="glass-card overflow-hidden">
                    <table class="w-full text-left">
                        <tbody>
                            <?php if ($best_products && $best_products->num_rows > 0) : $i = 0; ?>
                                <?php while ($row = $best_products->fetch_assoc()) : $i++; ?>
                                    <tr class="<?= $i == 1 ? 'border-b border-white/60 bg-white/20' : '' ?>">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-5">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-[13px] font-black <?= $i == 1 ? 'bg-gradient-to-br from-yellow-gold to-[#f4d481] text-white shadow-[0_4px_10px_rgba(240,193,75,0.4)] border border-white' : 'bg-white/70 text-ucla-blue border border-white/50' ?>"><?= $i ?></div>
                                                <span class="text-[14px] font-bold text-space-cadet font-serif"><?= htmlspecialchars($row['name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <span class="text-[16px] font-black text-cyan-azure"><?= $row['sold_count'] ?></span>
                                            <span class="text-[11px] font-bold text-air-blue ml-1">pcs</span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="2" class="p-8 text-center">
                                        <i class="fa-solid fa-box-open text-2xl text-air-blue mb-2 opacity-50"></i>
                                        <p class="text-[12px] text-ucla-blue font-bold">Belum ada data penjualan. Terus semangat! ✨</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($is_malam && $business_id > 0): ?>
                <div class="mt-8">
                    <div class="flex items-center gap-2 mb-4 px-1">
                        <span class="text-[14px] font-bold text-space-cadet flex items-center gap-2">
                            <div class="w-6 h-6 bg-space-cadet/10 text-space-cadet rounded-md flex items-center justify-center border border-space-cadet/20"><i class="fa-solid fa-moon text-[12px]"></i></div> Cek Stok Malam
                        </span>
                    </div>
                    <div class="glass-card p-5">

                        <div class="mb-6 p-4 bg-gradient-to-r from-cyan-azure/10 to-pink-lavender/10 rounded-xl border border-cyan-azure/20 shadow-inner">
                            <p class="text-[11px] font-medium text-ucla-blue text-center leading-relaxed">
                                Cocokkan stok sistem vs fisik hari ini.<br>
                                <span class="text-cyan-azure font-bold">Menjaga keseimbangan bisnismu.</span>
                            </p>
                        </div>

                        <form id="formOpname">
                            <input type="hidden" name="business_id" value="<?= $business_id ?>">
                            <div class="opname-scroll bg-white/50 border border-white/80 shadow-inner mb-6">
                                <table class="w-full text-left">
                                    <thead class="bg-white/80 backdrop-blur-xl sticky top-0 z-10 border-b border-white">
                                        <tr>
                                            <th class="py-4 px-4 text-[10px] font-black text-space-cadet w-[45%] uppercase tracking-wider">Barang</th>
                                            <th class="py-4 px-2 text-[10px] font-black text-space-cadet text-center w-[18%] uppercase tracking-wider">Sistem</th>
                                            <th class="py-4 px-2 text-[10px] font-black text-space-cadet text-center w-[20%] uppercase tracking-wider">Fisik</th>
                                            <th class="py-4 px-2 text-[10px] font-black text-space-cadet text-center w-[17%] uppercase tracking-wider">Selisih</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($products_opname)): ?>
                                            <tr>
                                                <td colspan="4" class="p-6 text-center text-[12px] font-bold text-air-blue">Belum ada produk</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($products_opname as $p): ?>
                                                <tr class="border-t border-white/60 hover:bg-white/30 transition-colors">
                                                    <td class="py-4 px-4 text-[13px] font-bold text-space-cadet font-serif"><?= htmlspecialchars($p['name']) ?></td>
                                                    <td id="sys_<?= $p['id'] ?>" class="py-4 px-2 text-[14px] font-black text-cyan-azure text-center"><?= $p['stock'] ?></td>
                                                    <td class="py-3 px-2 text-center">
                                                        <input type="number" name="real_stock[<?= $p['id'] ?>]" id="fisik_<?= $p['id'] ?>"
                                                            oninput="hitungSelisih(<?= $p['id'] ?>, <?= $p['stock'] ?>)"
                                                            value="<?= $p['real_stock'] !== null ? $p['real_stock'] : '' ?>"
                                                            class="glass-input w-full max-w-[50px] mx-auto" placeholder="…">
                                                    </td>
                                                    <td id="selisih_<?= $p['id'] ?>" class="py-4 px-2 text-[14px] font-black text-center text-air-blue">-</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (!empty($products_opname)): ?>
                                <button type="button" onclick="selesaiCekStok()" class="w-full py-[18px] btn-dreamy text-[13px] font-black uppercase tracking-[0.1em] flex items-center justify-center gap-3">
                                    Selesai Pengecekan <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <div class="absolute bottom-5 left-5 right-5 navbar-glass px-4 py-3 flex justify-between items-center z-50">
            <a href="kasir.php" class="nav-item flex flex-col items-center py-2 px-4">
                <i class="nav-icon fa-solid fa-cash-register"></i>
                <span class="nav-label mt-1">Kasir</span>
            </a>
            <a href="main_page.php" class="nav-item active flex flex-col items-center py-2 px-4">
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

        <div id="modalTransaksi" class="absolute inset-0 hidden items-center justify-center p-5 z-[100] bg-space-cadet/40 backdrop-blur-md">
            <div class="relative bg-white/95 backdrop-blur-xl w-full max-w-[320px] p-8 border border-white rounded-[40px] shadow-[0_20px_60px_rgba(16,43,83,0.2)] overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-pink-lavender/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="w-12 h-1.5 bg-ucla-blue/20 rounded-full mx-auto mb-6"></div>
                <h2 id="modalTitle" class="text-[20px] font-serif font-black text-space-cadet text-center mb-6 tracking-tight">Tambah Transaksi</h2>

                <form action="proses_transaksi.php" method="POST" class="relative z-10">
                    <input type="hidden" name="business_id" value="<?= $business_id ?>">
                    <input type="hidden" id="modalType" name="type" value="Pemasukan">

                    <div class="mb-5">
                        <label class="text-[12px] font-bold text-ucla-blue block mb-2 ml-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" required class="modal-input" placeholder="0">
                    </div>
                    <div class="mb-5">
                        <label class="text-[12px] font-bold text-ucla-blue block mb-2 ml-1">Keterangan</label>
                        <textarea name="description" rows="2" class="modal-input resize-none" placeholder="Misal: Beli bahan baku..."></textarea>
                    </div>
                    <div class="mb-6">
                        <label class="text-[12px] font-bold text-ucla-blue block mb-2 ml-1">Tanggal</label>
                        <input type="date" name="created_at" value="<?= date('Y-m-d') ?>" class="modal-input">
                    </div>
                    <div class="flex gap-4 pt-2">
                        <button type="button" onclick="closeModal()" class="flex-1 py-[16px] bg-white border border-slate-200 shadow-sm text-ucla-blue rounded-[20px] text-[12px] font-bold uppercase tracking-widest transition hover:bg-slate-50 active:scale-95">Batal</button>
                        <button type="submit" class="flex-[1.5] py-[16px] btn-dreamy-alt rounded-[20px] text-[12px] font-bold uppercase tracking-widest">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL DETAIL (DREAMY THEME) -->
        <div id="modalDetail" class="absolute inset-0 hidden items-center justify-center p-5 z-[110] bg-space-cadet/40 backdrop-blur-sm transition-all duration-300">
            <div class="relative bg-[#f8f9fc]/95 backdrop-blur-2xl w-full max-w-[340px] rounded-[40px] shadow-[0_30px_80px_rgba(16,43,83,0.3)] overflow-hidden flex flex-col max-h-[85%] border-2 border-white">
                
                <!-- Background Glow di dalam Modal -->
                <div class="absolute top-0 right-0 w-40 h-40 bg-pink-lavender/20 rounded-full blur-3xl pointer-events-none -mt-10 -mr-10"></div>
                <div class="absolute bottom-20 left-0 w-32 h-32 bg-cyan-azure/10 rounded-full blur-3xl pointer-events-none -ml-10"></div>

                <!-- Header Modal -->
                <div class="px-6 pt-8 pb-2 flex-shrink-0 relative z-10">
                    <button onclick="closeDetailModal()" class="absolute top-6 right-6 w-9 h-9 bg-white/60 backdrop-blur-md rounded-full flex items-center justify-center text-ucla-blue hover:bg-white hover:text-blush-pink transition-colors shadow-sm border border-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                    
                    <div id="detailIcon" class="w-16 h-16 rounded-[22px] mx-auto mb-4 flex items-center justify-center shadow-[0_10px_25px_rgba(0,0,0,0.1)] border-[3px] border-white">
                        <i class="fa-solid fa-list text-white text-2xl"></i>
                    </div>
                    <h2 id="detailTitle" class="text-[22px] font-serif font-black text-space-cadet text-center tracking-tight">Detail</h2>
                    <p class="text-center text-[10px] font-black text-air-blue mt-1 uppercase tracking-[0.15em]">Rincian Transaksi</p>
                </div>

                <!-- Konten List -->
                <div class="flex-1 overflow-y-auto hide-scrollbar px-5 py-4 relative z-10">
                    <div id="detailListContainer" class="space-y-3">
                        <!-- Konten di-generate via JavaScript -->
                    </div>
                </div>

                <!-- Footer Total -->
                <div class="px-7 py-5 bg-white/80 backdrop-blur-xl border-t border-white/60 flex-shrink-0 flex justify-between items-center shadow-[0_-10px_30px_rgba(16,43,83,0.03)] relative z-20">
                    <span class="text-[11px] font-black text-ucla-blue uppercase tracking-[0.2em]">Total</span>
                    <span id="detailTotal" class="text-[22px] font-black text-space-cadet">Rp 0</span>
                </div>
            </div>
        </div>

    </div>
    <script>
        // Opname Logic
        function hitungSelisih(id, sysStock) {
            let fisikInput = document.getElementById('fisik_' + id).value;
            let selisihCell = document.getElementById('selisih_' + id);

            if (fisikInput === '') {
                selisihCell.innerHTML = '-';
                selisihCell.className = "py-4 px-2 text-[14px] font-black text-center text-air-blue";
                return;
            }

            let fisik = parseInt(fisikInput);
            let selisih = fisik - sysStock;

            if (selisih === 0) {
                selisihCell.innerHTML = '<span class="text-[11px] bg-mint-green/20 text-mint-green border border-mint-green/30 px-3 py-1.5 rounded-lg">PAS</span>';
                selisihCell.className = "py-4 px-2 text-center";
            } else if (selisih < 0) {
                selisihCell.innerHTML = selisih;
                selisihCell.className = "py-4 px-2 text-[15px] font-black text-center text-blush-pink";
            } else {
                selisihCell.innerHTML = '+' + selisih;
                selisihCell.className = "py-4 px-2 text-[15px] font-black text-center text-cyan-azure";
            }
        }

        window.onload = function() {
            if (opnameData.length > 0) {
                opnameData.forEach(p => {
                    hitungSelisih(p.id, p.stock);
                });
            }
        };

        function selesaiCekStok() {
            let form = document.getElementById('formOpname');
            let formData = new FormData(form);
            fetch('proses_opname.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.text())
                .then(() => {
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

                                    <h2 class="text-2xl font-serif font-black text-space-cadet mb-2">Balance Achieved!</h2>
                                    <p class="text-[13px] font-medium text-ucla-blue mb-4">Pengecekan stok harian selesai dicatat.</p>
                                </div>
                            </div>
                        `,
                        buttonsStyling: false,
                        confirmButtonText: 'Great',
                        width: '320px',
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdrop: 'rgba(16, 43, 83, 0.5)',
                        customClass: {
                            popup: 'rounded-[40px] border border-white shadow-2xl',
                            htmlContainer: '!overflow-hidden !m-0 !p-5',
                            confirmButton: 'swal-gradient-btn mt-2 mb-2'
                        }
                    }).then(() => {
                        window.location.href = 'main_page.php';
                    });
                })
                .catch(() => {
                    Swal.fire({
                        target: '#phone',
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal menghubungi server.',
                        confirmButtonColor: '#102B53',
                        width: '300px',
                        customClass: {
                            popup: 'rounded-[30px]'
                        }
                    });
                });
        }

        // Chart.js 
        const ctx = document.getElementById('financeChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabelsData,
                datasets: [{
                        label: 'Pemasukan',
                        data: chartPemasukanData,
                        backgroundColor: '#4E7AB1',
                        borderRadius: 6,
                        barPercentage: 0.65,
                        categoryPercentage: 0.75
                    },
                    {
                        label: 'Pengeluaran',
                        data: chartPengeluaranData,
                        backgroundColor: '#CEB5D4',
                        borderRadius: 6,
                        barPercentage: 0.65,
                        categoryPercentage: 0.75
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
                        labels: {
                            font: {
                                size: 10,
                                family: 'Nunito',
                                weight: '800'
                            },
                            usePointStyle: true,
                            boxWidth: 6,
                            color: '#50698D'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#102B53',
                        bodyColor: '#50698D',
                        borderColor: '#eef2f8',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 4,
                        usePointStyle: true,
                        titleFont: {
                            family: 'Quicksand',
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Nunito',
                            size: 12,
                            weight: 'bold'
                        },
                        callbacks: {
                            label: function(ctx) {
                                return (ctx.dataset.label || '') + ': Rp ' + ctx.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        border: {
                            display: false
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.4)',
                            tickLength: 0
                        },
                        ticks: {
                            padding: 8,
                            font: {
                                size: 9,
                                family: 'Nunito',
                                weight: 'bold'
                            },
                            color: '#7D9FC0',
                            callback: v => v >= 1000000 ? (v / 1000000) + 'M' : v >= 1000 ? (v / 1000) + 'K' : v
                        }
                    },
                    x: {
                        border: {
                            display: false
                        },
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10,
                                family: 'Nunito',
                                weight: '800'
                            },
                            color: '#50698D'
                        }
                    }
                }
            }
        });

        // Ambil data dari PHP
        const listPemasukan = <?= $js_list_pemasukan ?>;
        const listPengeluaran = <?= $js_list_pengeluaran ?>;
        const listKeuntungan = <?= $js_list_keuntungan ?>;

        function openDetailModal(type) {
            const modal = document.getElementById('modalDetail');
            const title = document.getElementById('detailTitle');
            const iconWrap = document.getElementById('detailIcon');
            const iconTag = iconWrap.querySelector('i');
            const container = document.getElementById('detailListContainer');
            const totalDisplay = document.getElementById('detailTotal');

            let data = [];
            let total = 0;
            let iconClass = '';
            let bgClass = '';

            // Setup Tema Berdasarkan Tipe
            if (type === 'pemasukan') {
                title.innerText = 'Pemasukan';
                data = listPemasukan;
                iconClass = 'fa-arrow-down';
                bgClass = 'bg-gradient-to-br from-cyan-azure to-[#81A4CD]';
            } else if (type === 'pengeluaran') {
                title.innerText = 'Pengeluaran';
                data = listPengeluaran;
                iconClass = 'fa-arrow-up';
                bgClass = 'bg-gradient-to-br from-pink-lavender to-[#e1ccdb]';
            } else if (type === 'keuntungan') {
                title.innerText = 'Keuntungan';
                data = listKeuntungan;
                iconClass = 'fa-coins';
                bgClass = 'bg-gradient-to-br from-mint-green to-[#88d5c2]';
            }

            iconWrap.className = `w-16 h-16 rounded-[22px] mx-auto mb-4 flex items-center justify-center shadow-lg border-[3px] border-white ${bgClass}`;
            iconTag.className = `fa-solid ${iconClass} text-white text-2xl`;

            container.innerHTML = '';
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="py-12 flex flex-col items-center justify-center opacity-60">
                        <i class="fa-solid fa-receipt text-4xl text-air-blue mb-3"></i>
                        <p class="text-center text-[12px] font-bold text-air-blue">Belum ada rincian tercatat.</p>
                    </div>`;
            } else {
                data.forEach(item => {
                    let amountColor = '';
                    let operator = '';
                    
                    // PERBAIKAN: Ubah string menjadi angka (Float) agar bisa dijumlahkan!
                    let amountVal = parseFloat(item.amount) || 0; 
                    
                    if (type === 'keuntungan') {
                        amountColor = item.is_positive ? 'text-mint-green' : 'text-blush-pink';
                        operator = item.is_positive ? '+' : ''; // Minus sudah bawaan angkanya jika negatif
                        total += item.is_positive ? amountVal : -amountVal;
                    } else {
                        amountColor = type === 'pemasukan' ? 'text-cyan-azure' : 'text-pink-lavender';
                        total += amountVal;
                    }

                    // Tampilan List ala Glassmorphism
                    container.innerHTML += `
                        <div class="bg-white/80 backdrop-blur-md border border-white p-4 rounded-[24px] flex justify-between items-center shadow-[0_8px_25px_rgba(80,105,141,0.06)] transition-all hover:bg-white hover:scale-[1.02]">
                            <div class="flex-1 pr-3">
                                <h4 class="text-[13px] font-bold font-serif text-space-cadet leading-snug line-clamp-2">${item.desc}</h4>
                                <p class="text-[10px] font-bold text-air-blue mt-1.5 flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-[9px] opacity-70"></i> ${item.date}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0 bg-slate-50/50 px-3 py-2 rounded-2xl border border-white/60">
                                <span class="text-[14px] font-black ${amountColor}">${operator}Rp ${amountVal.toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                    `;
                });
            }

            // Menampilkan Total yang sudah benar
            totalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
            totalDisplay.className = `text-[22px] font-black ${total >= 0 ? 'text-space-cadet' : 'text-blush-pink'}`;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDetailModal() {
            const modal = document.getElementById('modalDetail');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</body>

</html>