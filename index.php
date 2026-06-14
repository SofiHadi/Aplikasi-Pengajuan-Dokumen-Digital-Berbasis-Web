<?php
session_start();
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Berkas Digital</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <style>
        body {
            background-color: #047857 !important; /* Warna Hijau Emerald Asli */
        }
        .kotak-login {
            background-color: #ffffff !important; /* Kotak tengah tetap putih bersih */
        }
        .tombol-hijau {
            background-color: #047857 !important; /* Tombol juga Hijau Emerald */
            color: white !important;
        }
        .tombol-hijau:hover {
            background-color: #065f46 !important; /* Warna saat disentuh kursor (lebih gelap dikit) */
        }
    </style>
</head>
<body class="flex min-h-screen items-center justify-center px-4">

    <div class="kotak-login w-full max-w-md p-8 rounded-2xl shadow-2xl border border-emerald-800/20">
        <div class="text-center mb-6">
            <span class="text-4xl">📁</span>
            <h2 class="text-3xl font-bold text-emerald-950 mt-2">Sign In</h2>
            <p class="text-sm text-slate-500">Selamat datang di BerkasDigital</p>
        </div>
        
        <?php if (isset($_GET['pesan'])): ?>
            <div class="mb-4 p-3 bg-rose-100 text-rose-700 text-sm rounded-lg text-center font-medium">
                <?= htmlspecialchars($_GET['pesan']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST" class="space-y-4">
            <div>
                <label class="text-sm font-medium text-slate-700 block mb-1">Username</label>
                <input type="text" name="username" required placeholder="Masukkan username" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
            </div>
            
            <div>
                <label class="text-sm font-medium text-slate-700 block mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
            </div>
            
            <button type="submit" class="tombol-hijau w-full py-2.5 font-semibold rounded-lg transition mt-2 cursor-pointer shadow-md">Masuk Aplikasi</button>
        </form>
    </div>

</body>
</html>