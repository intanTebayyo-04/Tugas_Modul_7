<?php
declare(strict_types=1);

class GuestBook {
    private ?PDO $pdo = null;

    public function __construct() {
        $host = '127.0.0.1';
        $db   = 'akademik_db';
        $user = 'root'; // Sesuaikan username database
        $pass = '';     // Sesuaikan password database
        $dsn  = "mysql:host={$host};dbname={$db};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Sistem mengalami kegagalan teknis.");
        }
    }

    // Menyimpan data dengan Prepared Statements
    public function addEntry(string $nama, string $email, string $pesan): bool {
        $sql = "INSERT INTO buku_tamu (nama, email, pesan) VALUES (:nama, :email, :pesan)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'nama'  => $nama,
            'email' => $email,
            'pesan' => $pesan
        ]);
    }

    // Mengambil data dengan Prepared Statements & Sanitasi Keluaran
    public function getEntries(): array {
        $sql = "SELECT id, nama, email, pesan, tanggal_kirim FROM buku_tamu ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rawEntries = $stmt->fetchAll();

        // Sanitasi keluaran (mencegah XSS)
        $entries = [];
        foreach ($rawEntries as $row) {
            $entries[] = [
                'id'            => $row['id'],
                'nama'          => htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'),
                'email'         => htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'),
                'pesan'         => htmlspecialchars($row['pesan'], ENT_QUOTES, 'UTF-8'),
                'tanggal_kirim' => htmlspecialchars($row['tanggal_kirim'], ENT_QUOTES, 'UTF-8')
            ];
        }
        return $entries;
    }
}