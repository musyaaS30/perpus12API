<?php
// require_once '../middleware.php';
// member/buku_dipinjam.php
include "../config.php";

// Hanya member
$user = requireAuth(3);

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
        "total_dipinjam" => 0,
        "data" => []
    ]);
    exit();
}

// Query untuk buku yang sedang dipinjam
$sql = "SELECT 
            p.id_peminjaman,
            b.id_buku,
            b.judul,
            b.image_buku,
            b.penulis,
            b.penerbit,
            p.tanggal_pinjam,
            p.status,
            DATEDIFF(CURDATE(), p.tanggal_pinjam) as hari_terpinjam,
            DATE_ADD(p.tanggal_pinjam, INTERVAL 7 DAY) as batas_kembali,
            CASE 
                WHEN DATEDIFF(CURDATE(), p.tanggal_pinjam) > 7 THEN 'terlambat'
                WHEN DATEDIFF(CURDATE(), p.tanggal_pinjam) >= 5 THEN 'segera'
                ELSE 'aman'
            END as status_pinjam,
            k.nama_kategori
        FROM peminjaman p
        JOIN buku b ON p.id_buku = b.id_buku
        LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
        WHERE p.id_siswa = $member_id
        AND p.status = 'dipinjam'
        ORDER BY p.tanggal_pinjam ASC";

$query = mysqli_query($conn, $sql);
$rows = [];

while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
}

echo json_encode([
    "status" => true,
    "total_dipinjam" => count($rows),
    "data" => $rows
]);
?>