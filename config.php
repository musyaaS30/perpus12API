<?php
// Konfigurasi Database
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Preflight request handler
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

date_default_timezone_set('Asia/Jakarta');

// Koneksi Database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perpus_baru";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Koneksi database gagal: " . mysqli_connect_error()
    ]);
    exit();
}

// Fungsi Helper
function validateToken($token) {
    global $conn;
    $query = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token'");
    return mysqli_fetch_assoc($query);
}

function checkRole($user, $requiredRole) {
    return isset($user['id_role']) && $user['id_role'] == $requiredRole;
}

function getUserIdByToken($token) {
    global $conn;
    $query = mysqli_query($conn, "SELECT id_user FROM users WHERE password = '$token'");
    $result = mysqli_fetch_assoc($query);
    return $result ? $result['id_user'] : null;
}
