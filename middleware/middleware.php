<?php
// middleware.php
require_once __DIR__ . '/../helpers/jwt_helper.php';

/**
 * Middleware untuk memverifikasi token JWT
 * 
 * @return object Data user dari token
 */
function authenticate() {
    $headers = getallheaders();
    
    // Cek header Authorization
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode([
            "status" => false,
            "message" => "Token tidak ditemukan"
        ]);
        exit;
    }
    
    // Ambil token dari header
    $authHeader = $headers['Authorization'];
    $token = str_replace("Bearer ", "", $authHeader);
    
    try {
        // Verifikasi token
        $decoded = verifyJWT($token);
        return $decoded->data;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            "status" => false,
            "message" => "Token tidak valid atau expired",
            "error" => $e->getMessage()
        ]);
        exit;
    }
}

/**
 * Middleware untuk cek role tertentu
 * 
 * @param array $allowedRoles Role yang diizinkan
 * @return object Data user dari token
 */
function authorize($allowedRoles = []) {
    $user = authenticate();
    
    if (!empty($allowedRoles) && !in_array($user->role, $allowedRoles)) {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "Anda tidak memiliki akses ke resource ini"
        ]);
        exit;
    }
    
    return $user;
}

/**
 * Contoh penggunaan di file API:
 * 
 * <?php
 * require_once 'middleware.php';
 * 
 * // Untuk proteksi basic (hanya cek token)
 * $user = authenticate();
 * 
 * // Untuk proteksi dengan role tertentu
 * $user = authorize(['admin', 'pustakawan']); // Hanya admin & pustakawan
 * $user = authorize(['admin']); // Hanya admin
 * $user = authorize(['siswa']); // Hanya siswa
 * 
 * // Lanjutkan proses API...
 * ?>
 */
?>