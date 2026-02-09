<?php
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

// Debug: Log request method
error_log("Method: " . $method);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Fungsi untuk mendapatkan user dari token
function getAuthUser($token) {
    global $conn;
    
    if (empty($token)) {
        return null;
    }
    
    // Cari di tabel users berdasarkan password (sebagai token sederhana)
    $query = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token'");
    
    if (mysqli_num_rows($query) > 0) {
        return mysqli_fetch_assoc($query);
    }
    
    return null;
}

// Fungsi untuk require authentication
function requireAuth($requiredRole = null) {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    error_log("Token received: " . $token);
    
    $user = getAuthUser($token);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode([
            "status" => false,
            "message" => "Token tidak valid atau tidak ditemukan"
        ]);
        exit();
    }
    
    if ($requiredRole && $user['id_role'] != $requiredRole) {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "Akses ditolak. Hanya untuk role: " . $requiredRole
        ]);
        exit();
    }
    
    return $user;
}

try {
    switch ($method) {
        case "GET":
            $user = requireAuth(2); // Hanya pustakawan
            
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                
                $sql = "SELECT 
                            b.*,
                            k.nama_kategori
                        FROM buku b
                        LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
                        WHERE b.id_buku = $id";
                
                $query = mysqli_query($conn, $sql);
                
                if (!$query) {
                    throw new Exception("Query error: " . mysqli_error($conn));
                }
                
                if (mysqli_num_rows($query) > 0) {
                    $data = mysqli_fetch_assoc($query);
                    echo json_encode([
                        "status" => true,
                        "data" => $data
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "status" => false,
                        "message" => "Buku tidak ditemukan"
                    ]);
                }
            } else {
                $sql = "SELECT 
                            b.id_buku,
                            b.judul,
                            b.penulis,
                            b.penerbit,
                            b.tahun_terbit,
                            b.stok,
                            k.nama_kategori
                        FROM buku b
                        LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
                        ORDER BY b.judul ASC";
                
                $query = mysqli_query($conn, $sql);
                
                if (!$query) {
                    throw new Exception("Query error: " . mysqli_error($conn));
                }
                
                $rows = [];
                while ($row = mysqli_fetch_assoc($query)) {
                    $rows[] = $row;
                }
                
                echo json_encode([
                    "status" => true,
                    "data" => $rows
                ]);
            }
            break;
            
        case "POST":
            error_log("POST Request received");
            
            $user = requireAuth(2); // Hanya pustakawan
            
            // Dapatkan raw input
            $input = file_get_contents('php://input');
            error_log("Raw input: " . $input);
            
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg());
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "JSON tidak valid: " . json_last_error_msg()
                ]);
                exit();
            }
            
            // Log data yang diterima
            error_log("Data received: " . print_r($data, true));
            
            // Validasi data yang diperlukan
            $requiredFields = ['id_kategori', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'stok'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        "status" => false,
                        "message" => "Field '$field' diperlukan"
                    ]);
                    exit();
                }
            }
            
            // Escape input untuk keamanan
            $id_kategori = mysqli_real_escape_string($conn, $data['id_kategori']);
            $judul = mysqli_real_escape_string($conn, $data['judul']);
            $image_buku = isset($data['image_buku']) ? mysqli_real_escape_string($conn, $data['image_buku']) : '';
            $deskripsi = isset($data['deskripsi']) ? mysqli_real_escape_string($conn, $data['deskripsi']) : '';
            $penulis = mysqli_real_escape_string($conn, $data['penulis']);
            $penerbit = mysqli_real_escape_string($conn, $data['penerbit']);
            $tahun_terbit = mysqli_real_escape_string($conn, $data['tahun_terbit']);
            $halaman = isset($data['halaman']) ? intval($data['halaman']) : 0;
            $stok = mysqli_real_escape_string($conn, $data['stok']);
            
            // Cek apakah kategori ada
            $checkKategori = mysqli_query($conn, "SELECT id_kategori FROM kategori_buku WHERE id_kategori = $id_kategori");
            if (mysqli_num_rows($checkKategori) == 0) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "Kategori dengan ID $id_kategori tidak ditemukan"
                ]);
                exit();
            }
            
            // Query INSERT
            $sql = "INSERT INTO buku (
                        id_kategori,
                        judul,
                        image_buku,
                        deskripsi,
                        penulis,
                        penerbit,
                        tahun_terbit,
                        halaman,
                        stok
                    ) VALUES (
                        '$id_kategori',
                        '$judul',
                        '$image_buku',
                        '$deskripsi',
                        '$penulis',
                        '$penerbit',
                        '$tahun_terbit',
                        '$halaman',
                        '$stok'
                    )";
            
            error_log("SQL Query: " . $sql);
            
            if (mysqli_query($conn, $sql)) {
                $id_buku = mysqli_insert_id($conn);
                
                echo json_encode([
                    "status" => true,
                    "message" => "Buku berhasil ditambahkan",
                    "data" => [
                        "id_buku" => $id_buku,
                        "judul" => $judul
                    ]
                ]);
            } else {
                error_log("MySQL Error: " . mysqli_error($conn));
                throw new Exception("Gagal menambahkan buku: " . mysqli_error($conn));
            }
            break;
            
        case "PUT":
            $user = requireAuth(2); // Hanya pustakawan
            
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "JSON tidak valid"
                ]);
                exit();
            }
            
            // Validasi ID buku
            if (!isset($data['id_buku']) || empty($data['id_buku'])) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "ID buku diperlukan"
                ]);
                exit();
            }
            
            // Escape input
            $id_buku = mysqli_real_escape_string($conn, $data['id_buku']);
            $id_kategori = mysqli_real_escape_string($conn, $data['id_kategori']);
            $judul = mysqli_real_escape_string($conn, $data['judul']);
            $image_buku = mysqli_real_escape_string($conn, $data['image_buku']);
            $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi']);
            $penulis = mysqli_real_escape_string($conn, $data['penulis']);
            $penerbit = mysqli_real_escape_string($conn, $data['penerbit']);
            $tahun_terbit = mysqli_real_escape_string($conn, $data['tahun_terbit']);
            $halaman = intval($data['halaman']);
            $stok = mysqli_real_escape_string($conn, $data['stok']);
            
            // Cek apakah buku ada
            $checkBuku = mysqli_query($conn, "SELECT id_buku FROM buku WHERE id_buku = $id_buku");
            if (mysqli_num_rows($checkBuku) == 0) {
                http_response_code(404);
                echo json_encode([
                    "status" => false,
                    "message" => "Buku dengan ID $id_buku tidak ditemukan"
                ]);
                exit();
            }
            
            // Query UPDATE
            $sql = "UPDATE buku SET
                        id_kategori = '$id_kategori',
                        judul = '$judul',
                        image_buku = '$image_buku',
                        deskripsi = '$deskripsi',
                        penulis = '$penulis',
                        penerbit = '$penerbit',
                        tahun_terbit = '$tahun_terbit',
                        halaman = '$halaman',
                        stok = '$stok'
                    WHERE id_buku = '$id_buku'";
            
            if (mysqli_query($conn, $sql)) {
                echo json_encode([
                    "status" => true,
                    "message" => "Buku berhasil diupdate",
                    "data" => [
                        "id_buku" => $id_buku,
                        "judul" => $judul
                    ]
                ]);
            } else {
                throw new Exception("Gagal mengupdate buku: " . mysqli_error($conn));
            }
            break;
            
        case "DELETE":
            $user = requireAuth(2); // Hanya pustakawan
            
            parse_str($_SERVER['QUERY_STRING'], $params);
            $id = intval($params['id'] ?? 0);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "ID buku tidak valid"
                ]);
                exit();
            }
            
            // Cek apakah buku sedang dipinjam
            $checkSql = "SELECT COUNT(*) as jumlah FROM peminjaman WHERE id_buku = $id AND status = 'dipinjam'";
            $checkQuery = mysqli_query($conn, $checkSql);
            $checkResult = mysqli_fetch_assoc($checkQuery);
            
            if ($checkResult['jumlah'] > 0) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "Buku tidak dapat dihapus karena masih dipinjam"
                ]);
                exit();
            }
            
            $sql = "DELETE FROM buku WHERE id_buku = $id";
            
            if (mysqli_query($conn, $sql)) {
                if (mysqli_affected_rows($conn) > 0) {
                    echo json_encode([
                        "status" => true,
                        "message" => "Buku berhasil dihapus"
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "status" => false,
                        "message" => "Buku dengan ID $id tidak ditemukan"
                    ]);
                }
            } else {
                throw new Exception("Gagal menghapus buku: " . mysqli_error($conn));
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                "status" => false,
                "message" => "Method tidak diizinkan"
            ]);
            break;
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}

// Tutup koneksi
mysqli_close($conn);