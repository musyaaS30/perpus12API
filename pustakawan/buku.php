<?php
// pustakawan/buku.php
include "../config.php";
require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['pustakawan']);

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case "GET":
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                
                $sql = "SELECT 
                            b.*,
                            k.nama_kategori,
                            k.id_kategori
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
            } else {
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
                            k.nama_kategori,
                            k.id_kategori
                        FROM buku b
                        LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
                        ORDER BY b.id_buku DESC";
                
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
            break;
            
        case "POST":
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(["status" => false, "message" => "Data tidak valid"]);
                exit();
            }
            
            // Validasi field wajib
            $requiredFields = ['id_kategori', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'stok'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        "status" => false, 
                        "message" => "Field '$field' wajib diisi"
                    ]);
                    exit();
                }
            }
            
            // Escape input
            $id_kategori = intval($data['id_kategori']);
            $judul = mysqli_real_escape_string($conn, $data['judul']);
            $image_buku = mysqli_real_escape_string($conn, $data['image_buku'] ?? '');
            $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi'] ?? '');
            $penulis = mysqli_real_escape_string($conn, $data['penulis']);
            $penerbit = mysqli_real_escape_string($conn, $data['penerbit']);
            $tahun_terbit = intval($data['tahun_terbit']);
            $halaman = intval($data['halaman'] ?? 0);
            $stok = intval($data['stok']);
            
            // Cek kategori
            $checkKategori = mysqli_query($conn, "SELECT id_kategori FROM kategori_buku WHERE id_kategori = $id_kategori");
            if (mysqli_num_rows($checkKategori) == 0) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "Kategori tidak ditemukan"
                ]);
                exit();
            }
            
            $sql = "INSERT INTO buku (
                        id_kategori, judul, image_buku, deskripsi, 
                        penulis, penerbit, tahun_terbit, halaman, stok
                    ) VALUES (
                        $id_kategori, '$judul', '$image_buku', '$deskripsi',
                        '$penulis', '$penerbit', $tahun_terbit, $halaman, $stok
                    )";
            
            if (mysqli_query($conn, $sql)) {
                $id_buku = mysqli_insert_id($conn);
                
                echo json_encode([
                    "status" => true,
                    "message" => "Buku berhasil ditambahkan",
                    "data" => ["id_buku" => $id_buku, "judul" => $judul]
                ]);
            } else {
                throw new Exception("Gagal menambahkan buku: " . mysqli_error($conn));
            }
            break;
            
        case "PUT":
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data || !isset($data['id_buku'])) {
                http_response_code(400);
                echo json_encode(["status" => false, "message" => "ID buku diperlukan"]);
                exit();
            }
            
            $id_buku = intval($data['id_buku']);
            $id_kategori = intval($data['id_kategori']);
            $judul = mysqli_real_escape_string($conn, $data['judul']);
            $image_buku = mysqli_real_escape_string($conn, $data['image_buku'] ?? '');
            $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi'] ?? '');
            $penulis = mysqli_real_escape_string($conn, $data['penulis']);
            $penerbit = mysqli_real_escape_string($conn, $data['penerbit']);
            $tahun_terbit = intval($data['tahun_terbit']);
            $halaman = intval($data['halaman'] ?? 0);
            $stok = intval($data['stok']);
            
            // Cek buku
            $checkBuku = mysqli_query($conn, "SELECT id_buku FROM buku WHERE id_buku = $id_buku");
            if (mysqli_num_rows($checkBuku) == 0) {
                http_response_code(404);
                echo json_encode([
                    "status" => false,
                    "message" => "Buku tidak ditemukan"
                ]);
                exit();
            }
            
            $sql = "UPDATE buku SET
                        id_kategori = $id_kategori,
                        judul = '$judul',
                        image_buku = '$image_buku',
                        deskripsi = '$deskripsi',
                        penulis = '$penulis',
                        penerbit = '$penerbit',
                        tahun_terbit = $tahun_terbit,
                        halaman = $halaman,
                        stok = $stok
                    WHERE id_buku = $id_buku";
            
            if (mysqli_query($conn, $sql)) {
                echo json_encode([
                    "status" => true,
                    "message" => "Buku berhasil diupdate",
                    "data" => ["id_buku" => $id_buku, "judul" => $judul]
                ]);
            } else {
                throw new Exception("Gagal mengupdate buku: " . mysqli_error($conn));
            }
            break;
            
        case "DELETE":
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(["status" => false, "message" => "ID buku tidak valid"]);
                exit();
            }
            
            // Cek apakah sedang dipinjam
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
                        "message" => "Buku tidak ditemukan"
                    ]);
                }
            } else {
                throw new Exception("Gagal menghapus buku: " . mysqli_error($conn));
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(["status" => false, "message" => "Method tidak diizinkan"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
?>