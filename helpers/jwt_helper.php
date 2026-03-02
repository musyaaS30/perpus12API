<?php
// helpers/jwt_helper.php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "SUPER_SECRET_KEY_PERPUS12_FIVE_2026_BACKEND_SECURED";

function generateJWT($user) {
    global $secretKey;

    $payload = [
        "iss" => "perpus12",
        "iat" => time(),
        "exp" => time() + (60 * 60), // 1 jam
        "data" => [
            "id" => $user['id'],
            "nama" => $user['nama'],
            "email" => $user['email'],
            "role" => $user['role']
        ]
    ];

    return JWT::encode($payload, $secretKey, 'HS256');
}

function verifyJWT($token) {
    global $secretKey;
    return JWT::decode($token, new Key($secretKey, 'HS256'));
}