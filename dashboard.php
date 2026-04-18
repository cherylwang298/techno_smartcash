<?php
include 'db.php';

// ambil nama usaha (sementara ambil 1 aja dulu)
$usaha = "";

$result = $conn->query("SELECT business_name FROM businesses LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    $usaha = $row['business_name'];
}   
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: 'rgb(16, 43, 83)'
            }
        }
    }
}
</script>
</head>

<body class="bg-slate-200 flex justify-center items-center min-h-screen">

<div class="w-[360px] h-[780px] bg-white rounded-[40px] shadow-xl overflow-hidden relative">

    <!-- 🔥 HEADER -->
    <div class="bg-primary text-white p-5 text-center">
        <h1 class="font-bold text-lg">
            <?php echo $usaha ? $usaha : "Nama Usaha"; ?>
        </h1>
    </div>

    <!-- 🔥 SCROLLABLE CONTENT -->
    <div class="absolute top-[80px] bottom-[70px] left-0 right-0 overflow-y-auto px-4 py-3 space-y-4">

        <!-- FILTER -->
        <div class="flex justify-center gap-2 text-xs">
            <button class="bg-primary text-white px-3 py-1 rounded-full">Hari</button>
            <button class="bg-gray-200 px-3 py-1 rounded-full">Minggu</button>
            <button class="bg-gray-200 px-3 py-1 rounded-full">Bulan</button>
        </div>

        <!-- DONUT -->
        <div class="bg-white rounded-2xl shadow p-4">
            <canvas id="donutChart"></canvas>
        </div>

        <!-- LEGEND -->
        <div class="text-xs flex justify-center gap-6">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-primary rounded"></div>
                <p>Pemasukan</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-400 rounded"></div>
                <p>Pengeluaran</p>
            </div>
        </div>

        <!-- CARDS -->
        <div class="grid grid-cols-3 gap-3 text-center">

            <div class="bg-primary/10 p-3 rounded-xl shadow-sm">
                <p class="text-[10px]">Pemasukan</p>
                <p class="font-bold text-primary text-sm">Rp 5jt</p>
            </div>

            <div class="bg-red-100 p-3 rounded-xl shadow-sm">
                <p class="text-[10px]">Pengeluaran</p>
                <p class="font-bold text-red-500 text-sm">Rp 2jt</p>
            </div>

            <div class="bg-green-100 p-3 rounded-xl shadow-sm">
                <p class="text-[10px]">Keuntungan</p>
                <p class="font-bold text-green-600 text-sm">Rp 3jt</p>
            </div>

        </div>

        <!-- DOWNLOAD -->
        <div class="bg-primary/10 p-4 rounded-xl text-center shadow-sm">
            <button class="bg-primary text-white px-4 py-2 rounded-lg text-xs font-bold w-full">
                ⬇️ Download Laporan
            </button>
        </div>

        <!-- MENU -->
        <div class="grid grid-cols-4 gap-3 pt-2 text-center text-xs">

            <div>
                <div class="bg-primary/10 p-3 rounded-xl shadow-sm">💰</div>
                <p>Kas</p>
            </div>

            <div>
                <div class="bg-primary/10 p-3 rounded-xl shadow-sm">📦</div>
                <p>Stok</p>
            </div>

            <div>
                <div class="bg-primary/10 p-3 rounded-xl shadow-sm">📊</div>
                <p>Laporan</p>
            </div>

            <div>
                <div class="bg-primary/10 p-3 rounded-xl shadow-sm">⚙️</div>
                <p>Setting</p>
            </div>

        </div>

    </div>

    <!-- 🔥 FIXED NAVBAR -->
    <div class="absolute bottom-0 left-0 right-0 bg-white border-t flex justify-around py-3 text-xs shadow-inner">

        <div class="text-center text-gray-400">
            👤
            <p>Profil</p>
        </div>

        <div class="text-center text-gray-400">
            📦
            <p>Stok</p>
        </div>

        <div class="text-center text-primary font-bold">
            🏠
            <p>Home</p>
        </div>

        <div class="text-center text-gray-400">
            🧾
            <p>Kasir</p>
        </div>

    </div>

</div>

<!-- 🔥 DONUT CHART -->
<script>
const ctx = document.getElementById('donutChart');

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Pemasukan', 'Pengeluaran'],
        datasets: [{
            data: [70, 30],
            backgroundColor: [
                'rgb(16, 43, 83)',
                '#f87171'
            ],
            borderWidth: 0
        }]
    },
    options: {
        plugins: {
            legend: { display: false }
        },
        cutout: '70%'
    }
});
</script>

</body>
</html>