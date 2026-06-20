<?php
include 'koneksi.php'; 

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM dokumen WHERE id=$id");
    $data = mysqli_fetch_assoc($result);
    if (!$data) {
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}

// Proses Update Data
if (isset($_POST['ubah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_pengaju']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['keterangan_dokumen']);
    
    // Cek apakah user mencentang opsi ubah tanda tangan
    if (isset($_POST['ubah_ttd_cek']) && !empty($_POST['ttd_image'])) {
        $ttd_image = $_POST['ttd_image']; // Mengambil data base64 canvas baru
        $query_update = "UPDATE dokumen SET nama_dokumen='$nama', deskripsi='$deskripsi', ttd_digital='$ttd_image' WHERE id=$id";
    } else {
        // Jika tidak dicentang, gunakan TTD lama
        $query_update = "UPDATE dokumen SET nama_dokumen='$nama', deskripsi='$deskripsi' WHERE id=$id";
    }

    mysqli_query($conn, $query_update);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Pengajuan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f9f6; font-family: 'Segoe UI', sans-serif; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        #canvas-ttd { border: 2px dashed #bdc3c7; background: #fafafa; border-radius: 10px; cursor: crosshair; display: block; margin: 0 auto; }
        .canvas-container { display: none; } /* Tersembunyi sebelum dicentang */
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-custom p-4 bg-white">
                    <h4 class="fw-bold text-success mb-4">Ubah Data Pengajuan</h4>
                    
                    <form action="" method="POST" id="form-edit">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nama Pengaju</label>
                            <input type="text" name="nama_pengaju" class="form-control" value="<?= $data['nama_dokumen']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Keterangan Dokumen</label>
                            <textarea name="keterangan_dokumen" class="form-control" rows="3" required><?= $data['deskripsi']; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-medium d-block">Tanda Tangan Saat Ini</label>
                            <img src="<?= $data['ttd_digital']; ?>" width="150" class="border p-2 bg-light rounded mb-2">
                            
                            <div class="form-check form-switch my-3">
                                <input class="form-check-input" type="checkbox" name="ubah_ttd_cek" id="ubah_ttd_cek">
                                <label class="form-check-label small fw-bold text-danger" for="ubah_ttd_cek">Centang untuk mengubah Tanda Tangan</label>
                            </div>
                        </div>

                        <div class="mb-4 canvas-container" id="box-canvas">
                            <label class="form-label small d-block fw-medium text-center text-success">Bubuhkan Tanda Tangan Baru</label>
                            <canvas id="canvas-ttd" width="415" height="150"></canvas>
                            <div class="mt-2 text-end">
                                <button type="button" class="btn btn-sm btn-light border" id="clear-canvas">Bersihkan</button>
                            </div>
                            <input type="hidden" name="ttd_image" id="ttd_image">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-secondary w-50">Batal</a>
                            <button type="submit" name="ubah" class="btn btn-success w-50">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const checkbox = document.getElementById('ubah_ttd_cek');
        const boxCanvas = document.getElementById('box-canvas');
        const canvas = document.getElementById('canvas-ttd');
        const ctx = canvas.getContext('2d');
        let drawing = false;

        ctx.lineWidth = 3; ctx.lineCap = 'round'; ctx.strokeStyle = '#0f764e';

        // Tampilkan/Sembunyikan Canvas
        checkbox.addEventListener('change', function() {
            if(this.checked) {
                boxCanvas.style.display = 'block';
            } else {
                boxCanvas.style.display = 'none';
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                document.getElementById('ttd_image').value = '';
            }
        });

        // Logika Canvas Drawing
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

        document.getElementById('form-edit').addEventListener('submit', function(e) {
            if(checkbox.checked && document.getElementById('ttd_image').value === "") {
                alert("Anda mencentang ubah TTD, mohon isi tanda tangan baru!");
                e.preventDefault();
            }
        });
    </script>
</body>
</html>