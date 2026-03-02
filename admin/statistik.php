<?php
// admin/statistik.php
include "../config.php";

// Hanya admin
$user = requireAuth(1);

$method = $_SERVER['REQUEST_METHOD'];

if ($method != "GET") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
    exit();
}

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
    'stok_rendah' => "SELECT COUNT(*) as total FROM buku WHERE stok <= 5",
    'pending_siswa' => "SELECT COUNT(*) as total FROM siswa WHERE status = 'pending'",
    'pending_guru' => "SELECT COUNT(*) as total FROM guru_anggota WHERE status = 'pending'"
];

foreach ($queries as $key => $query) {
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    $statistik[$key] = intval($data['total']);
}

// Statistik peminjaman 7 hari terakhir
$hariSql = "SELECT 
                DATE(tanggal_pinjam) as tanggal,
                COUNT(*) as total
            FROM peminjaman 
            WHERE tanggal_pinjam >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(tanggal_pinjam)
            ORDER BY tanggal";

$hariQuery = mysqli_query($conn, $hariSql);
$statistik['peminjaman_harian'] = [];

while ($row = mysqli_fetch_assoc($hariQuery)) {
    $statistik['peminjaman_harian'][] = $row;
}

// Statistik bulanan
$bulanSql = "SELECT 
                MONTH(tanggal_pinjam) as bulan,
                COUNT(*) as total
            FROM peminjaman 
            WHERE YEAR(tanggal_pinjam) = YEAR(CURDATE())
            GROUP BY MONTH(tanggal_pinjam)
            ORDER BY bulan";

$bulanQuery = mysqli_query($conn, $bulanSql);
$statistik['peminjaman_bulanan'] = [];

while ($row = mysqli_fetch_assoc($bulanQuery)) {
    $statistik['peminjaman_bulanan'][] = $row;
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

// Member aktif
$memberAktifSql = "SELECT 
                        s.nama_siswa,
                        s.nis,
                        s.kelas,
                        COUNT(p.id_peminjaman) as total_pinjam
                    FROM siswa s
                    JOIN peminjaman p ON s.id_siswa = p.id_siswa
                    WHERE p.tanggal_pinjam >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY s.id_siswa
                    ORDER BY total_pinjam DESC
                    LIMIT 5";

$memberAktifQuery = mysqli_query($conn, $memberAktifSql);
$statistik['member_aktif'] = [];

while ($row = mysqli_fetch_assoc($memberAktifQuery)) {
    $statistik['member_aktif'][] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $statistik
]);
?>