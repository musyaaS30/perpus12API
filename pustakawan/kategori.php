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

// Verifikasi role pustakawan
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token' AND id_role = 2");
if (mysqli_num_rows($userQuery) == 0) {
    http_response_code(403);
    echo json_encode(["status" => false, "message" => "Akses ditolak"]);
    exit();
}

switch ($method) {
    case "GET":
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $query = mysqli_query($conn, "SELECT * FROM kategori_buku WHERE id_kategori = $id");
            $data = mysqli_fetch_assoc($query);
            echo json_encode($data);
        } else {
            $query = mysqli_query($conn, "SELECT * FROM kategori_buku ORDER BY nama_kategori ASC");
            $rows = [];
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        }
        break;
        
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        $nama = mysqli_real_escape_string($conn, $data['nama_kategori']);
        
        $sql = "INSERT INTO kategori_buku (nama_kategori) VALUES ('$nama')";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Kategori berhasil ditambahkan"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => mysqli_error($conn)
            ]);
        }
        break;
        
    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        
        $sql = "UPDATE kategori_buku SET
                    nama_kategori = '{$data['nama_kategori']}'
                WHERE id_kategori = '{$data['id_kategori']}'";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Kategori berhasil diupdate"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => mysqli_error($conn)
            ]);
        }
        break;
        
    case "DELETE":
        parse_str($_SERVER['QUERY_STRING'], $query);
        $id = intval($query['id']);
        
        // Cek apakah kategori digunakan
        $checkSql = "SELECT COUNT(*) as jumlah FROM buku WHERE id_kategori = $id";
        $checkQuery = mysqli_query($conn, $checkSql);
        $checkResult = mysqli_fetch_assoc($checkQuery);
        
        if ($checkResult['jumlah'] > 0) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Kategori tidak dapat dihapus karena masih digunakan"
            ]);
            exit();
        }
        
        $sql = "DELETE FROM kategori_buku WHERE id_kategori = $id";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Kategori berhasil dihapus"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => mysqli_error($conn)
            ]);
        }
        break;
}