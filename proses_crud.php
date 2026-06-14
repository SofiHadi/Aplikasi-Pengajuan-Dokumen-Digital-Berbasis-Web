<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

require 'koneksi.php';

$action = $_GET['action'] ?? '';

// 1. TAMBAH DATA
if ($action == 'create') {
    $nama_pengaju = mysqli_real_escape_string($conn, trim($_POST['nama_pengaju']));
    $keterangan = mysqli_real_escape_string($conn, trim($_POST['keterangan']));
    
    // Multi upload file berkas
    $uploadedFiles = [];
    if (!empty($_FILES['berkas']['name'][0])) {
        foreach ($_FILES['berkas']['name'] as $key => $val) {
            $fileName = time() . '_' . basename($_FILES['berkas']['name'][$key]);
            $targetPath = "uploads/" . $fileName;
            if (move_uploaded_file($_FILES['berkas']['tmp_name'][$key], $targetPath)) {
                $uploadedFiles[] = $fileName;
            }
        }
    }
    $filesJson = json_encode($uploadedFiles);

    // Tangkap data base64 tanda tangan langsung
    $signatureData = $_POST['signature_base64'] ?? '';

    $sql = "INSERT INTO dokumen (nama_pengaju, keterangan, files, signature) VALUES ('$nama_pengaju', '$keterangan', '$filesJson', '$signatureData')";
    mysqli_query($conn, $sql);
    header("Location: dashboard.php");
    exit;
}

// 2. PERBARUI / EDIT DATA
if ($action == 'update') {
    $id = intval($_POST['id']);
    $nama_pengaju = mysqli_real_escape_string($conn, trim($_POST['nama_pengaju']));
    $keterangan = mysqli_real_escape_string($conn, trim($_POST['keterangan']));

    $resOld = mysqli_query($conn, "SELECT * FROM dokumen WHERE id = $id");
    $oldData = mysqli_fetch_assoc($resOld);

    // mengupload file baru
    if (!empty($_FILES['berkas']['name'][0])) {
        $uploadedFiles = [];
        foreach ($_FILES['berkas']['name'] as $key => $val) {
            $fileName = time() . '_' . basename($_FILES['berkas']['name'][$key]);
            $targetPath = "uploads/" . $fileName;
            if (move_uploaded_file($_FILES['berkas']['tmp_name'][$key], $targetPath)) {
                $uploadedFiles[] = $fileName;
            }
        }
        $filesJson = json_encode($uploadedFiles);
    } else {
        $filesJson = $oldData['files'];
    }

    // mencoret tanda tangan baru
    if (!empty($_POST['signature_base64'])) {
        $signatureData = $_POST['signature_base64'];
    } else {
        $signatureData = $oldData['signature'];
    }

    $sql = "UPDATE dokumen SET nama_pengaju = '$nama_pengaju', keterangan = '$keterangan', files = '$filesJson', signature = '$signatureData' WHERE id = $id";
    mysqli_query($conn, $sql);
    header("Location: dashboard.php");
    exit;
}

// 3. HAPUS DATA
if ($action == 'delete') {
    $id = intval($_GET['id']);
    
    $res = mysqli_query($conn, "SELECT * FROM dokumen WHERE id = $id");
    $row = mysqli_fetch_assoc($res);
    
    if ($row) {
        $filesArr = json_decode($row['files'], true) ?? [];
        foreach($filesArr as $f) {
            if (file_exists("uploads/".$f)) unlink("uploads/".$f);
        }
    }

    mysqli_query($conn, "DELETE FROM dokumen WHERE id = $id");
    header("Location: dashboard.php");
    exit;
}
?>