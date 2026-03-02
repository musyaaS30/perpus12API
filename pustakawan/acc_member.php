<?php
include "../config.php";
require_once "../middleware/middleware.php";

// Hanya pustakawan
$user = authorize(['pustakawan']);

$method = $_SERVER['REQUEST_METHOD'];

/*
|--------------------------------------------------------------------------
| GET - Ambil data pending
|--------------------------------------------------------------------------
*/
if ($method == "GET") {

    $sql = "
        SELECT 
            id_siswa as id,
            nama_siswa as nama,
            email,
            'siswa' as tipe,
            status,
            created_at
        FROM siswa
        WHERE status = 'pending'
        
        UNION ALL
        
        SELECT
            id_guru as id,
            nama_guru as nama,
            email,
            'guru' as tipe,
            status,
            created_at
        FROM guru_anggota
        WHERE status = 'pending'
        
        ORDER BY created_at DESC
    ";

    $query = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }

    echo json_encode([
        "status" => true,
        "data" => $data
    ]);
}


/*
|--------------------------------------------------------------------------
| POST - ACC / Tolak Member
|--------------------------------------------------------------------------
*/
elseif ($method == "POST") {

    $input = json_decode(file_get_contents("php://input"), true);

    $tipe = $input['tipe'] ?? '';
    $id   = intval($input['id'] ?? 0);
    $aksi = $input['aksi'] ?? '';

    if (!in_array($tipe, ['siswa','guru']) || $id <= 0 || !in_array($aksi,['setuju','tolak'])) {
        echo json_encode(["status"=>false,"message"=>"Data tidak valid"]);
        exit;
    }

    mysqli_begin_transaction($conn);

    try {

        /*
        |--------------------------------------------------------------------------
        | JIKA SISWA
        |--------------------------------------------------------------------------
        */
        if ($tipe == "siswa") {

            $get = mysqli_query($conn, "SELECT * FROM siswa WHERE id_siswa = $id FOR UPDATE");
            if (mysqli_num_rows($get) == 0) {
                throw new Exception("Siswa tidak ditemukan");
            }

            $siswa = mysqli_fetch_assoc($get);

            if ($aksi == "setuju") {

                // Update status
                mysqli_query($conn, "UPDATE siswa SET status='approved' WHERE id_siswa=$id");

                // Insert ke users
                $password = $siswa['password']; // sudah hash
                $email    = !empty($siswa['email']) 
                            ? $siswa['email'] 
                            : $siswa['nis'].'@siswa.local';

                $insertUser = mysqli_query($conn, "
                    INSERT INTO users (id_role,nama,email,password)
                    VALUES (3,'{$siswa['nama_siswa']}','$email','$password')
                ");

                if (!$insertUser) {
                    throw new Exception(mysqli_error($conn));
                }

                $user_id = mysqli_insert_id($conn);

                // 🔥 WAJIB: SIMPAN ID_USER KE SISWA
                mysqli_query($conn, "
                    UPDATE siswa 
                    SET id_user = $user_id 
                    WHERE id_siswa = $id
                ");

                $message = "Siswa berhasil di ACC";

            } else {

                mysqli_query($conn, "DELETE FROM siswa WHERE id_siswa=$id");
                $message = "Siswa ditolak & dihapus";
            }
        }


        /*
        |--------------------------------------------------------------------------
        | JIKA GURU
        |--------------------------------------------------------------------------
        */
        if ($tipe == "guru") {

            $get = mysqli_query($conn, "SELECT * FROM guru_anggota WHERE id_guru = $id FOR UPDATE");
            if (mysqli_num_rows($get) == 0) {
                throw new Exception("Guru tidak ditemukan");
            }

            $guru = mysqli_fetch_assoc($get);

            if ($aksi == "setuju") {

                mysqli_query($conn, "UPDATE guru_anggota SET status='approved' WHERE id_guru=$id");

                $password = $guru['password'];
                $email    = !empty($guru['email']) 
                            ? $guru['email'] 
                            : $guru['nip'].'@guru.local';

                $insertUser = mysqli_query($conn, "
                    INSERT INTO users (id_role,nama,email,password)
                    VALUES (3,'{$guru['nama_guru']}','$email','$password')
                ");

                if (!$insertUser) {
                    throw new Exception(mysqli_error($conn));
                }

                $user_id = mysqli_insert_id($conn);

                // 🔥 WAJIB: SIMPAN ID_USER KE GURU
                mysqli_query($conn, "
                    UPDATE guru_anggota 
                    SET id_user = $user_id 
                    WHERE id_guru = $id
                ");

                $message = "Guru berhasil di ACC";

            } else {

                mysqli_query($conn, "DELETE FROM guru_anggota WHERE id_guru=$id");
                $message = "Guru ditolak & dihapus";
            }
        }

        mysqli_commit($conn);

        echo json_encode([
            "status" => true,
            "message" => $message
        ]);

    } catch (Exception $e) {

        mysqli_rollback($conn);

        echo json_encode([
            "status"=>false,
            "message"=>$e->getMessage()
        ]);
    }
}


/*
|--------------------------------------------------------------------------
| METHOD TIDAK DIIZINKAN
|--------------------------------------------------------------------------
*/
else {
    http_response_code(405);
    echo json_encode([
        "status"=>false,
        "message"=>"Method tidak diizinkan"
    ]);
}