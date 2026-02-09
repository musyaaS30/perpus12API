<?php
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $username = mysqli_real_escape_string($conn, $data['username'] ?? '');
    $password = mysqli_real_escape_string($conn, $data['password'] ?? '');
    
    // Cek di semua tabel berdasarkan role
    $user = null;
    $role = null;
    $userData = null;
    
    // Cek Admin
    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username' AND password = '$password'");
    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        $role = 1;
        $userData = [
            'id' => $user['id_admin'],
            'nama' => $user['nama_admin'],
            'email' => $user['email']
        ];
    }
    
    // Cek Pustakawan
    if (!$user) {
        $query = mysqli_query($conn, "SELECT * FROM pustakawan WHERE nama_pustakawan = '$username' AND password = '$password'");
        if (mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            $role = 2;
            $userData = [
                'id' => $user['id_pustakawan'],
                'nama' => $user['nama_pustakawan'],
                'no_telp' => $user['no_telp'],
                'alamat' => $user['alamat']
            ];
        }
    }
    
    // Cek Siswa
    if (!$user) {
        $query = mysqli_query($conn, "SELECT * FROM siswa WHERE nis = '$username' AND password = '$password'");
        if (mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            $role = 3;
            $userData = [
                'id' => $user['id_siswa'],
                'nis' => $user['nis'],
                'nama' => $user['nama_siswa'],
                'kelas' => $user['kelas'],
                'alamat' => $user['alamat']
            ];
        }
    }
    
    // Cek Guru
    if (!$user) {
        $query = mysqli_query($conn, "SELECT * FROM guru_anggota WHERE nip = '$username' AND password = '$password'");
        if (mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            $role = 3;
            $userData = [
                'id' => $user['id_guru'],
                'nip' => $user['nip'],
                'nama' => $user['nama_guru'],
                'no_telp' => $user['no_telp'],
                'alamat' => $user['alamat']
            ];
        }
    }
    
    if ($user) {
        // Generate token (simplified)
        $token = $password; // Using password as token for simplicity
        
        echo json_encode([
            "status" => true,
            "message" => "Login berhasil",
            "data" => [
                "token" => $token,
                "role" => $role,
                "user" => $userData
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => false,
            "message" => "Username atau password salah"
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
}