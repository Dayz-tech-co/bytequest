<?php
require_once __DIR__ . '/../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

define('JWT_ALGO', 'HS256');

// Flexible token generator for both user and admin
function generate_jwt($id, $email, $role = 'user') {
    $secretKey = $_ENV['JWT_SECRET'];
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // 1 hour expiry

    $payload = [
        ($role === 'admin' ? 'admin_id' : 'id') => $id,
        'email' => $email,
        'role' => $role,
        'iat' => $issuedAt,
        'exp' => $expirationTime
    ];

    return JWT::encode($payload, $secretKey, JWT_ALGO);
}

// Decode JWT and return as array
function decode_jwt($token) {
    $secretKey = $_ENV['JWT_SECRET'];
    try {
        $decoded = JWT::decode($token, new Key($secretKey, JWT_ALGO));
        return (array) $decoded;
    } catch (Exception $e) {
        return ['error' => $e->getMessage()]; // Optional: return error for debugging
    }
}

?>

