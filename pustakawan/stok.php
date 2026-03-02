<?php
// pustakawan/stok.php
include "../config.php";

require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['pustakawan']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    // Get stok rendah
    $sql = "SELECT 
                b.id_buku,
                b.judul,
                b.penulis,
                b.stok,
                k.nama_kategori
            FROM buku b
            LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
            WHERE b.stok <= 5
            ORDER BY b.stok ASC";
    
    $query = mysqli_query($conn, $sql);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    
    echo json_encode([
        "status" => true,
        "data" => $rows
    ]);
}

elseif ($method == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_buku = intval($data['id_buku'] ?? 0);
    $jumlah = intval($data['jumlah'] ?? 0);
    $action = $data['action'] ?? '';
    
    if ($id_buku <= 0 || $jumlah <= 0 || !in_array($action, ['tambah', 'kurang'])) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Data tidak valid"]);
        exit();
    }
    
    if ($action == 'tambah') {
        $sql = "UPDATE buku SET stok = stok + $jumlah WHERE id_buku = $id_buku";
    } elseif ($action == 'kurang') {
        // Cek stok cukup
        $checkSql = "SELECT stok FROM buku WHERE id_buku = $id_buku";
        $checkQuery = mysqli_query($conn, $checkSql);
        
        if (mysqli_num_rows($checkQuery) == 0) {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "Buku tidak ditemukan"]);
            exit();
        }
        
        $checkResult = mysqli_fetch_assoc($checkQuery);
        
        if ($checkResult['stok'] < $jumlah) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Stok tidak mencukupi. Stok saat ini: " . $checkResult['stok']
            ]);
            exit();
        }
        
        $sql = "UPDATE buku SET stok = stok - $jumlah WHERE id_buku = $id_buku";
    }
    
    if (mysqli_query($conn, $sql)) {
        // Ambil stok terbaru
        $getStok = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = $id_buku");
        $stokData = mysqli_fetch_assoc($getStok);
        
        echo json_encode([
            "status" => true,
            "message" => "Stok berhasil diupdate",
            "data" => ["stok" => $stokData['stok']]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => false, "message" => mysqli_error($conn)]);
    }
}

else {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
}
?>