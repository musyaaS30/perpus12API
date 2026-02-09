<?php
include "../config.php";

// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // =========================
    // 🔍 READ (GET)
    // =========================
    case "GET":

        // GET by ID
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            $query = mysqli_query($conn, "SELECT * FROM kategori_buku WHERE id_kategori = $id");
            $data = mysqli_fetch_assoc($query);

            echo json_encode($data);
        }

        // GET ALL
        else {
            $query = mysqli_query($conn, "SELECT * FROM kategori_buku ORDER BY nama_kategori ASC");

            $rows = [];
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = $row;
            }

            echo json_encode($rows);
        }

        break;


    // =========================
    // ➕ CREATE (POST)
    // =========================
    case "POST":

        $data = json_decode(file_get_contents("php://input"), true);
        $nama = $data['nama_kategori'];

        $sql = "INSERT INTO kategori_buku (nama_kategori) VALUES ('$nama')";

        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Kategori berhasil ditambahkan"
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => mysqli_error($conn)
            ]);
        }

        break;


    // =========================
    // ✏️ UPDATE (PUT)
    // =========================
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
            echo json_encode([
                "status" => false,
                "message" => mysqli_error($conn)
            ]);
        }

        break;


    // =========================
    // ❌ DELETE
    // =========================
    case "DELETE":

        parse_str($_SERVER['QUERY_STRING'], $query);
        $id = intval($query['id']);

        $sql = "DELETE FROM kategori_buku WHERE id_kategori = $id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Kategori berhasil dihapus"
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => mysqli_error($conn)
            ]);
        }

        break;
}
