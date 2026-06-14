<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

require 'koneksi.php'; 

// --- FITUR EKSPOR CSV LANGSUNG (ANTI 404 NOT FOUND) ---
if (isset($_GET['action']) && $_GET['action'] == 'download_csv') {
    ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Berkas_Digital_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Nama Pengaju', 'Keterangan Dokumen', 'Nama-Nama Berkas']);
    
    $query = "SELECT * FROM dokumen ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $filesArr = json_decode($row['files'], true) ?? [];
        $namaFiles = implode(', ', $filesArr);
        fputcsv($output, [
            $row['id'],
            $row['nama_pengaju'],
            $row['keterangan'],
            $namaFiles
        ]);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Proyek Dokumen</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        #signature-canvas { 
            border: 2px dashed #a7f3d0; 
            cursor: crosshair; 
        }

        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            header, .no-print, button, a, #search-input, .area-aksi-tabel {
                display: none !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
            }
            .kotak-tabel {
                box-shadow: none !important;
                border: none !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #cbd5e1 !important;
                padding: 8px !important;
            }
        }
    </style>
</head>
<body class="bg-emerald-50/40 text-slate-700 font-sans">

    <audio id="audio-click" src="https://assets.mixkit.co/active_storage/sfx/2568/2568-84.wav" preload="auto"></audio>

    <header class="bg-white border-b border-emerald-100 px-6 py-4 flex justify-between items-center shadow-xs">
        <h1 class="text-xl font-bold text-emerald-950 flex items-center gap-2">
            <span class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg">📁</span> BerkasDigital
        </h1>
        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-600">Halo, <strong class="text-emerald-600"><?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong></span>
            <a href="logout.php" onclick="playAudio()" class="text-sm px-3 py-1.5 bg-rose-50 text-rose-600 font-semibold rounded-lg hover:bg-rose-100 transition-colors">Logout</a>
        </div>
    </header>

    <main class="p-6 max-w-7xl mx-auto space-y-6">
        
        <div class="w-full no-print">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-5 rounded-xl text-white flex flex-col sm:flex-row items-center justify-between shadow-md shadow-emerald-500/10 gap-4">
                <div>
                    <h4 class="font-bold text-lg">Sistem Utama Berjalan</h4>
                    <p class="text-xs text-emerald-100/80">Sistem pengelolaan data, upload multi-file, pencarian datatable, ekspor dokumen, dan validasi tanda tangan digital aktif.</p>
                </div>
                <div class="flex items-center gap-2 bg-emerald-700/40 px-3 py-1.5 rounded-full">
                    <span class="w-3 h-3 bg-emerald-300 rounded-full animate-ping"></span>
                    <span class="text-xs font-mono tracking-wider uppercase font-bold">Online</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-emerald-100 kotak-tabel overflow-hidden">
            <div class="p-6 border-b border-emerald-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-emerald-50/50">
                <div>
                    <h3 class="text-lg font-bold text-emerald-950">Data Pengajuan Dokumen</h3>
                    <p class="text-sm text-slate-500">Daftar berkas masuk dari database yang divalidasi tanda tangan.</p>
                </div>
                
                <div class="flex flex-wrap w-full md:w-auto items-center gap-2 no-print">
                    <input type="text" id="search-input" onkeyup="searchTable()" placeholder="🔍 Cari nama pengaju..." class="px-3 py-2 border border-emerald-200 bg-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-emerald-500 w-full sm:w-64">
                    
                    <a href="dashboard.php?action=download_csv" onclick="playAudio()" class="px-4 py-2 bg-slate-700 text-white text-sm font-bold rounded-lg hover:bg-slate-800 shadow-md transition-all text-center w-full sm:w-auto">📥 Ekspor CSV</a>
                    
                    <button onclick="exportToPDF()" class="px-4 py-2 bg-rose-600 text-white text-sm font-bold rounded-lg hover:bg-rose-700 shadow-md transition-all text-center w-full sm:w-auto cursor-pointer">📄 Ekspor PDF</button>
                    
                    <button onclick="openModal('create')" class="px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 shadow-md shadow-emerald-600/10 transition-all cursor-pointer w-full sm:w-auto">➕ Tambah Data</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="data-table">
                    <thead>
                        <tr class="border-b border-emerald-100 bg-emerald-50/30 text-xs font-bold uppercase text-emerald-800">
                            <th class="py-3.5 px-6">Nama Pengaju</th>
                            <th class="py-3.5 px-6">Keterangan</th>
                            <th class="py-3.5 px-6">Files Berkas</th>
                            <th class="py-3.5 px-6">Tanda Tangan</th>
                            <th class="py-3.5 px-6 text-right area-aksi-tabel">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50 text-sm text-slate-600">
                        <?php 
                            $query_berkas = "SELECT * FROM dokumen ORDER BY id DESC"; 
                            $result = mysqli_query($conn, $query_berkas);

                            if (mysqli_num_rows($result) == 0) : 
                        ?>
                        <tr id="no-data-row">
                            <td colspan="5" class="py-10 text-center text-sm text-slate-400 italic bg-slate-50/30">
                                Belum ada data pengajuan dokumen masuk di database.
                            </td>
                        </tr>
                        <?php 
                            else:
                                while($row = mysqli_fetch_assoc($result)): 
                                    $filesArr = json_decode($row['files'], true) ?? [];
                        ?>
                        <tr class="table-row-data hover:bg-emerald-50/20 transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-900 target-search"><?= htmlspecialchars($row['nama_pengaju']); ?></td>
                            <td class="py-3.5 px-6"><?= htmlspecialchars($row['keterangan']); ?></td>
                            <td class="py-3.5 px-6">
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach($filesArr as $f): ?>
                                        <span class="px-2 py-0.5 rounded border border-emerald-100 text-xs bg-emerald-50 text-emerald-700 font-mono"><?= htmlspecialchars($f); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="py-3.5 px-6">
                                <?php if(!empty($row['signature']) && strlen($row['signature']) > 50): ?>
                                    <img src="<?= $row['signature']; ?>" class="h-10 border border-emerald-200 bg-white rounded max-w-[140px] p-0.5" alt="Tanda Tangan">
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">Belum ttd</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-6 text-right space-x-1 whitespace-nowrap area-aksi-tabel">
                                <button onclick="openModal('edit', <?= htmlspecialchars(json_encode($row)); ?>)" class="px-2.5 py-1 bg-amber-500 text-white font-semibold text-xs rounded hover:bg-amber-600 transition-colors cursor-pointer">Edit</button>
                                <a href="proses_crud.php?action=delete&id=<?= $row['id']; ?>" onclick="playAudio(); return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="px-2.5 py-1 bg-rose-600 text-white font-semibold text-xs rounded hover:bg-rose-700 transition-colors inline-block">Hapus</a>
                            </td>
                        </tr>
                        <?php 
                                endwhile; 
                            endif; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="crud-modal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-lg w-full flex flex-col shadow-xl border border-emerald-100">
            <div class="px-6 py-4 border-b border-emerald-100 flex justify-between items-center bg-emerald-50 rounded-t-xl">
                <h3 id="modal-title" class="text-lg font-bold text-emerald-950">Tambah Data Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-rose-600 text-2xl font-bold transition-colors cursor-pointer">&times;</button>
            </div>
            
            <form id="modal-form" action="proses_crud.php?action=create" method="POST" enctype="multipart/form-data" onsubmit="prepareSubmit(event)" class="p-6 space-y-4">
                <input type="hidden" name="id" id="form-id">
                <input type="hidden" name="signature_base64" id="signature_base64">

                <div>
                    <label class="text-sm font-semibold text-emerald-900 block mb-1">Nama Lengkap Pengaju</label>
                    <input type="text" name="nama_pengaju" id="form-nama" required placeholder="Masukkan nama pengaju" class="w-full px-3 py-2 border border-emerald-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="text-sm font-semibold text-emerald-900 block mb-1">Keterangan Dokumen</label>
                    <textarea name="keterangan" id="form-keterangan" required rows="2" placeholder="Masukkan keterangan dokumen" class="w-full px-3 py-2 border border-emerald-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-emerald-900 block mb-1">Upload Berkas Dokumen <span id="file-label-info" class="text-xs text-slate-400 font-normal">(Bisa pilih banyak)</span></label>
                    <input type="file" name="berkas[]" id="form-files" multiple class="w-full text-sm text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 file:transition-colors file:cursor-pointer">
                    <p id="edit-file-warning" class="hidden text-xs text-amber-600 mt-1">💡 Kosongkan jika tidak ingin mengubah dokumen lama.</p>
                </div>
                
                <div>
                    <label class="text-sm font-semibold text-emerald-900 block mb-1">Gores Tanda Tangan Digital Anda</label>
                    <div class="bg-emerald-50/30 rounded-lg p-2 border border-emerald-100">
                        <canvas id="signature-canvas" width="400" height="130" class="w-full bg-white rounded-md shadow-inner"></canvas>
                        <div class="mt-2 flex justify-end">
                            <button type="button" onclick="clearCanvas()" class="px-2.5 py-1 text-xs font-semibold text-rose-600 bg-rose-50 rounded-md hover:bg-rose-100 transition-colors cursor-pointer">Hapus Coretan</button>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-emerald-100 flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" id="submit-btn" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/10 transition-all cursor-pointer">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('crud-modal');
        const form = document.getElementById('modal-form');
        const modalTitle = document.getElementById('modal-title');
        const submitBtn = document.getElementById('submit-btn');
        
        const canvas = document.getElementById('signature-canvas');
        const ctx = canvas.getContext('2d');
        const audioClick = document.getElementById('audio-click');
        let isDrawing = false;

        function playAudio() {
            audioClick.currentTime = 0;
            audioClick.play();
        }

        // Fungsi Cetak Otomatis Jadi PDF Resmi
        function exportToPDF() {
            playAudio();
            setTimeout(() => {
                window.print();
            }, 300);
        }

        function getMousePos(canvasDom, strokeEvent) {
            const rect = canvasDom.getBoundingClientRect();
            return {
                x: strokeEvent.clientX - rect.left,
                y: strokeEvent.clientY - rect.top
            };
        }

        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            const pos = getMousePos(canvas, e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        });

        canvas.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            const pos = getMousePos(canvas, e);
            ctx.lineWidth = 3; 
            ctx.lineCap = 'round'; 
            ctx.strokeStyle = '#047857'; 
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        });

        window.addEventListener('mouseup', () => isDrawing = false);

        function clearCanvas() { 
            ctx.clearRect(0, 0, canvas.width, canvas.height); 
            ctx.beginPath(); 
            playAudio();
        }
        
        function openModal(mode, data = null) { 
            playAudio();
            modal.classList.remove('hidden'); 
            clearCanvas(); 

            if (mode === 'create') {
                modalTitle.textContent = "Tambah Data Baru";
                form.action = "proses_crud.php?action=create";
                submitBtn.textContent = "Simpan Data";
                form.reset();
                document.getElementById('form-id').value = "";
                document.getElementById('form-files').required = true;
                document.getElementById('edit-file-warning').classList.add('hidden');
            } else if (mode === 'edit' && data) {
                modalTitle.textContent = "Edit Data Pengajuan";
                form.action = "proses_crud.php?action=update";
                submitBtn.textContent = "Perbarui Data";
                
                document.getElementById('form-id').value = data.id;
                document.getElementById('form-nama').value = data.nama_pengaju;
                document.getElementById('form-keterangan').value = data.keterangan;
                
                document.getElementById('form-files').required = false;
                document.getElementById('edit-file-warning').classList.remove('hidden');
            }
        }
        
        function closeModal() { 
            modal.classList.add('hidden'); 
            playAudio();
        }

        function prepareSubmit(e) {
            const blank = document.createElement('canvas');
            blank.width = canvas.width; 
            blank.height = canvas.height;
            
            if (canvas.toDataURL() !== blank.toDataURL()) {
                document.getElementById('signature_base64').value = canvas.toDataURL();
            } else {
                document.getElementById('signature_base64').value = "";
            }
        }

        function searchTable() {
            const input = document.getElementById('search-input').value.toLowerCase();
            const rows = document.getElementsByClassName('table-row-data');
            let foundAny = false;

            for (let i = 0; i < rows.length; i++) {
                const targetText = rows[i].querySelector('.target-search').textContent.toLowerCase();
                if (targetText.includes(input)) {
                    rows[i].style.display = "";
                    foundAny = true;
                } else {
                    rows[i].style.display = "none";
                }
            }

            const noDataRow = document.getElementById('no-data-row');
            if (noDataRow) {
                noDataRow.style.display = foundAny ? "none" : "";
            }
        }
    </script>
</body>
</html>