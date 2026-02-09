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

// Verifikasi token dan ambil user
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 3");
if (mysqli_num_rows($userQuery) == 0) {
    http_response_code(403);
    echo json_encode(["status" => false, "message" => "Akses ditolak"]);
    exit();
}

$user = mysqli_fetch_assoc($userQuery);

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_buku = intval($data['id_buku']);
    
    // Cek stok
    $stokQuery = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = $id_buku");
    $stokData = mysqli_fetch_assoc($stokQuery);
    
    if ($stokData['stok'] <= 0) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Buku tidak tersedia"
        ]);
        exit();
    }
    
    // Cek apakah siswa atau guru
    $siswaQuery = mysqli_query($conn, "SELECT id_siswa FROM siswa WHERE nama_siswa = '{$user['nama']}'");
    $guruQuery = mysqli_query($conn, "SELECT id_guru FROM guru_anggota WHERE nama_guru = '{$user['nama']}'");
    
    if (mysqli_num_rows($siswaQuery) > 0) {
        $member = mysqli_fetch_assoc($siswaQuery);
        $id_siswa = $member['id_siswa'];
    } elseif (mysqli_num_rows($guruQuery) > 0) {
        // Untuk guru, kita perlu menambahkan ke tabel siswa terlebih dahulu atau buat sistem khusus
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Fitur peminjaman untuk guru sedang dalam pengembangan"]);
        exit();
    } else {
        http_response_code(404);
        echo json_encode(["status" => false, "message" => "Data member tidak ditemukan"]);
        exit();
    }
    
    $tanggal_pinjam = date('Y-m-d');
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Insert peminjaman
        $sql1 = "INSERT INTO peminjaman (id_siswa, id_buku, tanggal_pinjam, status) 
                 VALUES ($id_siswa, $id_buku, '$tanggal_pinjam', 'dipinjam')";
        mysqli_query($conn, $sql1);
        
        // Kurangi stok
        $sql2 = "UPDATE buku SET stok = stok - 1 WHERE id_buku = $id_buku AND stok > 0";
        mysqli_query($conn, $sql2);
        
        mysqli_commit($conn);
        
        echo json_encode([
            "status" => true,
            "message" => "Buku berhasil dipinjam"
        ]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Gagal meminjam buku: " . $e->getMessage()
        ]);
    }
}