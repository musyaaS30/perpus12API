<?php
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if (empty($token)) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Token diperlukan"]);
    exit();
}

// Verifikasi role admin
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 1");
if (mysqli_num_rows($userQuery) == 0) {
    http_response_code(403);
    echo json_encode(["status" => false, "message" => "Akses ditolak"]);
    exit();
}

if ($method == "GET") {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    $sql = "SELECT 
                s.nama_siswa,
                b.judul,
                p.tanggal_pinjam,
                p.tanggal_kembali,
                p.status,
                CASE 
                    WHEN p.status = 'dipinjam' THEN 'Meminjam'
                    ELSE 'Mengembalikan'
                END as aktivitas
            FROM peminjaman p
            JOIN siswa s ON p.id_siswa = s.id_siswa
            JOIN buku b ON p.id_buku = b.id_buku
            ORDER BY 
                CASE WHEN p.tanggal_kembali IS NULL THEN p.tanggal_pinjam ELSE p.tanggal_kembali END DESC
            LIMIT $limit";
    
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    
    echo json_encode($rows);
}