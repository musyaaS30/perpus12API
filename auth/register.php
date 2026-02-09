<?php
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $type = $data['type']; // 'siswa' atau 'guru'
    $password = mysqli_real_escape_string($conn, $data['password']);
    
    if ($type == 'siswa') {
        $nis = mysqli_real_escape_string($conn, $data['nis']);
        $nama = mysqli_real_escape_string($conn, $data['nama_siswa']);
        $kelas = mysqli_real_escape_string($conn, $data['kelas'] ?? '');
        $alamat = mysqli_real_escape_string($conn, $data['alamat'] ?? '');
        
        // Cek NIS sudah ada
        $check = mysqli_query($conn, "SELECT * FROM siswa WHERE nis = '$nis'");
        if (mysqli_num_rows($check) > 0) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "NIS sudah terdaftar"
            ]);
            exit();
        }
        
        $sql = "INSERT INTO siswa (nis, password, nama_siswa, kelas, alamat) 
                VALUES ('$nis', '$password', '$nama', '$kelas', '$alamat')";
        
    } elseif ($type == 'guru') {
        $nip = mysqli_real_escape_string($conn, $data['nip']);
        $nama = mysqli_real_escape_string($conn, $data['nama_guru']);
        $no_telp = mysqli_real_escape_string($conn, $data['no_telp'] ?? '');
        $alamat = mysqli_real_escape_string($conn, $data['alamat'] ?? '');
        
        // Cek NIP sudah ada
        $check = mysqli_query($conn, "SELECT * FROM guru_anggota WHERE nip = '$nip'");
        if (mysqli_num_rows($check) > 0) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "NIP sudah terdaftar"
            ]);
            exit();
        }
        
        $sql = "INSERT INTO guru_anggota (nip, password, nama_guru, no_telp, alamat) 
                VALUES ('$nip', '$password', '$nama', '$no_telp', '$alamat')";
    } else {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Tipe pendaftaran tidak valid"]);
        exit();
    }
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "status" => true,
            "message" => "Registrasi berhasil. Menunggu persetujuan pustakawan."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Registrasi gagal: " . mysqli_error($conn)
        ]);
    }
}