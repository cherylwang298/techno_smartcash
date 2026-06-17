<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// ─── Data Bisnis ────────────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT id, business_name FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();

$business_id   = $business['id']            ?? 0;
$business_name = $business['business_name'] ?? "Bisnis Saya";

// ─── Parameter Filter ────────────────────────────────────────────────────────
$filter = $_GET['filter'] ?? 'bulan';
$year   = (int)($_GET['year']  ?? date('Y'));
$month  = (int)($_GET['month'] ?? date('n'));

// Tentukan rentang tanggal berdasarkan filter agar klop dengan dashboard
switch ($filter) {
    case 'hari':
        $date_start = date('Y-m-d');
        $date_end   = $date_start;
        $period_label = "Harian – " . date('d/m/Y');
        break;
    case 'minggu':
        $date_start = date('Y-m-d', strtotime('monday this week'));
        $date_end   = date('Y-m-d', strtotime('sunday this week'));
        $period_label = "Mingguan – " . date('d/m/Y', strtotime($date_start)) . " s.d. " . date('d/m/Y', strtotime($date_end));
        break;
    case 'tahun':
        $date_start = "{$year}-01-01";
        $date_end   = "{$year}-12-31";
        $period_label = "Tahunan – {$year}";
        break;
    default: // bulan
        $date_start = date("Y-m-01");
        $date_end   = date("Y-m-t");
        $period_label = "Bulanan – " . date('F Y');
        break;
}

// ─── Ambil Semua Transaksi & Hitung Barang Terjual ───────────────────────────
$rows = [];
$product_summary = []; 
$total_pemasukan  = 0;
$total_hpp        = 0;   
$total_pengeluaran_ops = 0; 

if ($business_id > 0) {
    $sql = "SELECT
                t.id, t.type, t.nominal, t.qty, t.description, t.created_at,
                p.name AS product_name, p.sell_price, p.buy_price
            FROM transactions t
            LEFT JOIN products p ON t.product_id = p.id
            WHERE t.business_id = ?
              AND DATE(t.created_at) BETWEEN ? AND ?
            ORDER BY t.created_at ASC";

    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("iss", $business_id, $date_start, $date_end);
    $stmt2->execute();
    $result = $stmt2->get_result();

    while ($row = $result->fetch_assoc()) {
        $pemasukan_baris = 0;
        $hpp_baris       = 0;
        $ops_baris       = 0;

        if ($row['type'] === 'Pemasukan') {
            if ($row['product_name'] !== null) {
                // Hitungan untuk produk
                $pemasukan_baris = $row['sell_price'] * $row['qty'];
                $hpp_baris       = $row['buy_price']  * $row['qty'];
                
                // Track rekap barang terjual
                $p_name = $row['product_name'];
                if(!isset($product_summary[$p_name])) {
                    $product_summary[$p_name] = ['qty' => 0, 'revenue' => 0];
                }
                $product_summary[$p_name]['qty'] += $row['qty'];
                $product_summary[$p_name]['revenue'] += $pemasukan_baris;
                
            } else {
                // Hitungan pemasukan manual
                $pemasukan_baris = $row['nominal'];
            }
        } elseif ($row['type'] === 'Pengeluaran') {
            // Hitungan pengeluaran operasional
            $ops_baris = $row['nominal'];
        }

        $total_pemasukan       += $pemasukan_baris;
        $total_hpp             += $hpp_baris;
        $total_pengeluaran_ops += $ops_baris;

        $laba_kotor_baris = $pemasukan_baris - $hpp_baris;
        $laba_baris       = $laba_kotor_baris - $ops_baris;

        $rows[] = [
            'no'           => count($rows) + 1,
            'tanggal'      => date('d/m/Y H:i', strtotime($row['created_at'])),
            'jenis'        => $row['type'],
            'produk'       => $row['product_name'] ?? 'Manual / Ops',
            'qty'          => ($row['product_name'] !== null) ? $row['qty'] : '-',
            'harga_jual'   => ($row['product_name'] !== null) ? $row['sell_price']  : $row['nominal'],
            'hpp_satuan'   => ($row['product_name'] !== null) ? $row['buy_price']   : 0,
            'pemasukan'    => $pemasukan_baris,
            'hpp_total'    => $hpp_baris,
            'ops'          => $ops_baris,
            'laba_kotor'   => $laba_kotor_baris,
            'laba_bersih'  => $laba_baris,
            'keterangan'   => $row['description'] ?: '-',
        ];
    }
}

// ─── Kalkulasi Laporan Laba Rugi ─────────────────────────────────────────────
$laba_kotor  = $total_pemasukan - $total_hpp;
$laba_bersih = $laba_kotor - $total_pengeluaran_ops;

// Estimasi PPh Final UMKM (PP 23/2018)
$pph_final_rate = 0.005; // 0,5%
$pph_final      = max(0, $total_pemasukan * $pph_final_rate);
$laba_after_tax = $laba_bersih - $pph_final;

// ─── Export ke Excel (Format .xls menggunakan HTML Table) ────────────────────
// Nama file otomatis disesuaikan dengan periode
$filename = "Laporan_Keuangan_" . preg_replace('/[^A-Za-z0-9\-]/', '_', $business_name) . "_" . strtoupper($filter) . "_" . date('Ymd') . ".xls";

// Header HTTP agar file di-download sebagai Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; margin-bottom: 20px; }
        .table-border th, .table-border td { border: 1px solid #000000; padding: 6px; }
        .header-section th { background-color: #102B53; color: white; font-weight: bold; text-align: left; font-size: 14px;}
        .sub-header td { background-color: #D9E1F2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>

<!-- INFORMASI UMUM -->
<table>
    <tr><th colspan="5" style="font-size: 20px; text-align: left; color: #102B53;">LAPORAN KEUANGAN UMKM</th></tr>
    <tr><td colspan="2">Nama Usaha</td><td colspan="3" class="bold">: <?= htmlspecialchars($business_name) ?></td></tr>
    <tr><td colspan="2">Periode</td><td colspan="3" class="bold">: <?= $period_label ?></td></tr>
    <tr><td colspan="2">Tanggal Cetak</td><td colspan="3">: <?= date('d/m/Y H:i') ?></td></tr>
</table>

<!-- LAPORAN LABA RUGI -->
<table class="table-border" style="width: 500px;">
    <tr class="header-section">
        <th colspan="2">LAPORAN LABA RUGI (INCOME STATEMENT)</th>
    </tr>
    <tr><td>Pendapatan / Omzet Total</td><td class="text-right"><?= number_format($total_pemasukan, 0, ',', '.') ?></td></tr>
    <tr><td>Harga Pokok Penjualan (HPP)</td><td class="text-right">(<?= number_format($total_hpp, 0, ',', '.') ?>)</td></tr>
    <tr class="sub-header"><td style="text-align: right;">LABA KOTOR</td><td class="text-right"><?= number_format($laba_kotor, 0, ',', '.') ?></td></tr>
    <tr><td>Biaya Operasional (Ops)</td><td class="text-right">(<?= number_format($total_pengeluaran_ops, 0, ',', '.') ?>)</td></tr>
    <tr class="sub-header"><td style="text-align: right;">LABA BERSIH SEBELUM PAJAK</td><td class="text-right"><?= number_format($laba_bersih, 0, ',', '.') ?></td></tr>
    <tr><td>Estimasi PPh Final 0,5% (PP 23/2018)</td><td class="text-right">(<?= number_format($pph_final, 0, ',', '.') ?>)</td></tr>
    <tr>
        <td class="bold" style="background-color: #102B53; color: white; text-align: right;">LABA BERSIH SETELAH PAJAK</td>
        <td class="text-right bold" style="background-color: #102B53; color: white;"><?= number_format($laba_after_tax, 0, ',', '.') ?></td>
    </tr>
</table>

<!-- NERACA SEDERHANA & PAJAK -->
<table class="table-border" style="width: 500px;">
    <tr class="header-section"><th colspan="2">NERACA & CATATAN PAJAK</th></tr>
    <tr class="sub-header"><td colspan="2">Aktiva (Aset)</td></tr>
    <tr><td>Penambahan Kas Periode Ini</td><td class="text-right"><?= number_format($laba_after_tax, 0, ',', '.') ?></td></tr>
    <tr class="sub-header"><td colspan="2">Kewajiban (Liabilitas) & Ekuitas</td></tr>
    <tr><td>Estimasi Pajak Terutang</td><td class="text-right"><?= number_format($pph_final, 0, ',', '.') ?></td></tr>
    <tr><td>Penambahan Modal / Laba Ditahan</td><td class="text-right"><?= number_format($laba_after_tax, 0, ',', '.') ?></td></tr>
    <tr class="sub-header"><td colspan="2">Instruksi Penyetoran Pajak UMKM</td></tr>
    <tr><td colspan="2" style="font-style: italic;">Setor paling lambat tgl 15 bulan berikutnya dengan <b>Kode Akun Pajak 411128</b> dan <b>Kode Setoran 420</b>.</td></tr>
</table>

<!-- REKAP PENJUALAN BARANG -->
<table class="table-border" style="width: 500px;">
    <tr class="header-section">
        <th colspan="3">REKAP BARANG TERJUAL</th>
    </tr>
    <tr class="sub-header">
        <td class="text-center">Nama Barang</td>
        <td class="text-center">Total Qty</td>
        <td class="text-center">Total Pendapatan (Rp)</td>
    </tr>
    <?php if(empty($product_summary)): ?>
        <tr><td colspan="3" class="text-center" style="font-style: italic;">Belum ada penjualan produk.</td></tr>
    <?php else: ?>
        <?php foreach($product_summary as $name => $data): ?>
        <tr>
            <td><?= htmlspecialchars($name) ?></td>
            <td class="text-center"><?= $data['qty'] ?></td>
            <td class="text-right"><?= number_format($data['revenue'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<!-- JURNAL TRANSAKSI DETAIL -->
<table class="table-border">
    <tr class="header-section">
        <th colspan="13">DETAIL JURNAL TRANSAKSI</th>
    </tr>
    <tr class="sub-header text-center">
        <td>No</td>
        <td>Tanggal</td>
        <td>Jenis</td>
        <td>Produk / Akun</td>
        <td>Qty</td>
        <td>Harga / Nominal</td>
        <td>HPP/Satuan</td>
        <td>Pemasukan</td>
        <td>Total HPP</td>
        <td>Biaya Ops</td>
        <td>Laba Kotor</td>
        <td>Laba Bersih</td>
        <td>Keterangan</td>
    </tr>
    <?php if(empty($rows)): ?>
        <tr><td colspan="13" class="text-center" style="font-style: italic;">Tidak ada transaksi pada periode ini.</td></tr>
    <?php else: ?>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td class="text-center"><?= $r['no'] ?></td>
            <td><?= $r['tanggal'] ?></td>
            <td class="text-center"><?= $r['jenis'] ?></td>
            <td><?= htmlspecialchars($r['produk']) ?></td>
            <td class="text-center"><?= $r['qty'] ?></td>
            <td class="text-right"><?= is_numeric($r['harga_jual']) ? number_format($r['harga_jual'], 0, ',', '.') : $r['harga_jual'] ?></td>
            <td class="text-right"><?= is_numeric($r['hpp_satuan']) ? number_format($r['hpp_satuan'], 0, ',', '.') : $r['hpp_satuan'] ?></td>
            <td class="text-right"><?= number_format($r['pemasukan'], 0, ',', '.') ?></td>
            <td class="text-right"><?= number_format($r['hpp_total'], 0, ',', '.') ?></td>
            <td class="text-right"><?= number_format($r['ops'], 0, ',', '.') ?></td>
            <td class="text-right"><?= number_format($r['laba_kotor'], 0, ',', '.') ?></td>
            <td class="text-right"><?= number_format($r['laba_bersih'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($r['keterangan']) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

</body>
</html>
<?php exit; ?>