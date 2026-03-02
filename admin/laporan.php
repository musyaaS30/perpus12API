<?php
// admin/laporan.php
include "../config.php";

// Hanya admin
$user = requireAuth(1);

$method = $_SERVER['REQUEST_METHOD'];

if ($method != "GET") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
    exit();
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$type = $_GET['type'] ?? 'peminjaman';

// Validasi tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Format tanggal tidak valid. Gunakan YYYY-MM-DD"]);
    exit();
}

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
                DATEDIFF(COALESCE(p.tanggal_kembali, CURDATE()), p.tanggal_pinjam) as lama_pinjam
            FROM peminjaman p
            JOIN siswa s ON p.id_siswa = s.id_siswa
            JOIN buku b ON p.id_buku = b.id_buku
            WHERE p.tanggal_pinjam BETWEEN '$start_date' AND '$end_date'
            ORDER BY p.tanggal_pinjam DESC";
            
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    
    // Summary
    $summary = [
        'total_peminjaman' => count($rows),
        'masih_dipinjam' => 0,
        'sudah_dikembalikan' => 0,
        'total_buku_dipinjam' => 0,
        'member_aktif' => 0
    ];
    
    $members = [];
    
    foreach ($rows as $row) {
        if ($row['status'] == 'dipinjam') {
            $summary['masih_dipinjam']++;
            $summary['total_buku_dipinjam']++;
        } else {
            $summary['sudah_dikembalikan']++;
        }
        
        $members[$row['nis']] = true;
    }
    
    $summary['member_aktif'] = count($members);
    
    echo json_encode([
        "status" => true,
        "periode" => [
            "start_date" => $start_date,
            "end_date" => $end_date
        ],
        "summary" => $summary,
        "data" => $rows
    ]);
    
} elseif ($type == 'buku') {
    $sql = "SELECT 
                b.judul,
                b.penulis,
                k.nama_kategori,
                b.stok,
                COUNT(p.id_peminjaman) as total_pinjam,
                COUNT(CASE WHEN p.status = 'dipinjam' THEN 1 END) as sedang_dipinjam
            FROM buku b
            LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
            LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
            GROUP BY b.id_buku
            ORDER BY total_pinjam DESC";
    
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    
    echo json_encode([
        "status" => true,
        "data" => $rows
    ]);
    
} else {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Tipe laporan tidak valid. Gunakan 'peminjaman' atau 'buku'"]);
}
?>