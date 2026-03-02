<?php
// auth/login.php - VERSI JWT (Token TIDAK disimpan di database)
require_once __DIR__ . '/../helpers/jwt_helper.php';
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode([
        "status" => false,
        "type" => "validation",
        "message" => "Email dan password wajib diisi"
    ]);
    exit;
}

function response($status, $type, $message, $extra = []) {
    echo json_encode(array_merge([
        "status" => $status,
        "type" => $type,
        "message" => $message
    ], $extra));
    exit;
}

/* ======================
   1. CEK USERS
====================== */
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    $valid = password_verify($password, $user['password']) || $password === $user['password'];

    if (!$valid) {
        response(false, "password", "Password salah");
    }

    $roleMap = [1 => "admin", 2 => "pustakawan", 3 => "siswa"];
    $roleName = $roleMap[$user['id_role']] ?? "user";

    // ✅ GENERATE JWT TOKEN (TIDAK DISIMPAN DI DATABASE)
    $jwtToken = generateJWT([
        'id' => $user['id_user'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'role' => $roleName
    ]);

    response(true, "success", "Login berhasil", [
        "token" => $jwtToken, // Token JWT
        "role" => $roleName,
        "user" => [
            "id" => $user['id_user'],
            "nama" => $user['nama'],
            "email" => $user['email']
        ]
    ]);
}

/* ======================
   2. CEK SISWA
====================== */
$stmt = $conn->prepare("SELECT * FROM siswa WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    if ($user['status'] !== 'approved') {
        response(false, "not_approved", "Akun anda belum di-ACC pustakawan");
    }

    if (!password_verify($password, $user['password'])) {
        response(false, "password", "Password salah");
    }

    // ✅ GENERATE JWT TOKEN (TIDAK DISIMPAN DI DATABASE)
    $jwtToken = generateJWT([
        'id' => $user['id_siswa'],
        'nama' => $user['nama_siswa'],
        'email' => $user['email'],
        'role' => 'siswa'
    ]);

    response(true, "success", "Login berhasil", [
        "token" => $jwtToken, // Token JWT
        "role" => "siswa",
        "user" => [
            "id" => $user['id_siswa'],
            "nama" => $user['nama_siswa'],
            "email" => $user['email']
        ]
    ]);
}

/* ======================
   EMAIL TIDAK DITEMUKAN
====================== */
response(false, "email", "Email tidak terdaftar");