<?php
include "../config.php";
// Izinkan akses dari origin mana pun (atau ganti * dengan http://localhost:5173)
header_remove("Access-Control-Allow-Origin");

// Set header CORS yang benar
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Jika kamu menggunakan JSON, tambahkan ini juga
header("Content-Type: application/json; charset=UTF-8");

// ... sisa kode koneksi database dan query kamu ...
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // =========================
    // 🔍 READ (GET)
    // =========================
    case "GET":

        // GET by ID
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            $sql = "SELECT 
                        buku.id_buku,
                        buku.judul,
                        buku.image_buku,
                        buku.deskripsi,
                        buku.penulis,
                        buku.penerbit,
                        buku.tahun_terbit,
                        buku.halaman,
                        buku.stok,
                        kategori_buku.id_kategori,
                        kategori_buku.nama_kategori
                    FROM buku
                    LEFT JOIN kategori_buku 
                    ON buku.id_kategori = kategori_buku.id_kategori
                    WHERE buku.id_buku = $id";

            $query = mysqli_query($conn, $sql);
            $data = mysqli_fetch_assoc($query);

            echo json_encode($data);
        }

        // GET ALL
        else {
            $sql = "SELECT 
                        buku.id_buku,
                        buku.judul,
                        buku.image_buku,
                        buku.deskripsi,
                        buku.penulis,
                        buku.penerbit,
                        buku.tahun_terbit,
                        buku.halaman,
                        buku.stok,
                        kategori_buku.nama_kategori
                    FROM buku
                    LEFT JOIN kategori_buku 
                    ON buku.id_kategori = kategori_buku.id_kategori
                    ORDER BY buku.id_buku DESC";

            $query = mysqli_query($conn, $sql);
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
    '{$data['id_kategori']}',
    '{$data['judul']}',
    '{$data['image_buku']}',
    '{$data['deskripsi']}',
    '{$data['penulis']}',
    '{$data['penerbit']}',
    '{$data['tahun_terbit']}',
    '{$data['halaman']}',
    '{$data['stok']}'
)";


        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Buku berhasil ditambahkan"
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

        $sql = "UPDATE buku SET
                    id_kategori = '{$data['id_kategori']}',
                    judul = '{$data['judul']}',
                    deskripsi = '{$data['deskripsi']}',
                    penulis = '{$data['penulis']}',
                    penerbit = '{$data['penerbit']}',
                    tahun_terbit = '{$data['tahun_terbit']}',
                    halaman = '{$data['halaman']}',
                    stok = '{$data['stok']}'
                WHERE id_buku = '{$data['id_buku']}'";

        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Buku berhasil diupdate"
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

        $sql = "DELETE FROM buku WHERE id_buku = $id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Buku berhasil dihapus"
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => mysqli_error($conn)
            ]);
        }

        break;
}
