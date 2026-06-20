<?php
include 'koneksi.php'; 

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$nama_tabel = "dokumen_sofi_2440511004";

// FITUR PENCARIAN & READ DATA
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query_select = "SELECT * FROM $nama_tabel WHERE nama_dokumen LIKE '%$search%' OR deskripsi LIKE '%$search%' ORDER BY id DESC";
} else {
    $query_select = "SELECT * FROM $nama_tabel ORDER BY id DESC";
}
$data_dokumen = mysqli_query($conn, $query_select);

//  FITUR EKSPORT CSV 

if (isset($_GET['aksi']) && $_GET['aksi'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Berkas_Digital_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array('NO', 'NAMA PENGAJU', 'KETERANGAN DOKUMEN', 'STATUS OTENTIKASI'));
    
    $no = 1;
    while ($row_csv = mysqli_fetch_assoc($data_dokumen)) {
        fputcsv($output, array(
            $no++, 
            $row_csv['nama_dokumen'], 
            $row_csv['deskripsi'], 
            'Terverifikasi Tanda Tangan Digital'
        ));
    }
    fclose($output);
    exit;
}

//  FITUR CETAK SEMUA DATA KE PDF

if (isset($_GET['aksi']) && $_GET['aksi'] == 'cetak_semua') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Berkas Digital - PDF</title>
        <style>
            body { font-family: 'Arial', sans-serif; padding: 20px; color: #333; }
            .kop-surat { text-align: center; border-bottom: 3px double #0f764e; padding-bottom: 10px; margin-bottom: 20px; }
            .kop-surat h2 { margin: 0; color: #0f764e; }
            .kop-surat p { margin: 5px 0 0 0; font-size: 11px; color: #666; }
            .judul { text-align: center; font-weight: bold; font-size: 16px; margin: 20px 0; text-transform: uppercase; }
            .table-pdf { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .table-pdf th, .table-pdf td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
            .table-pdf th { background-color: #f2f2f2; font-weight: bold; }
            @media print { .no-print { display: none; } body { padding: 0; } }
        </style>
    </head>
    <body>
        <div class="no-print" style="background: #e6f7f0; padding: 10px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #0f764e; text-align: center;">
            <button onclick="window.print()" style="background: #0f764e; color: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">💾 Save as PDF</button>
            <a href="index.php" style="margin-left: 15px; color: #666; text-decoration: none; font-size: 13px;">← Kembali ke Dashboard</a>
        </div>

        <div class="kop-surat">
            <h2>BERKAS DIGITAL MANAGEMENT</h2>
            <p>Laporan Data Berkas Masuk dan Otentikasi Tanda Tangan Digital</p>
        </div>

        <div class="judul">LAPORAN REKAPITULASI BERKAS MASUK</div>

        <table class="table-pdf">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NAMA PENGAJU</th>
                    <th>KETERANGAN DOKUMEN</th>
                    <th>BERKAS</th>
                    <th>TANDA TANGAN</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row_pdf = mysqli_fetch_assoc($data_dokumen)): 
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td style="font-weight: bold;"><?= htmlspecialchars($row_pdf['nama_dokumen']); ?></td>
                    <td><?= htmlspecialchars($row_pdf['deskripsi']); ?></td>
                    <td><?= htmlspecialchars($row_pdf['files']); ?></td>
                    <td style="text-align: center;">
                        <img src="<?= $row_pdf['ttd_digital']; ?>" width="60" style="border: 1px solid #ccc; padding: 2px;">
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <script>
            window.onload = function() { window.print(); }
        </script>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM $nama_tabel WHERE id=$id");
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BerkasDigital - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f9f6; font-family: 'Segoe UI', sans-serif; }
        .navbar-custom { background-color: #ffffff; border-bottom: 1px solid #e2e8f0; }
        .brand-title { color: #0f764e; font-weight: 700; font-size: 1.4rem; }
        .server-status-card { background: linear-gradient(135deg, #0f764e, #149c68); color: white; border-radius: 15px; border: none; }
        .main-card { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .btn-custom-primary { background-color: #0f764e; color: white; border-radius: 8px; border: none; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-custom-outline { background-color: #ffffff; color: #0f764e; border: 1px solid #0f764e; border-radius: 8px; font-weight: 500; text-decoration: none; display: inline-block; }
        
        .status-dot {
            height: 10px;
            width: 10px;
            background-color: #fff;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 1.5s infinite;
            margin-right: 8px;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <span class="brand-title">📁 BerkasDigital</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-secondary small">Halo, <strong class="text-success"><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?></strong></span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger px-3 rounded-pill">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card server-status-card p-4 mb-4 shadow-sm">
            <h4 class="fw-bold mb-1"><span class="status-dot"></span> Status Server Berjalan</h4>
            <p class="mb-0 text-white-50 small">Sistem manajemen berkas digital (CRUD Terpisah) aktif.</p>
        </div>

        <div class="card main-card bg-white p-4">
            
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Data Berkas Masuk</h5>
                    <p class="text-muted small mb-0">Daftar berkas pengaju yang dilengkapi tanda tangan digital.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="index.php?aksi=csv" class="btn btn-custom-outline btn-sm px-3 py-2">
                        <i class="fa-solid fa-file-csv me-1"></i> Eksport CSV
                    </a>
                    <a href="index.php?aksi=cetak_semua" class="btn btn-custom-outline btn-sm px-3 py-2" target="_blank" style="background-color: #fff; color: #dc3545; border-color: #dc3545;">
                        <i class="fa-solid fa-file-pdf me-1"></i> Eksport PDF
                    </a>
                    <a href="tambah.php" class="btn btn-custom-primary btn-sm px-3 py-2"><i class="fa-solid fa-plus me-1"></i> Tambah Pengajuan</a>
                </div>
            </div>

            <div class="mb-4">
                <form action="" method="GET" class="position-relative">
                    <span class="position-absolute top-50 translate-middle-y ms-3 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control ps-5 rounded-pill btn-light" placeholder="Cari nama pengaju..." value="<?= htmlspecialchars($search); ?>">
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-secondary small fw-bold">
                        <tr>
                            <th>NAMA PENGAJU</th>
                            <th>KETERANGAN DOKUMEN</th>
                            <th>BERKAS</th>
                            <th>TANDA TANGAN</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php 
                        if ($data_dokumen && mysqli_num_rows($data_dokumen) > 0) {
                            mysqli_data_seek($data_dokumen, 0); 
                            while($row = mysqli_fetch_assoc($data_dokumen)): 
                        ?>
                        <tr>
                            <td class="fw-bold text-dark py-3"><?= $row['nama_dokumen']; ?></td>
                            <td class="text-secondary"><?= $row['deskripsi']; ?></td>
                            <td class="text-secondary"><?= $row['files']; ?></td>
                            <td>
                                <img src="<?= $row['ttd_digital']; ?>" width="75" class="border p-1 bg-white rounded">
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning text-white px-2 py-1"><i class="fa-regular fa-pen-to-square"></i></a>
                                    <a href="index.php?hapus=<?= $row['id']; ?>" class="btn btn-sm btn-danger px-2 py-1" onclick="return confirm('Hapus data pengajuan ini?')"><i class="fa-regular fa-trash-can"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        } else { 
                        ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data berkas masuk.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>