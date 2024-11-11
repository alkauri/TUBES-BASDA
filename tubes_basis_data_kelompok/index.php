<?php
// Koneksi Database
$server = "localhost";
$user = "root";
$password = "";
$database = "tubes";

// buat koneksi
$koneksi = mysqli_connect($server, $user, $password, $database) or die(mysqli_error($koneksi));

// Kode otomatis
$q = mysqli_query($koneksi, "SELECT kode_barang FROM tbarang ORDER BY kode_barang DESC LIMIT 1");
$datax = mysqli_fetch_array($q);

if ($datax) {
    $no_terakhir = (int)substr($datax['kode_barang'], -3);
    $no = $no_terakhir + 1;

    if ($no < 10) {
        $kode_barang = "00" . $no;
    } elseif ($no < 100) {
        $kode_barang = "0" . $no;
    } else {
        $kode_barang = (string)$no;
    }
} else {
    $kode_barang = "001";
}

$vkode = "BRG" . $kode_barang;

// jika tombol simpan diklik
if (isset($_POST['bsimpan'])) {
  if (isset($_GET['hal']) && $_GET['hal'] == "edit") {
      // Update
      $stmt = $koneksi->prepare("UPDATE tbarang SET 
          kode_barang = ?, 
          nama_barang = ?, 
          asal_barang = ?, 
          jumlah = ?, 
          satuan = ?, 
          tanggal_diterima = ?, 
          id_supplier = ? 
          WHERE id_barang = ?");
      
      $stmt->bind_param("sssiisii", $_POST['tkode'], $_POST['tnama'], $_POST['tasal'], $_POST['tjumlah'], $_POST['tsatuan'], $_POST['ttanggal_diterima'], $_POST['tsupplier'], $_GET['id']);
      
      if ($stmt->execute()) {
          echo "<script>alert('Edit data sukses!'); document.location='index.php';</script>";
      } else {
          echo "<script>alert('Edit data gagal!'); document.location='index.php';</script>";
      }
      $stmt->close();
  } else {
      // Insert
      $stmt = $koneksi->prepare("INSERT INTO tbarang (kode_barang, nama_barang, asal_barang, jumlah, satuan, tanggal_diterima, id_supplier) VALUES (?, ?, ?, ?, ?, ?, ?)");
      
      $stmt->bind_param("sssiisi", $_POST['tkode'], $_POST['tnama'], $_POST['tasal'], $_POST['tjumlah'], $_POST['tsatuan'], $_POST['ttanggal_diterima'], $_POST['tsupplier']);
      
      if ($stmt->execute()) {
          echo "<script>alert('Simpan data sukses!'); document.location='index.php';</script>";
      } else {
          echo "<script>alert('Simpan data gagal!'); document.location='index.php';</script>";
      }
      $stmt->close();
  }
}

// jika tombol simpan supplier diklik
if (isset($_POST['bsimpan_supplier'])) {
    $stmt = $koneksi->prepare("INSERT INTO tsupplier (nama_supplier, alamat_supplier, telepon_supplier) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['tnama_supplier'], $_POST['talamat_supplier'], $_POST['ttelepon_supplier']);
    
    if ($stmt->execute()) {
        echo "<script>alert('Simpan data supplier sukses!'); document.location='index.php';</script>";
    } else {
        echo "<script>alert('Simpan data supplier gagal!'); document.location='index.php';</script>";
    }
    $stmt->close();
}

// deklarasi variabel untuk menampung data yang akan di edit
$vnama = $vasal = $vjumlah = $vsatuan = $vtanggal_diterima = "";
$vnama_supplier = $valamat_supplier = $vtelepon_supplier = "";

// Pengujian jika tombol edit atau hapus diklik
if (isset($_GET['hal'])) {
  if ($_GET['hal'] == 'edit') {
      // Pastikan tidak ada spasi di 'id'
      $stmt = $koneksi->prepare("SELECT * FROM tbarang WHERE id_barang = ?");
      $stmt->bind_param("i", $_GET['id']); // Perbaiki di sini
      $stmt->execute();
      $result = $stmt->get_result();
      
      if ($data = $result->fetch_assoc()) {
          // Ambil data dari database
          $vkode = $data['kode_barang'];
          $vnama = $data['nama_barang'];
          $vasal = $data['asal_barang'];
          $vjumlah = $data['jumlah'];
          $vsatuan = isset($data['satuan']) ? $data['satuan'] : ''; // Pastikan ada nilai
          $vtanggal_diterima = $data['tanggal_diterima'];
      } else {
          echo "<script>alert('Data tidak ditemukan!'); document.location='index.php';</script>";
      }
      $stmt->close();
  } elseif ($_GET['hal'] == 'hapus') {
      $stmt = $koneksi->prepare("DELETE FROM tbarang WHERE id_barang = ?");
      $stmt->bind_param("i", $_GET['id']);
      
      if ($stmt->execute()) {
          echo "<script>alert('Data berhasil dihapus!'); document.location='index.php';</script>";
      } else {
          echo "<script>alert('Data gagal dihapus!'); document.location='index.php';</script>";
      }
      $stmt->close();
  } elseif ($_GET['hal'] == 'edit_supplier') {
      $stmt = $koneksi->prepare("SELECT * FROM tsupplier WHERE id_supplier = ?");
      $stmt->bind_param("i", $_GET['id']);
      $stmt->execute();
      $result_supplier = $stmt->get_result();
      
      if ($data_supplier = $result_supplier->fetch_assoc()) {
          $vnama_supplier = $data_supplier['nama_supplier'];
          $valamat_supplier = $data_supplier['alamat_supplier'];
          $vtelepon_supplier = $data_supplier['telepon_supplier'];
      }
      $stmt->close();
  } elseif ($_GET['hal'] == 'hapus_supplier') {
      $stmt = $koneksi->prepare("DELETE FROM tsupplier WHERE id_supplier = ?");
      $stmt->bind_param("i", $_GET['id']);
      
      if ($stmt->execute()) {
          echo "<script>alert('Data supplier berhasil dihapus!'); document.location='index.php';</script>";
      } else {
          echo "<script>alert('Data supplier gagal dihapus!'); document.location='index.php';</script>";
      }
      $stmt->close();
  }
}

// Fitur pencarian
$filter_barang = "";
$filter_supplier = "";

// Jika pencarian dilakukan
if (isset($_POST['bcari']) && !empty($_POST['tcari'])) {
    $cari = mysqli_real_escape_string($koneksi, $_POST['tcari']);
    $filter_barang = "WHERE nama_barang LIKE '%$cari%' OR kode_barang LIKE '%$cari%'";
    $filter_supplier = "WHERE nama_supplier LIKE '%$cari%' OR alamat_supplier LIKE '%$cari%'";
}

// Reset pencarian
if (isset($_POST['breset'])) {
    $filter_barang = "";
    $filter_supplier = "";
    $_POST['tcari'] = ""; // Kosongkan input pencarian
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>SISTEM INFORMASI</title>
</head>
<body>
<nav class="navbar navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="gambar/hu tao.png" alt="hu tao" width="30" height="24" class="d-inline-block align-text-top">
            Project Group 1
        </a>
    </div>
</nav>

<div class="container">
    <h1 style="text-align: center;">
        <span style="color: blue;">SISTEM INFORMASI</span>
        <span style="color: green;">INVENTARIS KANTOR</span>
    </h1>

    <div class="row">
        <div class="col-md-8 mx-auto">

           <!-- Form Update Data Barang -->
<div class="card-header bg-primary text-light">
    Form Update Data Barang
</div>
<form method="POST">
    <div class="mb-3">
        <label class="form-label">Supplier</label>
        <select class="form-select" name="tsupplier" required>
            <option value="">- Pilih Supplier -</option>
            <?php
            $supplier = mysqli_query($koneksi, "SELECT * FROM tsupplier ORDER BY nama_supplier ASC");
            while ($row = mysqli_fetch_array($supplier)) {
                // Pastikan membandingkan dengan id_supplier
                $selected = ($row['id_supplier'] == $data['id_supplier']) ? "selected" : ""; // Untuk edit
                echo "<option value='{$row['id_supplier']}' $selected>{$row['nama_supplier']}</option>";
            }
            ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Kode Barang</label>
        <input type="text" name="tkode" value="<?=$vkode?>" class="form-control" placeholder="Masukkan ID Barang" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Nama Barang</label>
        <input type="text" name="tnama" value="<?=$vnama?>" class="form-control" placeholder="Masukkan Nama Barang" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Asal Barang</label>
        <select class="form-select" name="tasal" required>
            <option value="">-Pilih-</option>
            <option value="Pembelian" <?= ($vasal == "Pembelian") ? "selected" : "" ?>>Pembelian</option>
            <option value="Hibah" <?= ($vasal == "Hibah") ? "selected" : "" ?>>Hibah</option>
            <option value="Bantuan" <?= ($vasal == "Bantuan") ? "selected" : "" ?>>Bantuan</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Jumlah</label>
        <input type="number" name="tjumlah" value="<?=$vjumlah?>" class="form-control" placeholder="Masukkan Jumlah Barang" required>
    <div class="mb-3">
        <label class="form-label">Tanggal Diterima</label>
        <input type="date" name="ttanggal_diterima" value="<?=$vtanggal_diterima?>" class="form-control" required>
    </div>
    <div class="text-center">
        <hr>
        <button class="btn btn-primary" name="bsimpan" type="submit">Simpan</button>
        <button class="btn btn-danger" name="bkosongkan" type="reset">Kosongkan</button>
    </div>
</form>

        <!-- Form Tambah Data Supplier -->
        <div class="card-header bg-primary text-light mt-4">
            Form Tambah Data Supplier
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Nama Supplier</label>
                    <input type="text" name="tnama_supplier" value="<?=$vnama_supplier?>" class="form-control" placeholder="Masukkan Nama Supplier" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat Supplier</label>
                    <textarea name="talamat_supplier" class="form-control" placeholder="Masukkan Alamat Supplier" required><?=$valamat_supplier?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telepon Supplier</label>
                    <input type="text" name="ttelepon_supplier" value="<?=$vtelepon_supplier?>" class="form-control" placeholder="Masukkan Telepon Supplier" required>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary" name="bsimpan_supplier" type="submit">Simpan Supplier</button>
                </div>
            </form>
        </div>

        <!-- Form Pencarian -->
        <form method="POST">
            <div class="input-group mb-3">
                <input type="text" name="tcari" value="<?= isset($_POST['tcari']) ? $_POST['tcari'] : '' ?>" class="form-control" placeholder="Cari Data">
                <button class="btn btn-primary" name="bcari" type="submit">Cari</button>
                <button class="btn btn-danger" name="breset" type="submit">Reset</button>
            </div>
        </form>
        <!-- Form Pencarian End -->

        <!-- Tabel Data Barang -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-light">
                Tabel Data Barang
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover table-bordered border-primary">
                    <tr>
                        <th>No</th>
                        <th>ID Barang</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Asal Barang</th>
                        <th>Nama Supplier</th>
                        <th>Jumlah</th>
                        <th>Tanggal Diterima</th>
                        <th>Aksi</th>
                    </tr>
                    <?php
                    $no = 1;
                    $tampil = mysqli_query($koneksi, "SELECT tbarang.*, tsupplier.nama_supplier 
                                      FROM tbarang 
                                      LEFT JOIN tsupplier ON tbarang.id_supplier = tsupplier.id_supplier 
                                      $filter_barang 
                                      ORDER BY tbarang.id_barang DESC");
                    while ($data = mysqli_fetch_array($tampil)) :
                    ?>
                    <tr>
                        <td><?=$no++ ?></td>
                        <td><?=$data['id_barang']?></td>
                        <td><?=$data['kode_barang']?></td>
                        <td><?=$data['nama_barang']?></td>
                        <td><?=$data['asal_barang']?></td>
                        <td><?=$data['nama_supplier']?></td>
                        <td><?=$data['jumlah']?></td>
                        <td><?=$data['tanggal_diterima']?></td>
                        <td>
                            <a href="index.php?hal=edit&id=<?=$data['id_barang']?>" class="btn btn-success">Edit</a>
                            <a href="index.php?hal=hapus&id=<?=$data['id_barang']?>" class="btn btn-danger">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile ?>
                </table>
            </div>
        </div>

        <!-- Tabel Data Supplier -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-light">
                Tabel Data Supplier
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover table-bordered border-secondary">
                    <tr>
                        <th>No</th>
                        <th>ID Supplier</th>
                        <th>Nama Supplier</th>
                        <th>Alamat Supplier</th>
                        <th>Telepon Supplier</th>
                        <th>Aksi</th>
                    </tr>
                    <?php
                    $no = 1;
                    $tampil_supplier = mysqli_query($koneksi, "SELECT * FROM tsupplier $filter_supplier ORDER BY id_supplier DESC");
                    while ($data_supplier = mysqli_fetch_array($tampil_supplier)) :
                    ?>
                    <tr>
                        <td><?=$no++ ?></td>
                        <td><?=$data_supplier['id_supplier']?></td>
                        <td><?=$data_supplier['nama_supplier']?></td>
                        <td><?=$data_supplier['alamat_supplier']?></td>
                        <td><?=$data_supplier['telepon_supplier']?></td>
                        <td>
                            <a href="index.php?hal=edit_supplier&id=<?=$data_supplier['id_supplier']?>" class="btn btn-success">Edit</a>
                            <a href="index.php?hal=hapus_supplier&id=<?=$data_supplier['id_supplier']?>" class="btn btn-danger">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile ?>
                </table>
            </div>
        </div>

    </div>
</div>
</body>
</html>

