<?php
require 'config/database.php';

$type = $_GET['type'] ?? 'csv';
$filename = "ekspor_data_" . date('Ymd_His');

$query = "SELECT nama_pengaju, keterangan, files FROM dokumen ORDER BY id DESC";
$result = mysqli_query($conn, $query);

if ($type == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=$filename.xls");
} else {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$filename.csv");
}

// Buka sistem output stream PHP
$output = fopen("php://output", "w");

// Tulis Header Kolom di baris pertama
fputcsv($output, array('Nama Pengaju', 'Keterangan Dokumen', 'Daftar File Berkas'));

// Tulis Data dari MySQL
while ($row = mysqli_fetch_assoc($result)) {
    $filesArr = json_decode($row['files'], true) ?? [];
    $filesString = implode(', ', $filesArr); // jadikan teks pisah koma
    
    fputcsv($output, array($row['nama_pengaju'], $row['keterangan'], $filesString));
}

fclose($output);
exit;
?>