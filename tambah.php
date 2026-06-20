<?php
include 'koneksi.php';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_pengaju']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['keterangan_dokumen']);
    $ttd_image = $_POST['ttd_image']; 

    $uploaded_files = [];
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['name'] as $key => $val) {
            $file_name = time() . '_' . $_FILES['files']['name'][$key];
            $target = 'uploads/' . $file_name;
            if (move_uploaded_file($_FILES['files']['tmp_name'][$key], $target)) {
                $uploaded_files[] = $file_name;
            }
        }
    }
    $files_json = json_encode($uploaded_files);

    $query = "INSERT INTO dokumen (nama_dokumen, deskripsi, files, ttd_digital) VALUES ('$nama', '$deskripsi', '$files_json', '$ttd_image')";
    mysqli_query($conn, $query);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengajuan Baru</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f9f6; font-family: 'Segoe UI', sans-serif; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        #canvas-ttd { border: 2px dashed #bdc3c7; background: #fafafa; border-radius: 10px; cursor: crosshair; display: block; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-custom p-4 bg-white">
                    <h4 class="fw-bold text-success mb-4">Form Pengajuan Baru</h4>
                    
                    <form action="" method="POST" enctype="multipart/form-data" id="form-dokumen">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nama Pengaju</label>
                            <input type="text" name="nama_pengaju" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Keterangan Dokumen</label>
                            <textarea name="keterangan_dokumen" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Berkas (Multiple)</label>
                            <input type="file" name="files[]" class="form-control" multiple required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small d-block fw-medium text-center">Bubuhkan Tanda Tangan Digital</label>
                            <canvas id="canvas-ttd" width="415" height="150"></canvas>
                            <div class="mt-2 text-end">
                                <button type="button" class="btn btn-sm btn-light border" id="clear-canvas">Bersihkan</button>
                            </div>
                            <input type="hidden" name="ttd_image" id="ttd_image">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-secondary w-50">Batal</a>
                            <button type="submit" name="simpan" class="btn btn-success w-50">Kirim Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('canvas-ttd');
        const ctx = canvas.getContext('2d');
        let drawing = false;

        ctx.lineWidth = 3; ctx.lineCap = 'round'; ctx.strokeStyle = '#0f764e';

        canvas.addEventListener('mousedown', (e) => { drawing = true; draw(e); });
        canvas.addEventListener('mouseup', () => { drawing = false; ctx.beginPath(); saveSignature(); });
        canvas.addEventListener('mousemove', draw);

        function draw(e) {
            if (!drawing) return;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ctx.lineTo(x, y); ctx.stroke(); ctx.beginPath(); ctx.moveTo(x, y);
        }

        function saveSignature() { document.getElementById('ttd_image').value = canvas.toDataURL(); }

        document.getElementById('clear-canvas').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('ttd_image').value = '';
        });

        document.getElementById('form-dokumen').addEventListener('submit', function(e) {
            if(document.getElementById('ttd_image').value === "") {
                alert("Tanda tangan belum diisi!");
                e.preventDefault();
            }
        });
    </script>
</body>
</html>