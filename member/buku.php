<?php
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    // Ambil token dari header
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    $member_id = null;
    
    // Jika ada token, cari ID member
    if (!empty($token)) {
        // Cari user berdasarkan token
        $userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 3");
        
        if (mysqli_num_rows($userQuery) > 0) {
            $user = mysqli_fetch_assoc($userQuery);
            
            // Cari ID siswa berdasarkan nama
            $siswaQuery = mysqli_query($conn, "SELECT id_siswa FROM siswa WHERE password = '$token'");
            if (mysqli_num_rows($siswaQuery) > 0) {
                $siswa = mysqli_fetch_assoc($siswaQuery);
                $member_id = $siswa['id_siswa'];
            }
        }
    }
    
    $search = $_GET['search'] ?? '';
    $kategori = $_GET['kategori'] ?? '';
    
    // Query dasar
    $sql = "SELECT 
                b.id_buku,
                b.judul,
                b.image_buku,
                b.penulis,
                b.penerbit,
                b.tahun_terbit,
                b.stok,
                k.nama_kategori";
    
    // Tambahkan field untuk mengecek apakah buku sedang dipinjam oleh user ini
    if ($member_id) {
        $sql .= ", 
                (SELECT COUNT(*) FROM peminjaman p 
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
        $sql .= " AND (b.judul LIKE '%$search%' OR b.penulis LIKE '%$search%')";
    }
    
    if (!empty($kategori) && is_numeric($kategori)) {
        $sql .= " AND b.id_kategori = $kategori";
    }
    
    $sql .= " ORDER BY b.judul ASC";
    
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        // Konversi sedang_dipinjam ke boolean
        $row['sedang_dipinjam'] = ($row['sedang_dipinjam'] > 0);
        $rows[] = $row;
    }
    
    echo json_encode($rows);
}