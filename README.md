# 📁 Aplikasi Pengajuan dan Pengesahan Dokumen Digital Berbasis Web
ini dibuat untuk memenuhi tugas pemrograman mengenai sistem manajemen dokumen 
## 🚀 Fitur Utama & Progress Tugas

- [x] **Sistem Login**: Akses masuk aman (Session bypass fleksibel) dengan visualisasi tema *Emerald Green* solid.
- [x] **CRUD Operasional**: Pengelolaan penuh penambahan data (*Create*), pembaruan data via modal (*Update*), serta penghapusan data & berkas fisik (*Delete*).
- [x] **Upload Multiple Files**: Fitur unggah banyak berkas sekaligus dalam satu kali pengajuan yang disimpan secara dinamis ke database berbentuk JSON.
- [x] **Datatable & Live Search**: Penyaringan data secara *real-time* di sisi klien langsung pada kolom tabel pengaju tanpa *reload* halaman.
- [x] **Canvas Tanda Tangan Digital**: Fitur tanda tangan interaktif menggunakan HTML5 Canvas dengan kuas berwarna *Emerald Green*, datanya tersimpan aman dalam format enkripsi Base64 LONGTEXT di MySQL.
- [x] **Animasi**: indikator server "ONLINE" menggunakan animasi *pulse/ping* dinamis dari Tailwind.
- [x] **Penggunaan Modal Box**: Form interaktif tambah dan ubah data yang menyembul elegan di tengah layar dengan efek *backdrop blur*.
- [x] **Ekspor Data CSV & PDF**: Fitur unduh rekap laporan dalam format `.csv` (Excel) serta cetak langsung dokumen bersih formal menjadi `.pdf` via print-view browser.

## 🛠️ Persiapan Database
Pastikan database MySQL sudah dikonfigurasi dengan kolom `signature` bertipe teks panjang:
```sql
ALTER TABLE dokumen MODIFY COLUMN signature LONGTEXT NULL;