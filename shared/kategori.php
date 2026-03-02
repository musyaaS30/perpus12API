<?php
// shared/kategori.php - PUBLIC READ ONLY
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

// Hanya izinkan GET
if ($method != "GET") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
    exit();
}

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
?>