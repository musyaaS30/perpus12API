<?php
// Verifikasi member
require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['member']);
include "../config.php";

header("Content-Type: application/json");

$user = authenticate(); // hasil decode JWT
$method = $_SERVER['REQUEST_METHOD'];

$userId = (int) $user->id;   // ini adalah id_user dari tabel users
$role   = $user->role;

// =======================================================
// ======================= GET ===========================
// =======================================================
if ($method === "GET") {

    // ================== SISWA ==================
    if ($role === "siswa") {

        $stmt = $conn->prepare("
            SELECT s.*, u.nama, u.email 
            FROM siswa s
            JOIN users u ON s.user_id = u.id_user
            WHERE s.user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "Data siswa tidak ditemukan"
            ]);
            exit();
        }

        $profile = $result->fetch_assoc();
        unset($profile['password']);

        echo json_encode([
            "status" => true,
            "data" => $profile
        ]);
    }

    // ================== GURU ==================
    elseif ($role === "guru") {

        $stmt = $conn->prepare("
            SELECT g.*, u.nama, u.email
            FROM guru_anggota g
            JOIN users u ON g.user_id = u.id_user
            WHERE g.user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "Data guru tidak ditemukan"
            ]);
            exit();
        }

        $profile = $result->fetch_assoc();
        unset($profile['password']);

        echo json_encode([
            "status" => true,
            "data" => $profile
        ]);
    }

    // ================== PUSTAKAWAN ==================
    elseif ($role === "pustakawan") {

        $stmt = $conn->prepare("
            SELECT * FROM pustakawan
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "Data pustakawan tidak ditemukan"
            ]);
            exit();
        }

        $profile = $result->fetch_assoc();
        unset($profile['password']);

        echo json_encode([
            "status" => true,
            "data" => $profile
        ]);
    }

    // ================== ADMIN ==================
    elseif ($role === "admin") {

        $stmt = $conn->prepare("
            SELECT * FROM admin
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "Data admin tidak ditemukan"
            ]);
            exit();
        }

        $profile = $result->fetch_assoc();
        unset($profile['password']);

        echo json_encode([
            "status" => true,
            "data" => $profile
        ]);
    }

    else {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "Role tidak dikenali"
        ]);
    }
}


// =======================================================
// ======================= PUT ===========================
// =======================================================
elseif ($method === "PUT") {

    $data = json_decode(file_get_contents("php://input"), true);

    $no_telp = $data['no_telp'] ?? "";
    $alamat  = $data['alamat'] ?? "";

    if ($role === "siswa") {

        $stmt = $conn->prepare("
            UPDATE siswa 
            SET no_telp = ?, alamat = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param("ssi", $no_telp, $alamat, $userId);

    } 

    elseif ($role === "guru") {

        $stmt = $conn->prepare("
            UPDATE guru_anggota 
            SET no_telp = ?, alamat = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param("ssi", $no_telp, $alamat, $userId);

    } 

    else {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "Update hanya untuk siswa/guru"
        ]);
        exit();
    }

    if ($stmt->execute()) {
        echo json_encode([
            "status" => true,
            "message" => "Profil berhasil diperbarui"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Gagal memperbarui profil"
        ]);
    }
}

else {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method tidak diizinkan"
    ]);
}