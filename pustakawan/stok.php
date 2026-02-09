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
    
    echo json_encode($rows);
}

if ($method == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_buku = intval($data['id_buku']);
    $jumlah = intval($data['jumlah']);
    $action = $data['action']; // 'tambah' atau 'kurang'
    
    if ($action == 'tambah') {
        $sql = "UPDATE buku SET stok = stok + $jumlah WHERE id_buku = $id_buku";
    } elseif ($action == 'kurang') {
        // Cek stok cukup
        $checkSql = "SELECT stok FROM buku WHERE id_buku = $id_buku";
        $checkQuery = mysqli_query($conn, $checkSql);
        $checkResult = mysqli_fetch_assoc($checkQuery);
        
        if ($checkResult['stok'] < $jumlah) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Stok tidak mencukupi"
            ]);
            exit();
        }
        
        $sql = "UPDATE buku SET stok = stok - $jumlah WHERE id_buku = $id_buku";
    }
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "status" => true,
            "message" => "Stok berhasil diupdate"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => mysqli_error($conn)
        ]);
    }
}