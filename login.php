<?php
include 'koneksi.php';
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = false;

if (isset($_POST['submit_login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // SET SESSION DENGAN BENAR
        $_SESSION['login'] = true;
        $_SESSION['username'] = $row['username'];
        
        header("Location: index.php");
        exit;
    } else {
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - BerkasDigital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #0f764e; font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { border-radius: 15px; border: none; max-width: 400px; width: 100%; box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .btn-custom { background-color: #0f764e; color: white; }
        .btn-custom:hover { background-color: #0b593a; color: white; }
    </style>
</head>
<body>

    <div class="card card-login p-4 bg-white text-center">
        <div class="mb-3">
            <span style="font-size: 3rem;">📁</span>
            <h4 class="fw-bold text-dark mb-1">Sign In</h4>
            <p class="text-muted small">Selamat datang di BerkasDigital</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                Username atau Password salah!
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="text-start">
            <div class="mb-3">
                <label class="form-label small fw-medium">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" name="submit_login" class="btn btn-custom w-100 py-2 mt-2 rounded-3">Masuk Aplikasi</button>
        </form>
    </div>

</body>
</html>