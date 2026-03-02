<?php
// auth/register.php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

// Validasi input
$type = $data['type'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$nama = $data['type'] == 'siswa' ? ($data['nama_siswa'] ?? '') : ($data['nama_guru'] ?? '');
$nis = $data['nis'] ?? '';
$nip = $data['nip'] ?? '';
$kelas = $data['kelas'] ?? '';
$alamat = $data['alamat'] ?? '';
$no_telp = $data['no_telp'] ?? '';

if (!$type || !$email || !$password || !$nama) {
    echo json_encode([
        "status" => false, 
        "message" => "Data tidak lengkap. Email, password, dan nama wajib diisi."
    ]);
    exit;
}

// Validasi type
if (!in_array($type, ['siswa', 'guru'])) {
    echo json_encode([
        "status" => false,
        "message" => "Tipe harus 'siswa' atau 'guru'"
    ]);
    exit;
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

$plainPassword = $password;

// Cek email sudah terdaftar
if ($type == 'siswa') {
    $check = mysqli_query($conn, "SELECT id_siswa FROM siswa WHERE email = '$email'");
} else {
    $check = mysqli_query($conn, "SELECT id_guru FROM guru_anggota WHERE email = '$email'");
}

if (mysqli_num_rows($check) > 0) {
    echo json_encode([
        "status" => false,
        "message" => "Email sudah terdaftar"
    ]);
    exit;
}

// Insert ke tabel sesuai role
if ($type == "siswa") {
    if (empty($nis)) {
        echo json_encode(["status" => false, "message" => "NIS wajib diisi"]);
        exit;
    }
    
    $sql = "INSERT INTO siswa (nis, nama_siswa, email, password, kelas, alamat, no_telp, status) 
            VALUES ('$nis', '$nama', '$email', '$plainPassword', '$kelas', '$alamat', '$no_telp', 'pending')";
} else {
    if (empty($nip)) {
        echo json_encode(["status" => false, "message" => "NIP wajib diisi"]);
        exit;
    }
    
    $sql = "INSERT INTO guru_anggota (nip, nama_guru, email, password, alamat, no_telp, status) 
            VALUES ('$nip', '$nama', '$email', '$plainPassword', '$alamat', '$no_telp', 'pending')";
}

if (mysqli_query($conn, $sql)) {
    echo json_encode([
        "status" => true,
        "message" => "Registrasi berhasil. Silakan tunggu persetujuan pustakawan."
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Gagal registrasi: " . mysqli_error($conn)
    ]);
}
?>