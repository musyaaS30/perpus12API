<?php
// mustakawan/kategori.php
include "../config.php";

// Hanya pustakawan
require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['pustakawan']);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $query = mysqli_query($conn, "SELECT * FROM kategori_buku WHERE id_kategori = $id");
            
            if (mysqli_num_rows($query) == 0) {
                http_response_code(404);
                echo json_encode(["status" => false, "message" => "Kategori tidak ditemukan"]);
                exit();
            }
            
            $data = mysqli_fetch_assoc($query);
            echo json_encode(["status" => true, "data" => $data]);
        } else {
            $query = mysqli_query($conn, "SELECT * FROM kategori_buku ORDER BY nama_kategori ASC");
            $rows = [];
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = $row;
            }
            echo json_encode(["status" => true, "data" => $rows]);
        }
        break;
        
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        $nama = mysqli_real_escape_string($conn, $data['nama_kategori'] ?? '');
        
        if (empty($nama)) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Nama kategori wajib diisi"]);
            exit();
        }
        
        $sql = "INSERT INTO kategori_buku (nama_kategori) VALUES ('$nama')";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Kategori berhasil ditambahkan",
                "data" => ["id_kategori" => mysqli_insert_id($conn), "nama_kategori" => $nama]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => mysqli_error($conn)]);
        }
        break;
        
    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id_kategori']) || !isset($data['nama_kategori'])) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "ID dan nama kategori diperlukan"]);
            exit();
        }
        
        $id = intval($data['id_kategori']);
        $nama = mysqli_real_escape_string($conn, $data['nama_kategori']);
        
        $sql = "UPDATE kategori_buku SET nama_kategori = '$nama' WHERE id_kategori = $id";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Kategori berhasil diupdate"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => mysqli_error($conn)]);
        }
        break;
        
    case "DELETE":
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "ID tidak valid"]);
            exit();
        }
        
        // Cek apakah kategori digunakan
        $checkSql = "SELECT COUNT(*) as jumlah FROM buku WHERE id_kategori = $id";
        $checkQuery = mysqli_query($conn, $checkSql);
        $checkResult = mysqli_fetch_assoc($checkQuery);
        
        if ($checkResult['jumlah'] > 0) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Kategori tidak dapat dihapus karena masih digunakan oleh " . $checkResult['jumlah'] . " buku"
            ]);
            exit();
        }
        
        $sql = "DELETE FROM kategori_buku WHERE id_kategori = $id";
        
        if (mysqli_query($conn, $sql)) {
            if (mysqli_affected_rows($conn) > 0) {
                echo json_encode(["status" => true, "message" => "Kategori berhasil dihapus"]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => false, "message" => "Kategori tidak ditemukan"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => mysqli_error($conn)]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
        break;
}
?>