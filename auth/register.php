<?php
include "../config.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Data tidak valid"]);
        exit();
    }
    
    // Validasi tipe
    if (!isset($data['type']) || !in_array($data['type'], ['siswa', 'guru'])) {
        echo json_encode(["status" => false, "message" => "Tipe harus 'siswa' atau 'guru'"]);
        exit();
    }
    
    $type = $data['type'];
    $password = mysqli_real_escape_string($conn, $data['password'] ?? '');
    
    if (empty($password)) {
        echo json_encode(["status" => false, "message" => "Password diperlukan"]);
        exit();
    }
    
    if ($type == 'siswa') {
        // Validasi siswa
        if (!isset($data['nis']) || !isset($data['nama_siswa'])) {
            echo json_encode(["status" => false, "message" => "NIS dan nama siswa diperlukan"]);
            exit();
        }
        
        $nis = mysqli_real_escape_string($conn, $data['nis']);
        $nama = mysqli_real_escape_string($conn, $data['nama_siswa']);
        $kelas = mysqli_real_escape_string($conn, $data['kelas'] ?? '');
        $alamat = mysqli_real_escape_string($conn, $data['alamat'] ?? '');
        
        // Cek NIS duplikat
        $check = mysqli_query($conn, "SELECT id_siswa FROM siswa WHERE nis = '$nis'");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(["status" => false, "message" => "NIS sudah terdaftar"]);
            exit();
        }
        
        // Insert ke siswa (TANPA trigger ke users)
        $sql = "INSERT INTO siswa (nis, password, nama_siswa, kelas, alamat) 
                VALUES ('$nis', '$password', '$nama', '$kelas', '$alamat')";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Registrasi siswa berhasil. Tunggu ACC pustakawan.",
                "data" => [
                    "id" => mysqli_insert_id($conn),
                    "nis" => $nis,
                    "nama" => $nama,
                    "type" => "siswa"
                ]
            ]);
        } else {
            echo json_encode(["status" => false, "message" => "Gagal: " . mysqli_error($conn)]);
        }
        
    } elseif ($type == 'guru') {
        // Validasi guru
        if (!isset($data['nip']) || !isset($data['nama_guru'])) {
            echo json_encode(["status" => false, "message" => "NIP dan nama guru diperlukan"]);
            exit();
        }
        
        $nip = mysqli_real_escape_string($conn, $data['nip']);
        $nama = mysqli_real_escape_string($conn, $data['nama_guru']);
        $no_telp = mysqli_real_escape_string($conn, $data['no_telp'] ?? '');
        $alamat = mysqli_real_escape_string($conn, $data['alamat'] ?? '');
        
        // Cek NIP duplikat
        $check = mysqli_query($conn, "SELECT id_guru FROM guru_anggota WHERE nip = '$nip'");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(["status" => false, "message" => "NIP sudah terdaftar"]);
            exit();
        }
        
        // Insert ke guru (TANPA trigger ke users)
        $sql = "INSERT INTO guru_anggota (nip, password, nama_guru, no_telp, alamat) 
                VALUES ('$nip', '$password', '$nama', '$no_telp', '$alamat')";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Registrasi guru berhasil. Tunggu ACC pustakawan.",
                "data" => [
                    "id" => mysqli_insert_id($conn),
                    "nip" => $nip,
                    "nama" => $nama,
                    "type" => "guru"
                ]
            ]);
        } else {
            echo json_encode(["status" => false, "message" => "Gagal: " . mysqli_error($conn)]);
        }
    }
}