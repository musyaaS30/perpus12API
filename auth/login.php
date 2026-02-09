<?php
include "../config.php";

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "POST") {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Data tidak valid"]);
        exit();
    }
    
    $identifier = mysqli_real_escape_string($conn, $data['identifier'] ?? '');
    $password = mysqli_real_escape_string($conn, $data['password'] ?? '');
    
    if (empty($identifier) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Identifier dan password diperlukan"]);
        exit();
    }
    
    $user = null;
    $role = null;
    $userData = null;
    
    // 1. CEK APAKAH SUDAH DI-ACC (ADA DI TABEL USERS)
    $checkUsers = mysqli_query($conn, "SELECT * FROM users WHERE password = '$password'");
    
    // Cari berdasarkan identifier di tabel users
    $userInUsers = null;
    while ($row = mysqli_fetch_assoc($checkUsers)) {
        if ($row['nama'] == $identifier || $row['password'] == $password) {
            $userInUsers = $row;
            break;
        }
    }
    
    // Jika tidak ada di users = BELUM DI-ACC
    if (!$userInUsers) {
        http_response_code(401);
        echo json_encode([
            "status" => false,
            "message" => "Akun belum di-ACC oleh pustakawan"
        ]);
        exit();
    }
    
    // 2. JIKA SUDAH DI-ACC, CEK DETAIL DI TABEL MEREKA
    $role = $userInUsers['id_role'];
    
    if ($role == 1) { // ADMIN
        $query = mysqli_query($conn, "SELECT * FROM admin WHERE password = '$password'");
        if (mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            $userData = [
                'id' => $user['id_admin'],
                'nama' => $user['nama_admin'],
                'email' => $user['email'],
                'username' => $user['username']
            ];
        }
    }
    elseif ($role == 2) { // PUSTAKAWAN
        $query = mysqli_query($conn, "SELECT * FROM pustakawan WHERE password = '$password'");
        if (mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            $userData = [
                'id' => $user['id_pustakawan'],
                'nama' => $user['nama_pustakawan'],
                'no_telp' => $user['no_telp'],
                'alamat' => $user['alamat']
            ];
        }
    }
    elseif ($role == 3) { // MEMBER (SISWA/GURU)
        // Cek siswa
        $query = mysqli_query($conn, "SELECT * FROM siswa WHERE password = '$password'");
        if (mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            $userData = [
                'id' => $user['id_siswa'],
                'nis' => $user['nis'],
                'nama' => $user['nama_siswa'],
                'kelas' => $user['kelas'],
                'alamat' => $user['alamat'],
                'type' => 'siswa'
            ];
        } 
        // Cek guru
        else {
            $query = mysqli_query($conn, "SELECT * FROM guru_anggota WHERE password = '$password'");
            if (mysqli_num_rows($query) > 0) {
                $user = mysqli_fetch_assoc($query);
                $userData = [
                    'id' => $user['id_guru'],
                    'nip' => $user['nip'],
                    'nama' => $user['nama_guru'],
                    'no_telp' => $user['no_telp'],
                    'alamat' => $user['alamat'],
                    'type' => 'guru'
                ];
            }
        }
    }
    
    if ($userData) {
        echo json_encode([
            "status" => true,
            "message" => "Login berhasil",
            "data" => [
                "token" => $password, // Password sebagai token
                "role" => $role,
                "user" => $userData
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => false,
            "message" => "Akun tidak ditemukan"
        ]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
}