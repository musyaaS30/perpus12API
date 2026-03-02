<?php
// member/riwayat.php
// require_once '../middleware.php';
include "../config.php";

// Verifikasi member
require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['member']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method != "GET") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
    exit();
}

// Cari ID siswa
$member_id = getMemberId($user['nama'], 'siswa');

if (!$member_id) {
    echo json_encode([
        "status" => true,
        "statistik" => [
            "total_peminjaman" => 0,
            "sedang_dipinjam" => 0,
            "sudah_dikembalikan" => 0
        ],
        "data" => []
    ]);
    exit();
}

// Query riwayat peminjaman
$sql = "SELECT 
            p.id_peminjaman,
            b.id_buku,
            b.judul,
            b.image_buku,
            b.penulis,
            b.penerbit,
            p.tanggal_pinjam,
            p.tanggal_kembali,
            p.status,
            DATEDIFF(COALESCE(p.tanggal_kembali, CURDATE()), p.tanggal_pinjam) as lama_pinjam,
            k.nama_kategori
        FROM peminjaman p
        JOIN buku b ON p.id_buku = b.id_buku
        LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
        WHERE p.id_siswa = $member_id
        ORDER BY p.tanggal_pinjam DESC";

$query = mysqli_query($conn, $sql);
$rows = [];

while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
}

// Hitung statistik
$dipinjam = 0;
$dikembalikan = 0;

foreach ($rows as $row) {
    if ($row['status'] == 'dipinjam') {
        $dipinjam++;
    } else {
        $dikembalikan++;
    }
}

echo json_encode([
    "status" => true,
    "statistik" => [
        "total_peminjaman" => count($rows),
        "sedang_dipinjam" => $dipinjam,
        "sudah_dikembalikan" => $dikembalikan
    ],
    "data" => $rows
]);
?>