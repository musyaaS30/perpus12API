<?php
// member/buku.php
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

$search = $_GET['search'] ?? '';
$kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;

$sql = "SELECT 
            b.id_buku,
            b.judul,
            b.image_buku,
            b.deskripsi,
            b.penulis,
            b.penerbit,
            b.tahun_terbit,
            b.halaman,
            b.stok,
            k.nama_kategori";

// Cek apakah buku sedang dipinjam oleh user ini
if ($member_id) {
    $sql .= ", (SELECT COUNT(*) FROM peminjaman p 
                WHERE p.id_buku = b.id_buku 
                AND p.id_siswa = $member_id
                AND p.status = 'dipinjam') as sedang_dipinjam";
} else {
    $sql .= ", 0 as sedang_dipinjam";
}

$sql .= " FROM buku b
          LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
          WHERE b.stok > 0";

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (b.judul LIKE '%$search%' OR b.penulis LIKE '%$search%')";
}

if ($kategori > 0) {
    $sql .= " AND b.id_kategori = $kategori";
}

$sql .= " ORDER BY b.judul ASC";

$query = mysqli_query($conn, $sql);
$rows = [];

while ($row = mysqli_fetch_assoc($query)) {
    $row['sedang_dipinjam'] = ($row['sedang_dipinjam'] > 0);
    $rows[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $rows
]);
?>