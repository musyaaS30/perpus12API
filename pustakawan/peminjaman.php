<?php
// mustakawan/peminjaman.php
include "../config.php";

require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['pustakawan']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT 
                p.id_peminjaman,
                s.id_siswa,
                s.nis,
                s.nama_siswa,
                s.kelas,
                b.id_buku,
                b.judul,
                b.penulis,
                p.tanggal_pinjam,
                p.tanggal_kembali,
                p.status,
                DATEDIFF(CURDATE(), p.tanggal_pinjam) as lama_pinjam
            FROM peminjaman p
            JOIN siswa s ON p.id_siswa = s.id_siswa
            JOIN buku b ON p.id_buku = b.id_buku
            WHERE 1=1";
    
    if (!empty($status)) {
        $sql .= " AND p.status = '$status'";
    }
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (s.nama_siswa LIKE '%$search%' OR b.judul LIKE '%$search%' OR s.nis LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY 
                CASE WHEN p.status = 'dipinjam' THEN 0 ELSE 1 END,
                p.tanggal_pinjam DESC";
    
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    
    echo json_encode([
        "status" => true,
        "data" => $rows
    ]);
}

elseif ($method == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_peminjaman = intval($data['id_peminjaman'] ?? 0);
    $action = $data['action'] ?? '';
    
    if ($id_peminjaman <= 0 || $action != 'kembalikan') {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Data tidak valid"]);
        exit();
    }
    
    mysqli_begin_transaction($conn);
    
    try {
        // Ambil data peminjaman
        $getSql = "SELECT id_buku FROM peminjaman WHERE id_peminjaman = $id_peminjaman AND status = 'dipinjam'";
        $getQuery = mysqli_query($conn, $getSql);
        
        if (mysqli_num_rows($getQuery) == 0) {
            throw new Exception("Peminjaman tidak ditemukan atau sudah dikembalikan");
        }
        
        $peminjaman = mysqli_fetch_assoc($getQuery);
        $id_buku = $peminjaman['id_buku'];
        
        // Update status peminjaman
        $tanggal_kembali = date('Y-m-d');
        $sql1 = "UPDATE peminjaman SET 
                    status = 'dikembalikan',
                    tanggal_kembali = '$tanggal_kembali'
                 WHERE id_peminjaman = $id_peminjaman";
        
        if (!mysqli_query($conn, $sql1)) {
            throw new Exception("Gagal update peminjaman: " . mysqli_error($conn));
        }
        
        // Tambah stok buku
        $sql2 = "UPDATE buku SET stok = stok + 1 WHERE id_buku = $id_buku";
        
        if (!mysqli_query($conn, $sql2)) {
            throw new Exception("Gagal update stok: " . mysqli_error($conn));
        }
        
        mysqli_commit($conn);
        
        echo json_encode([
            "status" => true,
            "message" => "Buku berhasil dikembalikan"
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(["status" => false, "message" => $e->getMessage()]);
    }
}

else {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
}
?>