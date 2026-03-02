<?php
// auth/logout.php - VERSI JWT (Token TIDAK disimpan di database)

/**
 * ⚠️ CATATAN PENTING TENTANG JWT LOGOUT
 * 
 * JWT adalah stateless token, artinya server tidak menyimpan token di database.
 * Oleh karena itu, "logout" di sisi server sebenarnya TIDAK perlu dilakukan.
 * 
 * Yang perlu dilakukan:
 * 1. Frontend menghapus token dari localStorage/sessionStorage
 * 2. Server hanya perlu response success untuk konfirmasi
 * 
 * ALTERNATIF IMPLEMENTASI LOGOUT JWT:
 * - Gunakan token blacklist di Redis/cache (untuk revoke token sebelum expired)
 * - Gunakan short-lived token + refresh token pattern
 * - Set expiry time pendek (15-30 menit)
 */

header("Content-Type: application/json; charset=UTF-8");

echo json_encode([
    "status" => true,
    "message" => "Logout berhasil. Silakan hapus token dari client."
]);
exit;

/**
 * OPTIONAL: Jika ingin implementasi token blacklist menggunakan database
 * (Namun ini menambah kompleksitas dan database dependency)
 * 
 * Contoh:
 * 
 * $headers = getallheaders();
 * $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
 * 
 * if (!empty($token)) {
 *     // Simpan token ke tabel blacklist dengan expired_at
 *     $stmt = $conn->prepare("INSERT INTO token_blacklist (token, expired_at) VALUES (?, ?)");
 *     $expiredAt = date('Y-m-d H:i:s', time() + 3600); // sesuai dengan JWT exp
 *     $stmt->bind_param("ss", $token, $expiredAt);
 *     $stmt->execute();
 * }
 */
?>  