<?php
// Koneksi Database
$server = "localhost";
$user = "root";
$password = "";
$database = "tubes";

// buat koneksi
$koneksi = mysqli_connect($server, $user, $password, $database) or die(mysqli_error($koneksi));

// Memeriksa apakah form login telah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query untuk memeriksa email dan password
    $query = $koneksi->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $query->bind_param("ss", $email, $password); // Pastikan password sudah di-hash jika menggunakan hashing
    $query->execute();
    $result = $query->get_result();

    // Jika ada hasil, berarti login berhasil
    if ($result->num_rows > 0) {
        session_start(); // Mulai session
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id']; // Simpan ID pengguna di session
        $_SESSION['user_email'] = $user['email']; // Simpan email di session
        header("Location: index.php"); // Redirect ke halaman utama
        exit();
    } else {
        echo "<script>alert('Email atau password salah.'); window.location.href='login.html';</script>";
    }
}
?>