<?php
declare(strict_types=1);
require_once 'GuestBook.php';

session_start();

// Inisialisasi Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$guestBook = new GuestBook();
$errors = [];
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi Token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Validasi CSRF gagal.");
    }

    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    // Validasi Masukan
    if ($nama === '') {
        $errors[] = "Nama tidak boleh kosong.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if (mb_strlen($pesan) < 5) {
        $errors[] = "Pesan minimal harus 5 karakter.";
    }

    // Jika lolos validasi, simpan ke database
    if (empty($errors)) {
        if ($guestBook->addEntry($nama, $email, $pesan)) {
            $successMessage = "Pesan berhasil dikirim!";
            $nama = $email = $pesan = "";
        } else {
            $errors[] = "Gagal menyimpan pesan ke basis data.";
        }
    }
}

$entries = $guestBook->getEntries();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Tamu Digital Perpustakaan</title>
    <style>
        body { font-family: sans-serif; margin: 20px; padding: 20px; max-width: 800px; margin: auto; }
        .error { color: red; }
        .success { color: green; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 8px; box-sizing: border-box; }
    </style>
</head>
<body>
    <h1>Buku Tamu Digital Perpustakaan</h1>

    <?php if (!empty($successMessage)): ?>
        <p class="success"><?= $successMessage ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul class="error">
            <?php foreach ($errors as $err): ?>
                <li><?= $err ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Formulir Buku Tamu -->
    <form action="guestbook.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="form-group">
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" value="<?= isset($nama) ? htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') : '' ?>">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '' ?>">
        </div>

        <div class="form-group">
            <label for="pesan">Pesan (Minimal 5 karakter):</label>
            <textarea id="pesan" name="pesan" rows="4"><?= isset($pesan) ? htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') : '' ?></textarea>
        </div>

        <button type="submit">Kirim Pesan</button>
    </form>

    <hr>

    <h2>Daftar Pesan Tamu</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Pesan</th>
                <th>Tanggal Kirim</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entries)): ?>
                <tr><td colspan="5" style="text-align: center;">Belum ada pesan.</td></tr>
            <?php else: ?>
                <?php foreach ($entries as $index => $entry): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $entry['nama'] ?></td>
                        <td><?= $entry['email'] ?></td>
                        <td><?= $entry['pesan'] ?></td>
                        <td><?= $entry['tanggal_kirim'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>