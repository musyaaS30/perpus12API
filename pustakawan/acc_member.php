<?php
include "../config.php";

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Fungsi auth untuk pustakawan
function requirePustakawan() {
    global $conn;
    
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(["status" => false, "message" => "Token diperlukan"]);
        exit();
    }
    
    // Cek di users apakah pustakawan
    $query = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 2");
    if (mysqli_num_rows($query) == 0) {
        http_response_code(403);
        echo json_encode(["status" => false, "message" => "Hanya pustakawan yang bisa akses"]);
        exit();
    }
    
    return mysqli_fetch_assoc($query);
}

$method = $_SERVER['REQUEST_METHOD'];

// 1. GET: LIHAT MEMBER YANG BELUM DI-ACC
if ($method == "GET") {
    requirePustakawan();
    
    // Ambil siswa yang belum di-ACC (tidak ada di users)
    $sqlSiswa = "SELECT 
                    s.id_siswa as id,
                    s.nis,
                    s.nama_siswa as nama,
                    s.kelas,
                    s.alamat,
                    s.created_at,
                    'siswa' as tipe
                 FROM siswa s
                 WHERE s.nama_siswa NOT IN (SELECT nama FROM users)
                 ORDER BY s.created_at DESC";
    
    // Ambil guru yang belum di-ACC
    $sqlGuru = "SELECT 
                    g.id_guru as id,
                    g.nip,
                    g.nama_guru as nama,
                    '-' as kelas,
                    g.alamat,
                    g.created_at,
                    'guru' as tipe
                FROM guru_anggota g
                WHERE g.nama_guru NOT IN (SELECT nama FROM users)
                ORDER BY g.created_at DESC";
    
    $siswaQuery = mysqli_query($conn, $sqlSiswa);
    $guruQuery = mysqli_query($conn, $sqlGuru);
    
    $rows = [];
    
    // Gabungkan hasil
    while ($row = mysqli_fetch_assoc($siswaQuery)) {
        $rows[] = $row;
    }
    
    while ($row = mysqli_fetch_assoc($guruQuery)) {
        $rows[] = $row;
    }
    
    echo json_encode([
        "status" => true,
        "total_pending" => count($rows),
        "data" => $rows
    ], JSON_NUMERIC_CHECK);
}

// 2. POST: ACC ATAU TOLAK MEMBER
elseif ($method == "POST") {
    requirePustakawan();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Data tidak valid"]);
        exit();
    }
    
    // Validasi input
    if (!isset($data['tipe']) || !in_array($data['tipe'], ['siswa', 'guru'])) {
        echo json_encode(["status" => false, "message" => "Tipe harus 'siswa' atau 'guru'"]);
        exit();
    }
    
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        echo json_encode(["status" => false, "message" => "ID tidak valid"]);
        exit();
    }
    
    if (!isset($data['aksi']) || !in_array($data['aksi'], ['setuju', 'tolak'])) {
        echo json_encode(["status" => false, "message" => "Aksi harus 'setuju' atau 'tolak'"]);
        exit();
    }
    
    $tipe = $data['tipe'];
    $id = intval($data['id']);
    $aksi = $data['aksi'];
    
    if ($tipe == 'siswa') {
        // Ambil data siswa
        $query = mysqli_query($conn, "SELECT * FROM siswa WHERE id_siswa = $id");
        if (mysqli_num_rows($query) == 0) {
            echo json_encode(["status" => false, "message" => "Siswa tidak ditemukan"]);
            exit();
        }
        
        $siswa = mysqli_fetch_assoc($query);
        
        if ($aksi == 'setuju') {
            // Cek apakah sudah ada di users
            $checkUser = mysqli_query($conn, "SELECT id_user FROM users WHERE nama = '{$siswa['nama_siswa']}'");
            if (mysqli_num_rows($checkUser) > 0) {
                echo json_encode(["status" => false, "message" => "Siswa sudah di-ACC sebelumnya"]);
                exit();
            }
            
            // Insert ke users
            $sql = "INSERT INTO users (id_role, nama, password) 
                    VALUES (3, '{$siswa['nama_siswa']}', '{$siswa['password']}')";
            
            if (mysqli_query($conn, $sql)) {
                echo json_encode([
                    "status" => true,
                    "message" => "Siswa berhasil di-ACC. Sekarang bisa login."
                ]);
            } else {
                echo json_encode(["status" => false, "message" => "Gagal: " . mysqli_error($conn)]);
            }
            
        } elseif ($aksi == 'tolak') {
            // Hapus dari siswa
            mysqli_query($conn, "DELETE FROM siswa WHERE id_siswa = $id");
            echo json_encode(["status" => true, "message" => "Siswa ditolak dan dihapus"]);
        }
        
    } elseif ($tipe == 'guru') {
        // Ambil data guru
        $query = mysqli_query($conn, "SELECT * FROM guru_anggota WHERE id_guru = $id");
        if (mysqli_num_rows($query) == 0) {
            echo json_encode(["status" => false, "message" => "Guru tidak ditemukan"]);
            exit();
        }
        
        $guru = mysqli_fetch_assoc($query);
        
        if ($aksi == 'setuju') {
            // Cek apakah sudah ada di users
            $checkUser = mysqli_query($conn, "SELECT id_user FROM users WHERE nama = '{$guru['nama_guru']}'");
            if (mysqli_num_rows($checkUser) > 0) {
                echo json_encode(["status" => false, "message" => "Guru sudah di-ACC sebelumnya"]);
                exit();
            }
            
            // Insert ke users
            $sql = "INSERT INTO users (id_role, nama, password) 
                    VALUES (3, '{$guru['nama_guru']}', '{$guru['password']}')";
            
            if (mysqli_query($conn, $sql)) {
                echo json_encode([
                    "status" => true,
                    "message" => "Guru berhasil di-ACC. Sekarang bisa login."
                ]);
            } else {
                echo json_encode(["status" => false, "message" => "Gagal: " . mysqli_error($conn)]);
            }
            
        } elseif ($aksi == 'tolak') {
            // Hapus dari guru_anggota
            mysqli_query($conn, "DELETE FROM guru_anggota WHERE id_guru = $id");
            echo json_encode(["status" => true, "message" => "Guru ditolak dan dihapus"]);
        }
    }
}

// 3. DELETE: HAPUS MEMBER (Opsional)
elseif ($method == "DELETE") {
    requirePustakawan();
    
    parse_str($_SERVER['QUERY_STRING'], $params);
    $tipe = $params['tipe'] ?? '';
    $id = intval($params['id'] ?? 0);
    
    if (!in_array($tipe, ['siswa', 'guru'])) {
        echo json_encode(["status" => false, "message" => "Tipe harus 'siswa' atau 'guru'"]);
        exit();
    }
    
    if ($id <= 0) {
        echo json_encode(["status" => false, "message" => "ID tidak valid"]);
        exit();
    }
    
    if ($tipe == 'siswa') {
        // Hapus dari users dulu
        $getSiswa = mysqli_query($conn, "SELECT nama_siswa FROM siswa WHERE id_siswa = $id");
        if (mysqli_num_rows($getSiswa) > 0) {
            $siswa = mysqli_fetch_assoc($getSiswa);
            mysqli_query($conn, "DELETE FROM users WHERE nama = '{$siswa['nama_siswa']}'");
        }
        
        // Hapus dari siswa
        mysqli_query($conn, "DELETE FROM siswa WHERE id_siswa = $id");
        
    } elseif ($tipe == 'guru') {
        // Hapus dari users dulu
        $getGuru = mysqli_query($conn, "SELECT nama_guru FROM guru_anggota WHERE id_guru = $id");
        if (mysqli_num_rows($getGuru) > 0) {
            $guru = mysqli_fetch_assoc($getGuru);
            mysqli_query($conn, "DELETE FROM users WHERE nama = '{$guru['nama_guru']}'");
        }
        
        // Hapus dari guru_anggota
        mysqli_query($conn, "DELETE FROM guru_anggota WHERE id_guru = $id");
    }
    
    echo json_encode(["status" => true, "message" => "Member berhasil dihapus"]);
}

else {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
}