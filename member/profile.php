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

// Verifikasi token
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token'");
if (mysqli_num_rows($userQuery) == 0) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Token tidak valid"]);
    exit();
}

$user = mysqli_fetch_assoc($userQuery);

if ($method == "GET") {
    // Ambil detail berdasarkan role
    if ($user['id_role'] == 3) { // Member
        // Cek apakah siswa atau guru
        $siswaQuery = mysqli_query($conn, "SELECT * FROM siswa WHERE nama_siswa = '{$user['nama']}'");
        $guruQuery = mysqli_query($conn, "SELECT * FROM guru_anggota WHERE nama_guru = '{$user['nama']}'");
        
        if (mysqli_num_rows($siswaQuery) > 0) {
            $profile = mysqli_fetch_assoc($siswaQuery);
            $profile['type'] = 'siswa';
        } elseif (mysqli_num_rows($guruQuery) > 0) {
            $profile = mysqli_fetch_assoc($guruQuery);
            $profile['type'] = 'guru';
        }
        
        echo json_encode([
            "status" => true,
            "data" => $profile
        ]);
    }
}