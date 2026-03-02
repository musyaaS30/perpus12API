<?php
// shared/buku.php - PUBLIC READ ONLY
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

// Hanya izinkan GET
if ($method != "GET") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
    exit();
}

// GET by ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT 
                b.id_buku,
                b.judul,
                b.image_buku,
                b.deskripsi,
                b.penulis,
                b.penerbit,
                b.tahun_terbit,
                b.halaman,
                b.stok,
                k.id_kategori,
                k.nama_kategori
            FROM buku b
            LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
            WHERE b.id_buku = $id";
    
    $query = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($query) == 0) {
        http_response_code(404);
        echo json_encode([
            "status" => false,
            "message" => "Buku tidak ditemukan"
        ]);
        exit();
    }
    
    $data = mysqli_fetch_assoc($query);
    
    echo json_encode([
        "status" => true,
        "data" => $data
    ]);
} 
// GET ALL
else {
    $search = $_GET['search'] ?? '';
    $kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
    
    $sql = "SELECT 
                b.id_buku,
                b.judul,
                b.image_buku,
                b.deskripsi,
                b.penulis,
                b.penerbit,
                b.tahun_terbit,
                b.halaman,
                b.stok,
                k.nama_kategori
            FROM buku b
            LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
            WHERE 1=1";
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (b.judul LIKE '%$search%' OR b.penulis LIKE '%$search%')";
    }
    
    if ($kategori > 0) {
        $sql .= " AND b.id_kategori = $kategori";
    }
    
    $sql .= " ORDER BY b.judul ASC";
    
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
?>