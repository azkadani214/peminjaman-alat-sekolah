<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] != "admin utama") {
    die("Akses ditolak.");
}

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');

$query = "SELECT t.*, u.nama, u.nis, u.kelas 
          FROM transaksi t
          JOIN users u ON t.id_user = u.id_user
          WHERE DATE(t.waktu_pinjam) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY t.waktu_pinjam DESC";
$result = mysqli_query($connect, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman Alat - PopFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; padding: 0; margin: 0; color: #1F2937; background: #F4F4F5; }
        .report-container { padding: 50px; background: white; width: 210mm; margin: 0 auto; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 5px solid #2A4736; padding-bottom: 25px; margin-bottom: 40px; }
        .title { font-size: 28px; font-weight: 800; color: #2A4736; text-transform: uppercase; letter-spacing: -1.5px; line-height: 1; }
        .subtitle { font-size: 10px; font-weight: 700; color: #F5C460; text-transform: uppercase; letter-spacing: 3px; margin-top: 8px; }
        .meta { text-align: right; }
        .meta-text { font-size: 9px; font-weight: 700; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1px; }
        .meta-val { font-size: 13px; font-weight: 800; color: #2A4736; margin-top: 2px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #F4F4F5; padding: 15px 12px; text-align: left; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #2A4736; border-bottom: 2px solid #E4E4E7; }
        td { padding: 15px 12px; font-size: 11px; border-bottom: 1px solid #F3F4F6; color: #4B5563; vertical-align: middle; }
        
        .badge { font-size: 9px; font-weight: 800; text-transform: uppercase; padding: 3px 8px; border-radius: 2px; display: inline-block; background: #E5E7EB; color: #4B5563; }
        .status-dikembalikan { background: #f0fdf4; color: #166534; }
        .status-dipinjam { background: #2A4736; color: white; }
        .status-menunggu { background: #F5C460; color: #2A4736; }
        
        .bold-dark { font-weight: 800; color: #111827; text-transform: uppercase; }
        .footer { margin-top: 60px; text-align: center; border-top: 1px solid #F3F4F6; padding-top: 20px; }
        .footer-text { font-size: 9px; color: #9CA3AF; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 20px;}
        
        #download-btn { position: fixed; top: 30px; right: 30px; background: #2A4736; color: white; border: none; padding: 14px 28px; border-radius: 4px; font-weight: 800; cursor: pointer; text-transform: uppercase; font-size: 13px; z-index: 100; transition: all 0.2s; letter-spacing: 1px; }
        #download-btn:hover { background: #3E614C; transform: translateY(-2px); }
    </style>
</head>
<body>

    <button id="download-btn">Unduh PDF Resmi</button>

    <div class="report-container" id="report">
        <div class="header">
            <div>
                <div class="title">PopFit Report</div>
                <div class="subtitle">Official Equipment Circulation Log</div>
            </div>
            <div class="meta">
                <div class="meta-text">Report Duration</div>
                <div class="meta-val"><?= date('d.m.y', strtotime($tgl_mulai)) ?> — <?= date('d.m.y', strtotime($tgl_selesai)) ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Pinjam</th>
                    <th>Siswa</th>
                    <th>Status</th>
                    <th style="text-align: center;">Delay Detail</th>
                    <th style="text-align: right;">Denda</th>
                    <th style="text-align: right;">Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $st = $row['status'];
                ?>
                <tr>
                    <td>
                        <span class="bold-dark"><?= date('d M Y', strtotime($row['waktu_pinjam'])) ?></span><br>
                        <span style="font-size: 10px; opacity: 0.5; font-weight: 700;"><?= date('H:i', strtotime($row['waktu_pinjam'])) ?></span>
                    </td>
                    <td>
                        <span class="bold-dark"><?= htmlspecialchars($row['nama']) ?></span><br>
                        <span style="font-size: 10px; opacity: 0.6; font-weight: 700;"><?= $row['nis'] ?> • <?= strtoupper($row['kelas']) ?></span>
                    </td>
                    <td>
                        <span class="badge status-<?= $st ?>"><?= $st ?></span>
                    </td>
                    <td style="text-align: center;">
                        <?php if($row['keterlambatan'] == 'ya' && $row['waktu_kembali']): 
                            $det = cekDetailKeterlambatan($row['batas_kembali'], $row['waktu_kembali']);    
                        ?>
                            <span style="font-size: 8px; font-weight: 800; color: #DC2626; text-transform: uppercase;">TELAT <?= $det['teks'] ?></span>
                        <?php else: ?>
                            <span style="font-size: 8px; font-weight: 500; color: #D1D5DB;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; font-weight: 800; color: #111827;">
                        Rp <?= number_format($row['denda'], 0, ',', '.') ?>
                    </td>
                    <td style="text-align: right;">
                        <span class="bold-dark" style="font-size: 10px; <?= ($row['pembayaran'] == 'lunas') ? 'color: #166534;' : 'color: #DC2626;' ?>">
                            <?= $row['pembayaran'] ?: 'PENDING' ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="footer">
            <p class="footer-text">Generated via PopFit System Control Panel • <?= date('d.m.Y H:i') ?></p>
        </div>
    </div>

    <script>
        const element = document.getElementById('report');
        const btn = document.getElementById('download-btn');

        btn.addEventListener('click', () => {
            btn.style.display = 'none';
            const opt = {
                margin: 0,
                filename: 'POPFIT_REPORT_<?= str_replace('-','',$tgl_mulai) ?>.pdf',
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 3, useCORS: true, letterRendering: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btn.style.display = 'block';
            });
        });
    </script>
</body>
</html>
