<?php
include "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(["status" => false, "message" => "Token tidak ditemukan"]);
        exit();
    }
    
    // Cek user berdasarkan token
    $query = mysqli_query($conn, "SELECT * FROM users WHERE password = '$token'");
    
    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        echo json_encode([
            "status" => true,
            "role" => $user['id_role'],
            "user_id" => $user['id_user']
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => false, "message" => "Token tidak valid"]);
    }
}