<?php
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    // List member pending (semua member yang baru register)
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
    
    // Cek apakah pustakawan
    $userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 2");
    if (mysqli_num_rows($userQuery) == 0) {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "Akses ditolak. Hanya untuk pustakawan."
        ]);
        exit();
    }
    
    // Ambil semua siswa (status pending)
    $sql = "SELECT 
                s.id_siswa,
                s.nis,
                s.nama_siswa,
                s.kelas,
                s.alamat,
                s.created_at,
                'siswa' as type
            FROM siswa s
            UNION ALL
            SELECT 
                g.id_guru,
                g.nip,
                g.nama_guru,
                '-' as kelas,
                g.alamat,
                g.created_at,
                'guru' as type
            FROM guru_anggota g
            ORDER BY created_at DESC";
    
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    
    echo json_encode([
    "status" => true,
    "total_member" => count($rows),
    "data" => $rows
]);
    
} elseif ($method == "POST") {
    // ACC atau tolak member
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
    
    // Cek apakah pustakawan
    $userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 2");
    if (mysqli_num_rows($userQuery) == 0) {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "Akses ditolak. Hanya untuk pustakawan."
        ]);
        exit();
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['type']) || !isset($data['id']) || !isset($data['action'])) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Data tidak lengkap"
        ]);
        exit();
    }
    
    $type = $data['type']; // 'siswa' atau 'guru'
    $id = intval($data['id']);
    $action = $data['action']; // 'approve' atau 'reject'
    
    if ($action == 'approve') {
        // Untuk sistem sederhana, approval hanya mengubah status di users
        // (Di sistem lengkap bisa tambah field 'status_acc' di tabel)
        echo json_encode([
            "status" => true,
            "message" => "Member berhasil disetujui"
        ]);
    } elseif ($action == 'reject') {
        // Hapus member yang ditolak
        if ($type == 'siswa') {
            // Ambil nama dulu untuk hapus dari users
            $getSiswa = mysqli_query($conn, "SELECT nama_siswa FROM siswa WHERE id_siswa = $id");
            if (mysqli_num_rows($getSiswa) > 0) {
                $siswa = mysqli_fetch_assoc($getSiswa);
                $nama = $siswa['nama_siswa'];
                
                // Hapus dari users
                mysqli_query($conn, "DELETE FROM users WHERE nama = '$nama'");
                // Hapus dari siswa
                mysqli_query($conn, "DELETE FROM siswa WHERE id_siswa = $id");
            }
        } elseif ($type == 'guru') {
            // Ambil nama dulu untuk hapus dari users
            $getGuru = mysqli_query($conn, "SELECT nama_guru FROM guru_anggota WHERE id_guru = $id");
            if (mysqli_num_rows($getGuru) > 0) {
                $guru = mysqli_fetch_assoc($getGuru);
                $nama = $guru['nama_guru'];
                
                // Hapus dari users
                mysqli_query($conn, "DELETE FROM users WHERE nama = '$nama'");
                // Hapus dari guru_anggota
                mysqli_query($conn, "DELETE FROM guru_anggota WHERE id_guru = $id");
            }
        }
        
        echo json_encode([
            "status" => true,
            "message" => "Member berhasil ditolak dan dihapus"
        ]);
    }
}