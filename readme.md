# 📁 BerkasDigital - Sistem Manajemen Berkas & Tanda Tangan Elektronik

Sistem berbasis web (PHP Native) untuk melakukan manajemen pengajuan dokumen/berkas digital yang dilengkapi dengan fitur penandatanganan elektronik secara langsung menggunakan HTML5 Canvas. 

---

## 📦 Struktur File Proyek
Semua fitur diimplementasikan secara modular langsung pada direktori utama guna menjaga performa dan kemudahan pemeliharaan kode:
```text
BERKAS_DIGITAL/
├── index.php      -> Dashboard utama, pencarian data, sistem routing PDF & CSV
├── koneksi.php    -> Konfigurasi koneksi database MySQLi & Session global
├── login.php      -> Autentikasi keamanan hak akses masuk sistem
├── logout.php     -> Penghancuran session user dan pengamanan sistem
├── tambah.php     -> Form input data pengajuan berkas baru + Canvas Pad TTD
├── edit.php       -> Form pembaruan data pengajuan + Opsi pembaruan TTD
└── README.md      -> Dokumentasi deskripsi dan progress fitur aplikasi