<?php
session_start(); // Mulai session di awal

// Koneksi Database
$server = "localhost";
$user = "root";
$password = "";
$database = "tubes";

// Buat koneksi
$koneksi = mysqli_connect($server, $user, $password, $database);
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query untuk mencari user di database
    $query = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ss", $email, $password); // Sesuaikan dengan hash jika ada
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek apakah login berhasil
    if ($result->num_rows > 0) {
        $_SESSION['email'] = $email; // Simpan email ke dalam session jika berhasil
        echo "<script>
                alert('Login berhasil!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Email atau password salah!');
                window.location.href = 'login.php';
              </script>";
    }

    $stmt->close();
}
?>
