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
    // Statistik lengkap
    $statistik = [];
    
    // Total data
    $queries = [
        'total_siswa' => "SELECT COUNT(*) as total FROM siswa",
        'total_guru' => "SELECT COUNT(*) as total FROM guru_anggota",
        'total_pustakawan' => "SELECT COUNT(*) as total FROM pustakawan",
        'total_admin' => "SELECT COUNT(*) as total FROM admin",
        'total_buku' => "SELECT COUNT(*) as total FROM buku",
        'total_kategori' => "SELECT COUNT(*) as total FROM kategori_buku",
        'total_peminjaman' => "SELECT COUNT(*) as total FROM peminjaman",
        'peminjaman_aktif' => "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'",
        'peminjaman_selesai' => "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dikembalikan'",
        'stok_rendah' => "SELECT COUNT(*) as total FROM buku WHERE stok <= 5"
    ];
    
    foreach ($queries as $key => $query) {
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_assoc($result);
        $statistik[$key] = $data['total'];
    }
    
    // Statistik bulanan
    $bulananSql = "SELECT 
                        MONTH(tanggal_pinjam) as bulan,
                        COUNT(*) as total
                    FROM peminjaman 
                    WHERE YEAR(tanggal_pinjam) = YEAR(CURDATE())
                    GROUP BY MONTH(tanggal_pinjam)
                    ORDER BY bulan";
    
    $bulananQuery = mysqli_query($conn, $bulananSql);
    $statistik['bulanan'] = [];
    
    while ($row = mysqli_fetch_assoc($bulananQuery)) {
        $statistik['bulanan'][] = $row;
    }
    
    // Buku terpopuler
    $populerSql = "SELECT 
                        b.judul,
                        b.penulis,
                        COUNT(p.id_peminjaman) as total_pinjam
                    FROM buku b
                    LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
                    GROUP BY b.id_buku
                    ORDER BY total_pinjam DESC
                    LIMIT 5";
    
    $populerQuery = mysqli_query($conn, $populerSql);
    $statistik['buku_populer'] = [];
    
    while ($row = mysqli_fetch_assoc($populerQuery)) {
        $statistik['buku_populer'][] = $row;
    }
    
    echo json_encode([
        "status" => true,
        "data" => $statistik
    ]);
}