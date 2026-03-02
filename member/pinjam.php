<?php
// member/pinjam.php
// require_once '../middleware.php';
include "../config.php";

// Verifikasi member
require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['member']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method != "POST") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id_buku = intval($data['id_buku'] ?? 0);

if ($id_buku <= 0) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "ID buku tidak valid"]);
    exit();
}

// Cek apakah siswa (guru belum support peminjaman)
$siswaQuery = mysqli_query($conn, "SELECT id_siswa FROM siswa WHERE nama_siswa = '{$user['nama']}' AND status = 'approved'");

if (mysqli_num_rows($siswaQuery) == 0) {
    http_response_code(403);
    echo json_encode([
        "status" => false,
        "message" => "Hanya siswa yang dapat meminjam buku"
    ]);
    exit();
}

$siswa = mysqli_fetch_assoc($siswaQuery);
$id_siswa = $siswa['id_siswa'];

// Cek stok
$stokQuery = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = $id_buku");
if (mysqli_num_rows($stokQuery) == 0) {
    http_response_code(404);
    echo json_encode(["status" => false, "message" => "Buku tidak ditemukan"]);
    exit();
}

$stokData = mysqli_fetch_assoc($stokQuery);

if ($stokData['stok'] <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Stok buku habis"
    ]);
    exit();
}

// Cek apakah sudah meminjam buku yang sama
$checkPinjam = mysqli_query($conn, "SELECT id_peminjaman FROM peminjaman 
                                    WHERE id_siswa = $id_siswa 
                                    AND id_buku = $id_buku 
                                    AND status = 'dipinjam'");

if (mysqli_num_rows($checkPinjam) > 0) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Anda sedang meminjam buku ini"
    ]);
    exit();
}

// Cek batas peminjaman (maks 3 buku)
$countPinjam = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman 
                                   WHERE id_siswa = $id_siswa AND status = 'dipinjam'");
$countData = mysqli_fetch_assoc($countPinjam);

if ($countData['total'] >= 3) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Batas peminjaman maksimal 3 buku"
    ]);
    exit();
}

$tanggal_pinjam = date('Y-m-d');

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Insert peminjaman
    $sql1 = "INSERT INTO peminjaman (id_siswa, id_buku, tanggal_pinjam, status) 
             VALUES ($id_siswa, $id_buku, '$tanggal_pinjam', 'dipinjam')";
    
    if (!mysqli_query($conn, $sql1)) {
        throw new Exception("Gagal insert peminjaman: " . mysqli_error($conn));
    }
    
    // Kurangi stok
    $sql2 = "UPDATE buku SET stok = stok - 1 WHERE id_buku = $id_buku AND stok > 0";
    
    if (!mysqli_query($conn, $sql2)) {
        throw new Exception("Gagal update stok: " . mysqli_error($conn));
    }
    
    if (mysqli_affected_rows($conn) == 0) {
        throw new Exception("Stok buku habis");
    }
    
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
        "message" => $e->getMessage()
    ]);
}
?>