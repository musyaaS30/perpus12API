<?php
// admin/aktivitas.php
include "../config.php";

require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['admin']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method != "GET") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
    exit();
}

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$limit = min($limit, 100); // Max 100

$sql = "SELECT 
            s.nama_siswa,
            b.judul,
            p.tanggal_pinjam,
            p.tanggal_kembali,
            p.status,
            CASE 
                WHEN p.status = 'dipinjam' THEN 'Meminjam'
                ELSE 'Mengembalikan'
            END as aktivitas,
            DATE(p.tanggal_pinjam) as tanggal,
            TIME(p.tanggal_pinjam) as waktu
        FROM peminjaman p
        JOIN siswa s ON p.id_siswa = s.id_siswa
        JOIN buku b ON p.id_buku = b.id_buku
        ORDER BY 
            CASE WHEN p.status = 'dipinjam' THEN p.tanggal_pinjam ELSE p.tanggal_kembali END DESC
        LIMIT $limit";

$query = mysqli_query($conn, $sql);
$rows = [];

while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $rows
]);
?>