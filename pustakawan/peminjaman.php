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

// Verifikasi role pustakawan
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 2");
if (mysqli_num_rows($userQuery) == 0) {
    http_response_code(403);
    echo json_encode(["status" => false, "message" => "Akses ditolak"]);
    exit();
}

if ($method == "GET") {
    $status = $_GET['status'] ?? ''; // 'dipinjam' atau 'dikembalikan'
    
    $sql = "SELECT 
                p.id_peminjaman,
                s.nis,
                s.nama_siswa,
                s.kelas,
                b.judul,
                b.penulis,
                p.tanggal_pinjam,
                p.tanggal_kembali,
                p.status
            FROM peminjaman p
            JOIN siswa s ON p.id_siswa = s.id_siswa
            JOIN buku b ON p.id_buku = b.id_buku";
    
    if (!empty($status)) {
        $sql .= " WHERE p.status = '$status'";
    }
    
    $sql .= " ORDER BY p.tanggal_pinjam DESC";
    
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    
    echo json_encode($rows);
}

if ($method == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_peminjaman = intval($data['id_peminjaman']);
    $action = $data['action']; // 'kembalikan'
    
    if ($action == 'kembalikan') {
        // Mulai transaksi
        mysqli_begin_transaction($conn);
        
        try {
            // Ambil data peminjaman
            $getSql = "SELECT id_buku FROM peminjaman WHERE id_peminjaman = $id_peminjaman";
            $getQuery = mysqli_query($conn, $getSql);
            $peminjaman = mysqli_fetch_assoc($getQuery);
            $id_buku = $peminjaman['id_buku'];
            
            // Update status peminjaman
            $tanggal_kembali = date('Y-m-d');
            $sql1 = "UPDATE peminjaman SET 
                        status = 'dikembalikan',
                        tanggal_kembali = '$tanggal_kembali'
                     WHERE id_peminjaman = $id_peminjaman";
            mysqli_query($conn, $sql1);
            
            // Tambah stok buku
            $sql2 = "UPDATE buku SET stok = stok + 1 WHERE id_buku = $id_buku";
            mysqli_query($conn, $sql2);
            
            mysqli_commit($conn);
            
            echo json_encode([
                "status" => true,
                "message" => "Buku berhasil dikembalikan"
            ]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => "Gagal mengembalikan buku: " . $e->getMessage()
            ]);
        }
    }
}