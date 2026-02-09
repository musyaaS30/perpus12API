<?php
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    // Ambil token dari header
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    if (empty($token)) {
        http_response_code(401);
        echo json_encode([
            "status" => false,
            "message" => "Token diperlukan"
        ]);
        exit();
    }
    
    // Cari user berdasarkan token
    $userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 3");
    
    if (mysqli_num_rows($userQuery) == 0) {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "Akses ditolak. Hanya untuk member."
        ]);
        exit();
    }
    
    $user = mysqli_fetch_assoc($userQuery);
    
    // Cari ID siswa berdasarkan nama
    $siswaQuery = mysqli_query($conn, "SELECT id_siswa FROM siswa WHERE password = '$token'");
    
    if (mysqli_num_rows($siswaQuery) == 0) {
        echo json_encode([]);
        exit();
    }
    
    $siswa = mysqli_fetch_assoc($siswaQuery);
    $member_id = $siswa['id_siswa'];
    
    // Query untuk buku yang sedang dipinjam oleh member ini
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
                k.nama_kategori
            FROM peminjaman p
            JOIN buku b ON p.id_buku = b.id_buku
            LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
            WHERE p.id_siswa = $member_id
            AND p.status = 'dipinjam'
            ORDER BY p.tanggal_pinjam DESC";
    
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
}