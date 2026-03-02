<?php
// config.php - VERSI JWT (Token TIDAK disimpan di database)
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perpus_baru";

$conn = mysqli_connect($host, $username, $password, $database);

// CORS - SET ONCE
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Koneksi database gagal: " . mysqli_connect_error()
    ]);
    exit();
}

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * ❌ FUNGSI INI SUDAH TIDAK DIPERLUKAN LAGI (Token tidak disimpan di database)
 * 
 * Gunakan middleware.php dengan fungsi authenticate() dan authorize()
 * yang sudah menggunakan JWT verification
 */

/**
 * Get member ID (siswa/guru) dari nama user
 * @param string $nama
 * @param string $type 'siswa' atau 'guru'
 * @return int|null
 */
function getMemberId($nama, $type = 'siswa') {
    global $conn;
    
    if ($type == 'siswa') {
        $query = mysqli_query($conn, "SELECT id_siswa FROM siswa WHERE nama_siswa = '$nama'");
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            return $data['id_siswa'];
        }
    } else {
        $query = mysqli_query($conn, "SELECT id_guru FROM guru_anggota WHERE nama_guru = '$nama'");
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            return $data['id_guru'];
        }
    }
    
    return null;
}
?>