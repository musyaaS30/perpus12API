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
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $type = $_GET['type'] ?? 'peminjaman'; // 'peminjaman' atau 'buku'
    
    if ($type == 'peminjaman') {
        $sql = "SELECT 
                    p.id_peminjaman,
                    s.nis,
                    s.nama_siswa,
                    s.kelas,
                    b.judul,
                    b.penulis,
                    p.tanggal_pinjam,
                    p.tanggal_kembali,
                    p.status,
                    DATEDIFF(
                        COALESCE(p.tanggal_kembali, CURDATE()),
                        p.tanggal_pinjam
                    ) as lama_pinjam
                FROM peminjaman p
                JOIN siswa s ON p.id_siswa = s.id_siswa
                JOIN buku b ON p.id_buku = b.id_buku
                WHERE p.tanggal_pinjam BETWEEN '$start_date' AND '$end_date'
                ORDER BY p.tanggal_pinjam DESC";
    } else {
        $sql = "SELECT 
                    b.judul,
                    b.penulis,
                    k.nama_kategori,
                    b.stok,
                    COUNT(p.id_peminjaman) as total_pinjam
                FROM buku b
                LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
                LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
                GROUP BY b.id_buku
                ORDER BY b.judul ASC";
    }
    
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    
    // Tambah summary untuk laporan peminjaman
    if ($type == 'peminjaman' && !empty($rows)) {
        $summary = [
            'total_peminjaman' => count($rows),
            'masih_dipinjam' => count(array_filter($rows, function($item) {
                return $item['status'] == 'dipinjam';
            })),
            'sudah_dikembalikan' => count(array_filter($rows, function($item) {
                return $item['status'] == 'dikembalikan';
            }))
        ];
        
        echo json_encode([
            "status" => true,
            "summary" => $summary,
            "data" => $rows
        ]);
    } else {
        echo json_encode($rows);
    }
}